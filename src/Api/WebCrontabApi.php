<?php

namespace Codexu1024\WebCrontab\Api;

use Codexu1024\WebCrontab\Exception\WebCrontabException;
use Codexu1024\WebCrontab\Orm\Enum\Task;
use Codexu1024\WebCrontab\Orm\WebCrontabTask;
use think\facade\Db;

class WebCrontabApi
{
    private static $config = [];

    public static  function init(){

        if(!self::$config){
            $file = __DIR__ . '/../../../../../config/web_crontab.php';
            self::$config = $config = require_once $file;
            $db = [
                'default'     => 'mysql',
                'connections' => [
                    'mysql' =>$config['mysql']
                ]
            ];
            Db::setConfig($db);
        }
        return new self();
    }


    //添加任务
    public function addTask(ApiParams $obj){
        $data  =$obj->getData();
        $model = WebCrontabTask::saveData($data);
        $model['action'] = 'add';
        $this->handle($model->toArray());
        return $model->id;
    }

    //编辑任务
    public function editTask(ApiParams $obj){
        $data  =$obj->getData();
        $field = ['status','rule','type','command'];
        if(!isset($data['id'])){
            throw new WebCrontabException('');
        }
        $model = WebCrontabTask::where('id',$data['id'])->find();
        $update = false;
        foreach ($field as $v){
            if($model[$v] != $data[$v]){
                $update= true;
                break;
            }
        }
        if($update){
            $model = WebCrontabTask::saveData($data);
            $model['action'] = 'add';
            $this->handle($model->toArray());
        }
        return $model->id;
    }

    //删除任务
    public  function deleteTask($id){
        $isOk = WebCrontabTask::where('id',$id)->delete();
        if($isOk){
            $data['action'] = 'delete';
            $data['id'] = $id;
            return $this->handle($data);
        }
        return false;
    }

    //运行任务
    public  function runTask($id){
        $data['action'] = 'execute';
        $data['id'] = $id;
        return $this->handle($data);
    }

    //暂停任务
    public  function suspendTask($id){
        $isOk = WebCrontabTask::where('id',$id)->save(['status'=>Task::STATUS_PAUSE]);
        $data['action'] = 'suspend';
        $data['id'] = $id;
        return $this->handle($data);
    }

    //启动任务
    public  function startTask($id){
        $isOk = WebCrontabTask::where('id',$id)->save(['status'=>Task::STATUS_ENABLE]);
        $data['action'] = 'startUp';
        $data['id'] = $id;
        $this->handle($data);
    }

    public  function  request($data){
        $url = 'http://'.self::$config['http_addr'];
        if(is_array($data)){
            $data = http_build_query($data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        if(curl_errno($ch)){
            throw new WebCrontabException('curl 错误code:'.curl_errno($ch));
        }
        curl_close($ch);
        return $output;
    }

    public  function handle($data){
        $res = $this->request($data);
        $result = json_decode($res,true);
        if($result['code'] !== 200){
            throw new WebCrontabException($result['msg']);
        }
        return true;
    }


}