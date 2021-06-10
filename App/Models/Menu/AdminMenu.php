<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\Models\Menu;


use App\Models\ModelBase;

class AdminMenu extends ModelBase
{
    protected $tableName = 'admin_menu';
    protected $autoTimeStamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public function actions(){
        return $this->hasMany(AdminMenuAction::class, function ($builder){
        }, 'id', 'menu_id');
    }
}