
<?php

    $baseDir = dirname(dirname($GLOBALS['_composer_bin_dir']));
    $configPath = $baseDir.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."web_crontab.php";
    $consolePath = $baseDir.DIRECTORY_SEPARATOR."console".DIRECTORY_SEPARATOR;
    #查询配置文件是否存在
    if(!file_exists($configPath)){
        @mkdir($baseDir.DIRECTORY_SEPARATOR."config",0775);
        file_put_contents($configPath, createWebCrontabConfig());
    }

    if(!file_exists($consolePath."http.php")){
        @mkdir($consolePath,0775);
        file_put_contents($consolePath."http.php",createBootHttp());
    }

    if(!file_exists($consolePath."worker.php")){
        file_put_contents($consolePath."worker.php",createBootWorker());
    }

    function createWebCrontabConfig(){
        return <<<Cfg
<?php
    if(!defined('WEB_CRONTAB_ROOT_PATH')){
         define("WEB_CRONTAB_ROOT_PATH", dirname(__DIR__));
    }
    return  [
        'mysql' => [
            // 数据库类型
            'type'     => 'mysql',
            // 主机地址
            'hostname' => '127.0.0.1',
            // 用户名
            'username' => 'root',
            // 密码
            'password' => 'root',
            // 数据库名
            'database' => 'test',
            // 数据库编码默认采用utf8
            'charset'  => 'utf8',
            // 数据库表前缀
            'prefix'   => 'think_',
            'auto_timestamp'=>true,
            // 数据库调试模式
            'debug'    => true,
            'break_reconnect'=>true,
        ],
        'http_addr'=>'127.0.0.1:8001',
        'worker_addr'=>'127.0.0.1:14324',
        'worker_count'=>20,
        'log_dir'=>'log'
    ];
Cfg;
    }

    function createBootHttp(){
        return '<?php 
    require "vendor/autoload.php";
    use Codexu1024\WebCrontab\Server\HttpServer;
    $data = require_once  "config/web_crontab.php";
    (new HttpServer($data["http_addr"]))->setPrintFilePath($data["log_dir"])->setDbConfig($data["mysql"])->setWorkerAddress($data["worker_addr"])->run();
';
    }

    function createBootWorker(){
        return '<?php
    require_once "vendor/autoload.php";
    use Codexu1024\WebCrontab\Server\WorkerServer;
    $data = require_once  "config/web_crontab.php";
    (new WorkerServer($data["worker_addr"]))->setReusePort()->setDbConfig($data["mysql"])->setCount($data["worker_count"]?:5)->run();
    ';
    }
