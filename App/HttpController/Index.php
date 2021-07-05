<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\Controller;

class Index extends Controller
{

    public function index()
    {
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/welcome.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    function test()
    {
        $this->response()->write('this is test');
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    public function test1()
    {
        // 如果在同server中 直接用保存的rpc实例调用即可
        // 如果不是需要重新new一个rpc 注意config的配置 节点管理器 以及所在ip是否能被其他服务广播到 如果不能请调整其他服务的广播地址
        $config = new \EasySwoole\Rpc\Config();
        $rpc = new \EasySwoole\Rpc\Rpc($config);

        $ret = [];
        $client = $rpc->client();
        // client 全局参数
        $client->setClientArg([1, 2, 3]);
        /**
         * 调用商品列表
         */
        $ctx1 = $client->addRequest('User.UserModule.list');

        // 设置请求参数
        $ctx1->setArg(['a', 'b', 'c']);
        // 设置调用成功执行回调
        $ctx1->setOnSuccess(function (Response $response) use (&$ret) {
            var_dump(1);
            $ret[] = [
                'list' => [
                    'msg' => $response->getMsg(),
                    'result' => $response->getResult()
                ]
            ];
        });





        // 执行调用
        $client->exec();
        $this->writeJson(200, $ret);

    }
}