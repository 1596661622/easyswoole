<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\Models\User;


use App\Models\ModelBase;

class Admin extends ModelBase
{
    protected $tableName = 'admin';
    protected $autoTimeStamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}