<?php
namespace Codexu1024\WebCrontab\WorkerHandler\actuator;
abstract class BaseActuator {
    abstract public function doRun($data):ExecuteResult;
}
