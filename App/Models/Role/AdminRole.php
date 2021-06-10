<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\Models\Role;


use App\Models\ModelBase;

class AdminRole extends ModelBase
{
    protected $tableName = 'admin_roles';
    protected $autoTimeStamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

}