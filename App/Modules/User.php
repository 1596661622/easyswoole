<?php
/**
 * Created By
 * Date:2021/6/24
 * Author:jhwu
 */

namespace App\Modules;

use EasySwoole\Rpc\Service\AbstractServiceModule;

class User  extends  AbstractServiceModule
{

    function moduleName(): string
    {
        return 'UserModule';
    }

    function list()
    {
        $this->response()->setResult([
            [
                'id' => 1,
                'username' => 'test001',
                'password' => 1124
            ],
            [
                'id' => 2,
                'username' => 'test002',
                'password' => 599
            ]
        ]);
        $this->response()->setMsg('get users list success');
    }

    function exception()
    {
        throw new \Exception('the UserModule exception');

    }

    protected function onException(\Throwable $throwable)
    {
        $this->response()->setStatus(-1)->setMsg($throwable->getMessage());
    }
}