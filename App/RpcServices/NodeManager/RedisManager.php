<?php


namespace App\RpcServices\NodeManager;

use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\Pool;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\NodeManager\NodeManagerInterface;
use EasySwoole\Rpc\Server\ServiceNode;

class RedisManager implements NodeManagerInterface
{

    protected $redisKey;

    protected $ttl;

    /**
     * @var Pool $pool
     */
    protected $pool;

    public function __construct(Pool $pool, string $hashKey = 'rpc', int $ttl = 30)
    {
        $this->pool = $pool;
        $this->redisKey = $hashKey;
        $this->ttl = $ttl;
    }

    function getNodes(string $serviceName, ?int $version = null): array
    {
        $fails = [];
        $hits = [];
        $time = time();

        $redisPool = $this->pool;

        /** @var Redis $redis */
        $redis = $redisPool->defer(15);

        try {
            $nodes = $redis->hGetAll("{$this->redisKey}_{$serviceName}");

            $nodes = $nodes ?: [];

            foreach ($nodes as $nodeId => $value) {
                $node = json_decode($value, true);
                if ($time - $node['lastHeartbeat'] > $this->ttl) {
                    $fails[] = $nodeId;
                    continue;
                }
                if ($node['service'] === $serviceName) {
                    if ($version !== null && $version === $node['version']) {
                        $serviceNode = new ServiceNode($node);
                        $serviceNode->setNodeId(strval($nodeId));
                        $hits[$nodeId] = $serviceNode;
                    } else {
                        $serviceNode = new ServiceNode($node);
                        $serviceNode->setNodeId(strval($nodeId));
                        $hits[] = $serviceNode;
                    }
                }
            }
            if (!empty($fails)) {
                foreach ($fails as $failKey) {
                    $this->deleteServiceNode($serviceName, $failKey);
                }
            }
            return $hits;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return [];
    }

    function getNode(string $serviceName, ?int $version = null): ?ServiceNode
    {
        $list = $this->getNodes($serviceName, $version);
        if (empty($list)) {
            return null;
        }
        $allWeight = 0;

        $redisPool = $this->pool;;

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);

        $time = time();

        try {
            foreach ($list as $node) {
                /** @var ServiceNode $nodee */
                $key = $node->getNodeId();
                $nodeConfig = $redis->hGet("{$this->redisKey}_{$serviceName}", $key);
                $nodeConfig = json_decode($nodeConfig, true);
                $lastFailTime = $nodeConfig['lastFailTime'];
                if ($time - $lastFailTime >= 10) {
                    $weight = 10;
                } else {
                    $weight = abs(10 - ($time - $lastFailTime));
                }
                $allWeight += $weight;
                $node->__weight = $weight;
            }
            mt_srand(intval(microtime(true)));
            $allWeight = rand(0, $allWeight - 1);
            foreach ($list as $node) {
                $allWeight = $allWeight - $node->__weight;
                if ($allWeight <= 0) {
                    return $node;
                }
            }
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return null;
    }

    function failDown(ServiceNode $serviceNode): bool
    {

        $redisPool = $this->pool;;

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);
        try {
            $serviceName = $serviceNode->getService();
            $nodeId = $serviceNode->getNodeId();
            $hashKey = "{$this->redisKey}_{$serviceName}";
            $nodeConfig = $redis->hGet($hashKey, $nodeId);
            $nodeConfig = json_decode($nodeConfig, true);
            $nodeConfig['lastFailTime'] = time();
            $redis->hSet($hashKey, $nodeId, json_encode($nodeConfig));
            return true;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }

    function offline(ServiceNode $serviceNode): bool
    {

        $redisPool = $this->pool;;

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);
        try {
            $serviceName = $serviceNode->getService();
            $nodeId = $serviceNode->getNodeId();
            $hashKey = "{$this->redisKey}_{$serviceName}";
            $redis->hDel($hashKey, $nodeId);
            return true;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }

    function alive(ServiceNode $serviceNode): bool
    {
        $info = [
            'service' => $serviceNode->getService(),
            'ip' => $serviceNode->getIp(),
            'port' => $serviceNode->getPort(),
            'version' => $serviceNode->getVersion(),
            'lastHeartbeat' => time(),
            'lastFailTime' => 0
        ];

        $redisPool = $this->pool;;

        /** @var Redis $redis */
        $redis = $redisPool->getObj();

        try {
            $serviceName = $serviceNode->getService();
            $nodeId = $serviceNode->getNodeId();
            $hashKey = "{$this->redisKey}_{$serviceName}";
            $redis->hSet($hashKey, $nodeId, json_encode($info));
            return true;
        } catch (\Throwable $throwable) {
            // 如果该 redis 断线则销毁
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }

    private function deleteServiceNode($serviceName, $failKey): bool
    {
        $redisPool = $this->pool;;

        /** @var Redis $redis */
        $redis = $redisPool->getObj(15);
        try {
            $redis->hDel("{$this->redisKey}_{$serviceName}", $failKey);
            return true;
        } catch (\Throwable $throwable) {
            $redisPool->unsetObj($redis);
        } finally {
            $redisPool->recycleObj($redis);
        }

        return false;
    }
}