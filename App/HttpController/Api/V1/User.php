<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\HttpController\Api\V1;


use App\Languages\Dictionary;
use App\Models\Menu\AdminMenu;
use App\Models\Role\RoleAdminUser;
use App\Models\User\Admin;
use App\Tools\ResponseStatus;
use EasySwoole\Validate\Validate;

class User extends Api
{

    protected function getValidateRule(?string $action): ?Validate
    {
        // TODO: Implement getValidateRule() method.
        return null;
    }

    public function login(){
        $account = $this->request()->getRequestParam('username');
        $password = $this->request()->getRequestParam('password');
        $userInfo = $this->auth->login($account, $password);
        if ($userInfo) {
            return $this->success(1,'登录成功',$userInfo);
        }
        return $this->error(0, '登录失败');
    }

    public function add(){
        $username = $this->request()->getRequestParam('username');
        $password = $this->request()->getRequestParam('password');
        $email = $this->request()->getRequestParam('email');
        $mobile = $this->request()->getRequestParam('mobile');
        $extend = array();
        $user = Admin::create()->where('username',$username)->get();
        if ($user){
           return $this->error(1,'用户名已存在');
        }
        $this->auth->create($username, $password, $email, $mobile, $extend);
    }


    public function logout()
    {
        $this->auth->logout($this->token['user_id']);
        return $this->success(1, '退出成功');
    }


    public function userInfo()
    {

        $menuModel = new AdminMenu();
        $roles = new RoleAdminUser();
        $roleArr = $roles
            ->with(['roles'])
            ->where('admin_id', $this->user_id)
            ->all();
        $menu = $menuModel->with(['actions'])->all();

        $allMenu = [];
        foreach ($menu as $v) {
            $actions = [];
            if (is_array($v['actions']) && count($v['actions']) > 0) {
                foreach ($v['actions'] as $a) {
                    $actions[$a['action']] = $a;
                }
                $v['actions'] = $actions;
            }
            $allMenu[$v['id']] = $v;
        }
        unset($menu);

        $menuArr = [];
        $datalimit = 0;
        $userMenu = [];
        $roleName = [];

        foreach ($roleArr as $role) {

            if ($datalimit < $role['roles']['datalimit']) {
                $datalimit = $role['roles']['datalimit'];
            }
            $roleName[] = $role['roles']['name'];
            //菜单权限集合, {"menu_id":"all","menu_id":["index","query","add","edit","get","delete"]}
            if ($role['roles']['menujson'] === 'all') {
                $userMenu = $allMenu;
                break;
            }
            $role_menu = json_decode($role['roles']['menujson'], true);

            if (!empty($role_menu)) {
                foreach ($role_menu as $k => $m) {
                    if ($m === 'all') {
                        $userMenu[$k] = $allMenu[$k];
                        break;
                    }
                    if (is_array($m) && !empty($m) && is_array($allMenu[$k]['actions']) && !empty($allMenu[$k]['actions'])) {
                        if (isset($userMenu[$k])) {
                            $userMenu[$k]['actions'] = array_intersect($allMenu[$k]['actions'], $m);
                            foreach ($m as $v) {
                                if (isset($allMenu[$k]['actions'][$v]) && !isset($userMenu[$k]['actions'][$v])) {
                                    $userMenu[$k]['actions'][$v] = $allMenu[$k]['actions'][$v];
                                }
                            }
                        } else {
                            $userMenu[$k] = $allMenu[$k];
                            $hadActions = [];
                            foreach ($allMenu[$k]['actions'] as $kk => $a) {
                                if (in_array($kk, $m)) {
                                    $hadActions[$kk] = $a;
                                }
                            }
                            $userMenu[$k]['actions'] = $hadActions;
                            // $userMenu[$k]['actions'] = array_intersect($allMenu[$k]['actions'], $m);
                        }
                    }
                }
            }
        }

        $permissions = [];
        $menuNv = [];
        foreach ($userMenu as $u) {

            $permissions[] = [
                'roleId' => $u['id'],
                'permissionId' => $u['key'],
                'permissionName' => $u['title'],
                'actions' => ($u['actions']),
                'actionEntitySet' => ($u['actions']),
            ];
            $menuNv[] = [
                'name' => $u['key'],
                'parentId' => $u['parent_id'],
                'id' => $u['id'],
                'meta' => [
                    "icon" => $u['icon'],
                    "title" => $u['title'],
                    "show" => $u['ishow'] == 1 ? true : false,
                ],
                'component' => $u['component'],
                'redirect' => $u['uri'],

            ];
        }
//        $menuNv =  Language::getInstance()->translate($this->language,$menuNv); //转换
//        $permissions =  Language::getInstance()->translate($this->language,$permissions); //转换

        $userArr =
            [
//                'user' => $this->userInfo,
                'rolename' => implode(',', $roleName),
                'datalimit' => $datalimit,
                'roles' => $permissions,
                'menuNv' => $menuNv,
            ];

        return $this->success(1,'获取成功', $userArr);
    }

}