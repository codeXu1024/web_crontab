<?php
namespace Codexu1024\WebCrontab\WorkerHandler\actuator;

class Shell extends  BaseActuator{
    
    //执行Http请求
    public  function  doRun($data) :ExecuteResult{

        $resultObj = new ExecuteResult();
        // $resultObj->setResultContent('暂时屏蔽');
        // $resultObj->setStatus(200);
        // return $resultObj;
        $param =  $data['params']?:'';
        $shell = $data['command'].' '.$param;
        $res = exec($shell,$output, $return_val);
        if($return_val == 0){
            $resultObj->setResultContent($res);
            $resultObj->setStatus();
        }else{
            $resultObj->setResultContent('执行错误');
            $resultObj->setStatus(0);
        }
        return  $resultObj;
    }
    
}
