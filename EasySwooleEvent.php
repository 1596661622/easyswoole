<?php


namespace EasySwoole\EasySwoole;


use App\RpcServices\NodeManager\RedisManager;
use EasySwoole\Component\Process\Exception;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FastCache\Cache;
use EasySwoole\FastCache\Exception\RuntimeError;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\Rpc;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        date_default_timezone_set('Asia/Shanghai');
        // 初始化数据库ORM
        $configData = Config::getInstance()->getConf('MYSQL');
        $config = new \EasySwoole\ORM\Db\Config($configData);
        DbManager::getInstance()->addConnection(new Connection($config));

    }

    public static function mainServerCreate(EventRegister $register)
    {
        // hot-reload
        $hotReloadOptions = new \EasySwoole\HotReload\HotReloadOptions;
        $hotReload = new \EasySwoole\HotReload\HotReload($hotReloadOptions);
        $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);
        $server = ServerManager::getInstance()->getSwooleServer();
        $hotReload->attachToServer($server);

        // ***************** 注册fast-cache *****************
        try {
            $config = new \EasySwoole\FastCache\Config();
            $config->setTempDir(EASYSWOOLE_TEMP_DIR);
            Cache::getInstance($config)->attachToServer(ServerManager::getInstance()->getSwooleServer());
        } catch (Exception $e) {
            echo "[Warn] --> fast-cache注册失败\n";
        } catch (RuntimeError $e) {
            echo "[Warn] --> fast-cache注册失败\n";
        }

        $redis_pool = new RedisPool(new RedisConfig(
            [
                'host'=>'192.168.2.144'
            ]
        ));
        $manager = new RedisManager($redis_pool);
        $config = new \EasySwoole\Rpc\Config($manager);
        $config->setNodeManager($manager);
        $rpc = new Rpc($config);

    }
}