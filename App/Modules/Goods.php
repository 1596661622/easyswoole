<?php
/**
 * Created By
 * Date:2021/6/24
 * Author:jhwu
 */

namespace App\Modules;

use EasySwoole\Rpc\Service\AbstractServiceModule;

class Goods  extends  AbstractServiceModule
{

    function moduleName(): string
    {
        return 'GoodsModule';
    }

    function list()
    {
        $this->response()->setResult([
            [
                'goodsId' => '100001',
                'goodsName' => '商品1',
                'prices' => 1124
            ],
            [
                'goodsId' => '100002',
                'goodsName' => '商品2',
                'prices' => 599
            ]
        ]);
        $this->response()->setMsg('get goods list success');
    }

    function exception()
    {
        throw new \Exception('the GoodsModule exception');

    }

    protected function onException(\Throwable $throwable)
    {
        $this->response()->setStatus(-1)->setMsg($throwable->getMessage());
    }
}