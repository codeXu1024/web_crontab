<?php
namespace Codexu1024\WebCrontab\WorkerHandler\actuator;
use function gettype;
class SqlScript extends  BaseActuator{
    
    //执行Http请求
    public  function  doRun($data) :ExecuteResult{

        $resultObj = new ExecuteResult();
        try {
            $param =  $data['params']?:'';
            $className = $data['command']?:'';
            if(class_exists($className)){
                $res = (new $className($param))->run();
            }
            $type = gettype($res);
            $types = ['boolean','integer','double','string','array'];
            if(in_array($type,$types)){
                if(is_array($type)){
                    $res = json_encode($res,JSON_UNESCAPED_UNICODE);
                }
            }else{
                $res = "执行成功";
            }
            $resultObj->setResultContent($res);
            $resultObj->setStatus();
        }catch (\Exception $e){
            $resultObj->setResultContent("{$e->getFile()}>>>{$e->getLine()}>>>{$e->getMessage()}");
            $resultObj->setStatus(0);
        }
        return  $resultObj;
    }
    
}
