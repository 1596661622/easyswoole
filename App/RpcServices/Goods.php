<?php
/**
 * Created By
 * Date:2021/6/24
 * Author:jhwu
 */

namespace App\RpcServices;


use EasySwoole\Rpc\Service\AbstractService;
use EasySwoole\Rpc\Protocol\Request;

class Goods extends AbstractService
{

    /**
     *  重写onRequest(比如可以对方法做ip拦截或其它前置操作)
     *
     * @param Request $request
     * @return bool
     */
    protected function onRequest(Request $request): bool
    {
        return true;
    }

    function serviceName(): string
    {
        return 'Goods';
    }
}