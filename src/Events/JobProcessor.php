<?php
namespace Codexu1024\WebCrontab\Events;
use Codexu1024\WebCrontab\Orm\WebCrontabTask;
use Codexu1024\WebCrontab\Orm\WebCrontabTaskLog;
use Codexu1024\WebCrontab\WorkerHandler\Actuator;
use think\facade\Db;

class JobProcessor {
	//主进程初始化子进程的回调函数

    public static function onWorkerStart($worker){
        //如果想mysql提高查询效率,可以维护一个mysql链接池 需要共享变量的服务,
        if($worker->id == 0){
            echo "\033[32;40m---------------------------执行器启动成功--------------------------------\033[0m".PHP_EOL;
        }
        //配置数据库
        Db::setConfig($worker->DbConfig);
    }

    public static function onMessage($connection, $taskid) {
        //收到数据 进行连接销毁
        $connection->destroy();
        //to do task logic
        $info = self::getTaskOne($taskid);
        if($info){
            try {
                //匹配任务执行器
                $class = $info['type'];
                $resObj   =  Actuator::$class()->doRun($info);
                $res = $resObj->getData();
                $res['status_code'] = $res['result_status'];
            }catch (\Exception $e){
                $res['status_code'] = 500;
                $res['result_content'] = $e->getMessage();
            }
            self::writeLog($res,$taskid);
        }
    }

    public static function onClose($connection){

    }

    public static function onError($connection){
    	
    }

    //获取一条数据
    public static  function  getTaskOne($id){
        return WebCrontabTask::where('id',$id)->find();
    }

    public  static function writeLog($res,$taskid){
        $res['tid'] = $taskid;
        $model = WebCrontabTaskLog::create($res);
        if(!$model){
            consoleOutputFail("任务id==={$taskid}====写入日志失败");
        }
    }

}
