<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\Models\Device;


use App\Models\Area;
use App\Models\ModelBase;
use App\Models\User\Admin;

class Device extends ModelBase
{
    protected $tableName = 'device';
    protected $autoTimeStamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function creater(){
        return $this->hasOne(Admin::class,function ($builder){
            $builder->fields('id,username,nickname');
        },'creater_id','id');
    }

    public function area(){
        return $this->hasOne(Area::class,function ($builder){
            $builder->fields('id,name,en_name');
        },'area_id','id');
    }

    public function add($data){
        return self::create($data)->save();
    }
}