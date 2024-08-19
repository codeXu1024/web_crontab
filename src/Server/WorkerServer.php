<?php
namespace Codexu1024\WebCrontab\Server;

use Codexu1024\WebCrontab\Exception\WebCrontabException;
use Codexu1024\WebCrontab\Events\JobProcessor;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
class WorkerServer{
    private $worker;
    private $socketAddr = '';
    private  $workerName = 'Worker-Server';

    public function __construct($socketAddr,$context = []) {
        $this->socketAddr = 'text://'.$socketAddr;
        $this->initServer($context);
    }

    private function initServer($context = []) {
        $this->worker = new Worker($this->socketAddr);
        $this->worker->workerName = $this->workerName;
        $this->worker->loadSwow = checkLoadSwow();
        //设置回调处理
        $this->eventRegister();
    }

     //设置默认应用层发送缓冲区大小。默认1M。可以动态设置
    public function setBufferSize($size = 1024 * 1024){
        TcpConnection::$defaultMaxSendBufferSize = $size;
        return $this;
    }

    //设置进程名称
    public function setCount($number = 1){
       $this->worker->count = $number;
       return $this;
    }

    //设置进程名称
    public function setWorkerName($name = "Worker-Server"){
       $this->workerName = $name;
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
     * 设置进程端口复用
     * @return $this
     */
    public function setReusePort(){
       $this->worker->reusePort = true;
        return $this;
    }

    /**
     * 以daemon(守护进程)方式运行
     * @param $bool
     * @return $this
     */
    public function setDaemon($bool = false) {
        Worker::$daemonize = $bool;
        return $this;
    }

    /**
     * 注册回调函数
     * @return void
     */
    private function eventRegister() {
        $this->worker->onWorkerStart = [JobProcessor::class, 'onWorkerStart'];
        $this->worker->onMessage     =  [JobProcessor::class, 'onMessage'];
        $this->worker->onClose       = [JobProcessor::class, 'onClose'];
        $this->worker->onError       = [JobProcessor::class, 'onError'];
    }

    public function run() {
        if(!$this->worker->DbConfig){
            throw new WebCrontabException("未设置数据库配置");
        }
        if(!defined('GLOBAL_START')){
            Worker::runAll();
        }
    }

}
