<?php
namespace Codexu1024\WebCrontab\WorkerHandler\actuator;

class Http extends BaseActuator {
    
    //执行Http请求
    public  function  doRun($data) :ExecuteResult{

    	switch ($data['http_method']) {
    		case 1:
    			$method = 'GET';
    			break;
    		case 2:
    			$method = 'POST';
    			break;
    		default:
    			$method = 'GET';
    			break;
    	}
    	$headers = [];
    	$reqdata = [];
    	if($data['params']) {
    		$params= json_decode($data['params']);
    		if($params){
    			$headers    =  $params['headers']?:"";
    			$reqdata    =  $params['data']?:[];
    		}
    	}
        try {
            $resultObj = new ExecuteResult();
            $client = new \GuzzleHttp\Client([
                'timeout'  => $data['time_out'],
                'verify'=> false
            ]);
            if(!$data['command']){
                $resultObj->setResultContent('command is null');
                $resultObj->setStatus(500);
                return $resultObj;
            }
            $response = $client->request($method,$data['command'],[
                'form_params' => $reqdata,
                'headers'     => $headers
            ]);
            $resultObj->setResultContent($response->getReasonPhrase());
            $resultObj->setStatus($response->getStatusCode());
        } catch (\Exception $e) {
            $resultObj->setResultContent($e->getMessage());
            $resultObj->setStatus(500);
        }
    	
        
        return $resultObj;
    }
    
}
