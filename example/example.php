<?php
require_once "../vendor/autoload.php";

use Codexu1024\WebCrontab\Api\ApiParams;
use Codexu1024\WebCrontab\Orm\Enum\Task;
use Codexu1024\WebCrontab\Api\WebCrontabApi;

$api = WebCrontabApi::init();

//增加任务
$obj = (new ApiParams())->setName("测试2")
            ->setType(Task::TYPE_SHELL)
            ->setCommand("php -v")
            ->setRule("*/1 * * * * *")
            ->setStatus(Task::STATUS_ENABLE)
            ->setTimeOut(20);
$api->addTask($obj);
//编辑任务
$obj = (new ApiParams())->setName("测试2")
    ->setId(2)
    ->setType(Task::TYPE_SHELL)
    ->setCommand("php -v")
    ->setRule("*/1 * * * * *")
    ->setStatus(Task::STATUS_ENABLE)
    ->setTimeOut(20);
$api->editTask($obj);


//运行任务
$api->runTask(2);

//暂停任务
$api->suspendTask(2);

//启用任务
$api->startTask(2);

//删除任务
$api->deleteTask(2);









