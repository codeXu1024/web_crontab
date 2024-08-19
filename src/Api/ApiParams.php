<?php

namespace Codexu1024\WebCrontab\Api;

use Codexu1024\WebCrontab\Exception\WebCrontabException;
use Codexu1024\WebCrontab\Orm\Enum\Task;
use Codexu1024\WebCrontab\Orm\WebCrontabTask;
use Workerman\Crontab\Parser;

class ApiParams
{
    private $data = [];

    /**
     * 设置ID
     * @param $val
     * @return $this
     */
    public  function  setId($val){
        $this->data['id'] = $val;
        return $this;
    }


    /**
     * 设置任务名称
     * @param $val
     * @return $this
     */
    public  function  setName($val){
        $this->data['name'] = $val;
        return $this;
    }

    /**
     * 任务状态 0 禁用 1暂停 2启用
     * @param int $val
     * @return $this
     */
    public  function  setStatus(int $val){
        $this->data['status'] = $val;
        return $this;
    }

    /**
     * 定时任务规则
     * @param $val
     * @return $this
     */
    public  function  setRule($val){
        $this->data['rule'] = $val;
        return $this;
    }


    /**
     * 设置任务类型
     * @param $val
     * @return $this
     */
    public  function  setType($val){
        $this->data['type'] = $val;
        return $this;
    }

    /**
     * 如果为HTTP时需要设置请求参数
     * @param $val
     * @return $this
     */
    public  function  setHttpMethod($val){
        $this->data['http_method'] = $val;
        return $this;
    }


    /**
     * 设置超时时间
     * @param $val
     * @return $this
     */
    public  function  setTimeOut($val){
        $this->data['time_out'] = $val;
        return $this;
    }

    /**
     * 设置备注
     * @param $val
     * @return $this
     */
    public  function  setRemark($val){
        $this->data['remark'] = $val;
        return $this;
    }

    /**
     * 设置命令行|请求地址|执行sql|类文件的命名空间
     * @param $val
     * @return $this
     */
    public  function  setCommand($val){
        $this->data['command'] = $val;
        return $this;
    }

    /**
     * 设置参数
     * @param $val
     * @return $this
     */
    public  function  setParams($val){
        $this->data['params'] = $val;
        return $this;
    }

    public function getData(){
        $data = $this->data;
        if(!isset($data['name']) && !$data['name']){
            throw new WebCrontabException("任务名称不存在");
        }
        if(!isset($data['command']) && !$data['command']){
            throw new WebCrontabException("");
        }

        if(!isset($data['rule']) && !$data['rule']){
            throw new WebCrontabException("定时规则不存在");
        }

        $isok = (new Parser())->isValid($data['rule']);
        if(!$isok){
            throw new WebCrontabException("定时规则不正确");
        }

        if(!isset($data['type']) && !$data['type']){
            throw new WebCrontabException("任务类型不存在");
        }

        if(!in_array($data['type'],WebCrontabTask::getTypeList())){
            throw new WebCrontabException("任务类型错误");
        }

        if($data['type'] === Task::TYPE_HTTP){
            if(!isset($data['http_method']) && !$data['http_method']){
                throw new WebCrontabException("HTTP类型,请选择GET或POST");
            }
        }
        return $this->data;
    }



}