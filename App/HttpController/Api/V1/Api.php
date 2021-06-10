<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\HttpController\Api\V1;


use App\Tools\Auth;
use App\Tools\Response;
use EasySwoole\EasySwoole\Config;
use EasySwoole\FastCache\Cache;
use EasySwoole\Jwt\Jwt;
use EasySwoole\Validate\Validate;

abstract class Api extends \EasySwoole\Http\AbstractInterface\Controller
{

    private $basicAction = [
        '/api/v1/user/login',
    ];
    protected $token;

    protected $auth;
    protected $cache;
    protected $user_id;

    public function __construct()
    {
        $this->auth = Auth::instance();
        $this->cache = Cache::getInstance();
        parent::__construct();
    }

    public function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        };

        $path = $this->request()->getUri()->getPath();

        // basic列表里的不需要验证
        if (!in_array($path, $this->basicAction)){
            if (empty( $this->request()->getHeader('token')[0] )){
                $this->error(\EasySwoole\Http\Message\Status::CODE_UNAUTHORIZED,'token不可为空','',\EasySwoole\Http\Message\Status::CODE_UNAUTHORIZED);
                return false;
            }


            $config    = Config::getInstance();
            $jwtConfig = $config->getConf('JWT');

            $jwtObject = Jwt::getInstance()->setSecretKey($jwtConfig['key'])->decode($this->request()->getHeader('token')[0]);
            $status = $jwtObject->getStatus();
            // 如果encode设置了秘钥,decode 的时候要指定

            switch ($status)
            {
                case  1:
                    $this->token = $jwtObject->getData();
                    $cache_token = $this->cache->get('user_token_' . $this->token['user_id']);
                    if ($cache_token != $this->request()->getHeader('token')[0]) {
                        $this->error(0, "token无效",'',\EasySwoole\Http\Message\Status::CODE_BAD_REQUEST);
                        return false;
                    }
                    if (isset($this->token['user_id'])) {
                        $this->user_id = $this->token['user_id'];
                    }
                    break;
                case  -1:
                    $this->error(\EasySwoole\Http\Message\Status::CODE_BAD_REQUEST, "token无效",'',\EasySwoole\Http\Message\Status::CODE_BAD_REQUEST);
                    return false;
                    break;
                case  -2:
                    $this->error(\EasySwoole\Http\Message\Status::CODE_UNAUTHORIZED, "token过期",'',\EasySwoole\Http\Message\Status::CODE_UNAUTHORIZED);
                    return false;
                    break;
            }

            if (!is_array($this->token) || empty($this->token)){
                $this->error(\EasySwoole\Http\Message\Status::CODE_BAD_REQUEST, "token解析失败:".$this->token,'',\EasySwoole\Http\Message\Status::CODE_BAD_REQUEST);
                return false;
            }

        }


        return true;
    }


    public function writeJson($code = 200,  $msg = NULL,$data = NULL, $statusCode = 200)
    {
        if (!$this->response()->isEndResponse()) {
            $data = array(
                "code" => $code,
                "msg" => $msg,
                "data" => $data,
            );
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        } else {
            return false;
        }
    }

    abstract protected function getValidateRule(?string $action): ?Validate;

    protected function validate(Validate $validate)
    {
        return $validate->validate($this->request()->getRequestParam());
    }

    public function error($code = 0, $msg, $data = '', $status = 200)
    {
        return $this->writeJson($code = 0, $msg, $data, $status);
    }

    public function success($code = 1, $msg,  $data = '',$status = 200)
    {
        return $this->writeJson($code = 1,  $msg,$data, $status);
    }

}