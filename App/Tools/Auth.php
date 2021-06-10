<?php
/**
 * Created By
 * Date:2021/5/8
 * Author:jhwu
 */

namespace App\Tools;


use App\Models\User\Admin;

use EasySwoole\EasySwoole\Config;
use EasySwoole\FastCache\Cache;
use EasySwoole\Jwt\Jwt;

class Auth
{

    protected static $instance;
    protected $cache;

    public function __construct()
    {
        $this->cache = Cache::getInstance();
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return Auth
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }




    /**
     * 用户登录
     *
     * @param string $account 账号,用户名、邮箱、手机号
     * @param string $password 密码
     * @return boolean
     */
    public function login($account, $password)
    {
        $user = Admin::create()->where('username',$account)->get();
        if (empty($user)) {
            return false;
        }
//        if (!$user->status) {
//            return false;
//        }
        if ($user->password != $this->getEncryptPassword($password, $user->salt)) {
            return false;
        }

        $config = Config::getInstance();
        $jwtConfig = $config->getConf('JWT');
        $expire_at = time() + $jwtConfig['exp'];
        $jwtObject = Jwt::getInstance()
            ->setSecretKey($jwtConfig['key']) // 秘钥
            ->publish();
        $jwtObject->setAlg('HMACSHA256'); // 加密方式
        $jwtObject->setAud("easy_swoole_admin"); // 用户
        $jwtObject->setExp($expire_at); // 过期时间
        $jwtObject->setIat(time()); // 发布时间
        $jwtObject->setIss($jwtConfig['iss']); // 发行人
        $jwtObject->setJti(md5(time())); // jwt id 用于标识该jwt
        $jwtObject->setNbf(time()); // 在此之前不可用
        $jwtObject->setSub($jwtConfig['sub']); // 主题

        // 自定义数据
        $jwtObject->setData([
            'user_id' => $user->id,
            'username' => $user->username
        ]);
        $token = $jwtObject->__toString();
        if ($token) {
            Cache::getInstance()->unset('user_token_' . $user->id);
            $this->cache->set('user_token_' . $user->id, $token, $jwtConfig['exp']);
        }

        $data = [
            'token' => $token,
            'userInfo' => array(
                'id' => $user->id,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'status' => $user->status,
            ),
            'expire_at' => $expire_at,
        ];
        return $data;
    }


    public function logout($user_id)
    {
        Cache::getInstance()->unset('user_token_' . $user_id);
        return true;
    }

    /**
     * 获取密码加密后的字符串
     * @param string $password 密码
     * @param string $salt 密码盐
     * @return string
     */
    public function getEncryptPassword($password, $salt = '')
    {
        return md5(md5($password) . $salt);
    }


    public function user($uid)
    {
        return Admin::create()->get($uid);
    }


    public function create($username, $password, $email = '', $mobile = '', $extend = [])
    {
        $data = [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'mobile' => $mobile,
            'level' => 1,
            'score' => 0,
            'avatar' => '',
        ];
        $ip = '';
        $params = array_merge($data, [
            'nickname' => preg_match("/^1[3-9]{1}\d{9}$/", $username) ? substr_replace($username, '****', 3, 4) : $username,
            'salt' => random(30),
            'jointime' => time(),
            'joinip' => $ip,
            'logintime' => time(),
            'loginip' => $ip,
            'prevtime' => time(),
            'status' => 1
        ]);
        $params['password'] = $this->getEncryptPassword($password, $params['salt']);
        $params = array_merge($params, $extend);

        $model = new Admin($params);
        $rs = $model->save();
        return $rs;
    }

}