<?php
namespace Codexu1024\WebCrontab\WorkerHandler;

class Actuator {

    private function __construct() {
        
    }

    public static function __callStatic($method, $params)
    {   
        $app = new self($params);

        return $app->create($method);
    }


    public function create($method){

        $class = __NAMESPACE__ . '\\actuator\\'. ucwords($method);
        if (class_exists($class)) {
            return new $class();
        }

        throw new \Exception("[{$method}] API Not Exists");
    }

}