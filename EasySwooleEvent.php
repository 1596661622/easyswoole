<?php


namespace EasySwoole\EasySwoole;


use App\Modules\Goods;
use App\Modules\User;
use App\RpcServices\NodeManager\RedisManager;
use EasySwoole\Component\Process\Exception;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FastCache\Cache;
use EasySwoole\FastCache\Exception\RuntimeError;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\Pool;

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


        ###### 注册 rpc 服务 ######
        /** rpc 服务端配置 */

        $redis_pool = new Pool(new RedisConfig(
            [
                'host'=>'192.168.2.148'
            ]
        ));

        $manager = new RedisManager($redis_pool);
        $config = new \EasySwoole\Rpc\Config($manager);
        $config->setNodeManager($manager);

//        $config = new \EasySwoole\Rpc\Config();
        $config->setNodeId('EasySwooleRpcNode1');
        $config->setServerName('EasySwoole'); // 默认 EasySwoole
//        $config->setOnException(function (\Throwable $throwable) {
//
//        });
        $serverConfig = $config->getServer();
        $serverConfig->setServerIp('127.0.0.1');

        $rpc = new \EasySwoole\Rpc\Rpc($config);

        $goodsService = new \App\RpcServices\Goods();
        $goodsService->addModule(new Goods());
        $rpc->serviceManager()->addService($goodsService);

        $userService = new \App\RpcServices\User();
        $userService->addModule(new User());
        $rpc->serviceManager()->addService($userService);

        $rpc->attachServer(ServerManager::getInstance()->getSwooleServer());


    }
}