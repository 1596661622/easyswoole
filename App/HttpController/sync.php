<?php
/**
 * Created By
 * Date:2021/6/10
 * Author:jhwu
 */

namespace App\HttpController;


use App\Models\Device\Checks;
use App\Models\Device\Device;
use App\Models\Device\Links;
use EasySwoole\Http\AbstractInterface\Controller;

class sync extends Controller
{

    public function device_install(){
        $code = $this->request()->getRequestParam('code');
        $device =   Device::create()->where('code',$code)->get();
        if (empty($device))  return $this->response()->write('error');
        $ip = '127.12.11.1';
        $data =  array(
            'ip' => $ip,
            'is_install' => 1,
            'installtime' => time(),
        );
        $device->update($data);
        return $this->response()->write('success');
    }

    public function device(){
        $code = $this->request()->getRequestParam('code');
        $device =   Device::create()->where('code',$code)->get();
        if (empty($device))  return $this->response()->write('error');
        $etc_address = $this->request()->getRequestParam('etc_address');
        $ip = '127.12.11.1';
        $status = $this->request()->getRequestParam('status');
        $data =  array(
            'etc_address' => $etc_address,
            'ip' => $ip,
            'status' => $status,
        );
          $device->update($data);
        return $this->response()->write('success');
    }

    public function link(){
        $code = $this->request()->getRequestParam('code');
        $device =   Device::create()->where('code',$code)->get();
        if (empty($device)) return $this->response()->write('error');
        $amount = $this->request()->getRequestParam('amount');
        $ip = '127.12.11.1';
        $data = array(
            'device_id' => $device->id,
            'ip' => $ip,
            'amount' => $amount,
        );
        Links::create($data)->save();
        return $this->response()->write('success');

    }

    public function checks(){
        $code = $this->request()->getRequestParam('code');
        $device =   Device::create()->where('code',$code)->get();
        if (empty($device)) return $this->response()->write('error');
        $content = $this->request()->getRequestParam('content');
        if (is_array($content)){
            $content = json_encode($content);
        }
        $extra = $this->request()->getRequestParam('extra');
        if (is_array($extra)){
            $extra = json_encode($extra);
        }
        $data = array(
            'device_id' => $device->id,
            'extra' => $extra,
            'content' => $content,
        );
         Checks::create($data)->save();
        return $this->response()->write('success');
    }
}