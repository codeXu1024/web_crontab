<?php
namespace Codexu1024\WebCrontab\Events;
//设置时区
date_default_timezone_set('PRC');

use Codexu1024\WebCrontab\Orm\BaseSql;
use Codexu1024\WebCrontab\Orm\WebCrontabTask;
use think\facade\Db;
use Workerman\Crontab\Parser;
use Workerman\Crontab\Crontab;

class Http {
	//任务和定时任务的映射池
    public  static $taskPools= [];

    public  static  $taskConnAddr = '';

	//主进程初始化子进程的回调函数
    public static function onWorkerStart($worker){
        if($worker->id == 0){
            //配置数据库
            Db::setConfig($worker->DbConfig);
            //创建表格
            (new BaseSql())->createTable();
            //配置执行任务的进程的地址
            self::$taskConnAddr = $worker->workerAddress;
            //初始化任务列表加载到定时器中
            self::init();
        }
    }

    /**
     * 初始化进程异步连接通信
     * @throws \Exception
     */
    public static  function asyncTcpConnect($id){
          //初始化连接
        $conn = new \Workerman\Connection\AsyncTcpConnection(self::$taskConnAddr);
        $conn->send((string)$id);
        $conn->connect();
    }
    public static function onMessage($http_connection, $request) {
        $post = $request->post();
        $action = $post['action']??'';
        $id = $post['id']??'';
        if(!$action){
            $http_connection->send(self::error(201,"操作不存在"));
        }
        $funcName = $action.'Task';
        if(class_exists(self::class,$funcName)){
            if($action == 'add'){
                $http_connection->send(self::$funcName($post));
            }
            if(!$id){
                $http_connection->send(self::error('id不能为空'));
            }
            $http_connection->send(self::$funcName($id));
        }
    }

    public static function onClose($connection){

    }

    public static function onWorkerStop($worker){
    	
    }

    public static function onWorkerReload($worker){
    	
    }
    public static function onError($connection){
    	
    }

    //初始化任务
    public  static function  init(){
        $list  = self::getTaskList();
        if($list){
            foreach ($list as $v) {
                self::addTask($v);
            }
        }
    }
    
    //添加任务
    public  static function addTask($data){
        $rule = $data['rule']??'';
        if(!$rule){
            return self::error('定时规则不能为空');
        }
        $id = $data['id'];
        //判断 crontab 规则是否有效
        $isok = (new Parser())->isValid($rule);
        if(!$isok){
            return self::error('定时规则不正确');
        }
        //1.先进行删除
        self::deleteTask($id);

        self::$taskPools[$id] = new Crontab($data['rule'], function() use($id) {
            //防止删除后  php 垃圾还没有回收 导致错误
            if(!isset(self::$taskPools[$id])){
                return false;
            }
            if(property_exists(self::$taskPools[$id],'stop')){
                return false;
            }
            // 与远程task服务建立异步连接
            self::asyncTcpConnect($id);
            return true;
        });

        if($data['status'] == 1){
            self::$taskPools[$id]->stop=true;
        }
        return self::success();
    }

    /**
     * 执行任务
     * @param $post
     * @return false|string
     * @throws \Exception
     */
    public static  function executeTask($id){
        self::asyncTcpConnect($id);
        return self::success();
    }

    /**
     * 启动任务
     * @param $id
     * @return false|string
     */
    public  static function startUpTask($id){
        if(self::taskIsExist($id)){
            if(property_exists(self::$taskPools[$id],'stop')){
                unset(self::$taskPools[$id]->stop);
            }
            return self::success();
        }
        return self::error('任务不存在');
    }

    /**
     * 暂停任务
     * @param $id
     * @return false|string
     */
    public  static  function  suspendTask($id){
        if(self::taskIsExist($id)){
            self::$taskPools[$id]->stop = true;
        }
        return self::success();
    }

    /**
     * 删除任务
     * @param $id
     * @return bool
     */
    public static  function deleteTask($id){
        if(self::taskIsExist($id)){
            self::$taskPools[$id]->stop = true;
            //销毁这个定时类
            self::$taskPools[$id]->destroy();
            //销毁数组变量
            unset(self::$taskPools[$id]);
            return self::success();
        }
        return self::error("{$id}任务不存在");
    }

    /***
     * 判断任务是否存在
     * @param $id
     * @return bool
     */
    public  static function  taskIsExist($id){
        if(isset(self::$taskPools[$id])){
            return true;
        }
        return false;
    }

    //获取所有任务
    public static  function  getTaskList(){
        $list = WebCrontabTask::where('status',2)->field('id,rule,status')->select();
        return  $list;
    }

    public static function  responseJson($code,$msg){
        $data['code'] = $code;
        $data['msg'] = $msg;
        return json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    public static function  success($msg='success'){
        return self::responseJson(200,$msg);
    }

    public static function  error($msg='fail',$code=201){
        return self::responseJson($code,$msg);
    }


}
