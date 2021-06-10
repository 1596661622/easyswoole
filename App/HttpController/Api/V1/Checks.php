<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\HttpController\Api\V1;


use EasySwoole\Validate\Validate;

class Checks extends Api
{

    protected  $model;
    protected function getValidateRule(?string $action): ?Validate
    {
        // TODO: Implement getValidateRule() method.
        return null;
    }


    public function __construct()
    {
        $this->model = new \App\Models\Device\Checks();
        parent::__construct();
    }

    public function index()
    {
        $page = $this->request()->getRequestParam('page') ?: 1;
        $limit = $this->request()->getRequestParam('pageSize') ?: 20;

        $model = $this->model->limit($limit * ($page - 1), $limit)
            ->withTotalCount();
        $keyword = $this->request()->getRequestParam('keyword') ?: '';
        if ($keyword){
            $model = $model->where("(name like '%$keyword%' or en_name like '%$keyword%')");
        }
        $list = $model
            ->with(['device'])
            ->order('id')
            ->all(null);
        $result = $model->lastQueryResult();
        $total = $result->getTotalCount();

        $data = array(
            'list' => $list,
            'page' => $page,
            'total' => $total,
        );
        if ($list) return $this->success(1,'获取成功',$data);
        return  $this->error(0,'暂无数据');
    }


    public function detail(){
        $id = $this->request()->getRequestParam('id');
        $model = $this->model->with(['device'])->where('id',$id)->get();
        if ($model) return $this->success(1,'获取成功',$model);
        return  $this->error(0,'获取失败');
    }

    public function delete(){
        $id = $this->request()->getRequestParam('id');
        $model = $this->model->where('id',$id)->get();
        if (empty($model)) return $this->error(0,'该地区不存在');
        $model->destroy();
        return  $this->success(1,'删除成功');
    }

}