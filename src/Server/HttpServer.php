<?php
namespace Codexu1024\WebCrontab\Server;

use Codexu1024\WebCrontab\Exception\WebCrontabException;
use Codexu1024\WebCrontab\Events\Http;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
class HttpServer {
    private $worker;

    public static $socketAddr = '127.0.0.1:8283';

    private  $workerName = 'Http-Server';

    private $DbConfig = [];
    public function __construct($socketAddr = '', array $context = []) {
        self::$socketAddr = 'http://' .$socketAddr?:self::$socketAddr;
        $this->initServer($context);
    }

    /**
     * 获取地址
     * @return string
     */
    public static function getSocketArr(): string
    {
        return self::$socketAddr;
    }

    private function initServer($context = []) {
        $this->worker = new Worker(self::$socketAddr,$context);
        $this->worker->name = $this->workerName?:'HttpServer';
        if (isset($context['ssl'])) {
            $this->worker->transport = 'ssl';
        }
        //设置回调处理
        $this->eventRegister();
    }

    //设置默认应用层发送缓冲区大小。默认1M。可以动态设置
    public function setBufferSize($size = 1024 * 1024){
        TcpConnection::$defaultMaxPackageSize = $size;
        return $this;
    }

    /**
     * 设置数据库配置信息
     * @param $val
     * @return $this
     */
    public function setDbConfig($data){
        if(!is_array($data)){
            throw new WebCrontabException("配置不正确");
        }
        $mysql = [
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
        ];
        $mysql = array_merge($mysql,$data);
        $DbConfig = [
            // 默认数据连接标识
            'default'     => 'mysql',
            // 数据库连接信息
            'connections' => [
                'mysql' =>$mysql,
            ],
        ];
        $this->worker->DbConfig = $DbConfig;
        return $this;
    }

    /**
     * 设置数据库配置
     * @param $val
     * @return $this
     */
    public function setWorkerAddress($val=''){
        $this->worker->workerAddress = "Text://".$val;
        return $this;
    }

    //设置进程名称
    public function setWorkerName($name = "HttpServer"){
       $this->worker->workerName = $name;
        return $this;
    }

    //设置打印文件路径
    public function setPrintFilePath($path = "./log") {
        if(!is_dir($path)){
            @mkdir($path,0755);
        }
        Worker::$logFile = $path.'/http_server_run.log';
        return $this;
    }

    //以daemon(守护进程)方式运行
    public function setDaemon($bool = false) {
        Worker::$daemonize = $bool;
        return $this;
    }

    //注册回调函数
    private function eventRegister() {
        $this->worker->onWorkerStart    = [Http::class, 'onWorkerStart'];
        $this->worker->onWorkerReload   = [Http::class, 'onWorkerReload'];
        $this->worker->onWorkerStop     = [Http::class, 'onWorkerStop'];
        $this->worker->onMessage        = [Http::class, 'onMessage'];
        $this->worker->onClose          = [Http::class, 'onClose'];
        $this->worker->onError          = [Http::class, 'onError'];
    }

    public function run() {

        if(!$this->worker->DbConfig){
            throw new WebCrontabException("未设置数据库配置");
        }
        if(!$this->worker->workerAddress){
            throw new WebCrontabException("未设置WorkerServer的监听地址");
        }
        if(!defined('GLOBAL_START')){
            Worker::runAll();
        }
    }

}
