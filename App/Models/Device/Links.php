<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\Models\Device;


use App\Models\ModelBase;

class Links extends ModelBase
{
    protected $tableName = 'device_links';
    protected $autoTimeStamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function device(){
        return $this->hasOne(Device::class,function ($builder){
            $builder->fields('id,name,en_name');
        },'device_id','id');
    }
}