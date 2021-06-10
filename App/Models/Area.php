<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\Models;


use App\Models\User\Admin;

class Area extends ModelBase
{
    protected $tableName = 'area';
    protected $autoTimeStamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function creater(){
        return $this->hasOne(Admin::class,function ($builder){
            $builder->fields('id,username,nickname');
        },'creater_id','id');
    }

    public function add($data){
       return self::create($data)->save();
    }
}