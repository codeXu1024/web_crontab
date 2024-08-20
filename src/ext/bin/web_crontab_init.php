
<?php
    require_once dirname(dirname(__DIR__)).'/Lib/functions.php';

    $baseDir = dirname(dirname($GLOBALS['_composer_bin_dir']));
    $configPath = $baseDir.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."web_crontab.php";
    $consolePath = $baseDir.DIRECTORY_SEPARATOR."console".DIRECTORY_SEPARATOR;
    #查询配置文件是否存在
    if(!file_exists($configPath)){
        @mkdir($baseDir.DIRECTORY_SEPARATOR."config",0775);
        file_put_contents($configPath, createWebCrontabConfig());
    }
    #http server启动文件
    $bootHttp = $consolePath."boot_http.php";
    if(!file_exists($bootHttp)){
        @mkdir($consolePath,0775);
        file_put_contents($bootHttp,createBootHttp());
    }
    #工作server启动文件
    $boot_worker = $consolePath."boot_worker.php";
    if(!file_exists($boot_worker)){
        file_put_contents($boot_worker,createBootWorker());
    }
    #linux 启动文件
    $web_crontab = $baseDir.DIRECTORY_SEPARATOR."web_crontab.php";
    if(!file_exists($web_crontab)){
        file_put_contents($web_crontab,createBootFile());
    }
    #windows bat文件
    $windows_bat = $baseDir.DIRECTORY_SEPARATOR."windows_web_crontab.bat";
    if(!file_exists($windows_bat)){
        file_put_contents($windows_bat,createBootWindowsFile());
    }

    consoleOutputOK("--------------初始化成功-----------------");
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

    function createBootFile(){
        return '<?php
    /**
     * run with command
     * php start.php start
     */
    ini_set("display_errors", "on");
    use Workerman\Worker;
    
    if(strpos(strtolower(PHP_OS), "win") === 0)
    {
        exit("start.php not support windows, please use start_for_win.bat\n");
    }
    
    // 检查扩展
    if(!extension_loaded("pcntl"))
    {
        exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
    }
    
    if(!extension_loaded("posix"))
    {
        exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
    }
    
    // 标记是全局启动
    define("GLOBAL_START", 1);
    
    require_once __DIR__ . "/vendor/autoload.php";
    
    // 加载所有Applications/*/start.php，以便启动所有服务
    foreach(glob(__DIR__."/console/boot*.php") as $start_file)
    {
        require_once $start_file;
    }
    // 运行所有服务
    Worker::runAll();
        ';
    }

    function createBootWindowsFile(){
        return <<<Cfg
php console/boot_http.php  console/boot_worker.php
pause
Cfg;
    }