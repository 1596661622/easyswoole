<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\Models\Role;


use App\Models\ModelBase;

class RoleAdminUser extends ModelBase
{
    protected $tableName = 'admin_role_users';
    protected $autoTimeStamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function roles()
    {
        return $this->hasOne(AdminRole::class, function ($builder) {
        }, 'role_id', 'id');
    }
}