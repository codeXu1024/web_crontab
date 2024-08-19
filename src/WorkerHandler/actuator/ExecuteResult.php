<?php
namespace Codexu1024\WebCrontab\WorkerHandler\actuator;
class ExecuteResult {
   private $data;

    //200默认成功 0不成功
    public  function setStatus($code = 200){
   		$this->data['result_status'] = $code;
    }

   //结果
    public function setResultContent($content){
   		$this->data['result_content'] = $content;
    }

    //获取数据
    public  function getData(){
    	return $this->data;
    }
}
