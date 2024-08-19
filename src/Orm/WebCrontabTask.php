<?php

namespace Codexu1024\WebCrontab\Orm;
use Codexu1024\WebCrontab\Orm\Enum\Task;
use think\model\concern\SoftDelete;

class WebCrontabTask  extends BaseModel
{
        use SoftDelete;

        protected $autoWriteTimestamp = true;
        protected $defaultSoftDelete  = 0;

        /**
         * 类型列表
         * @return string[]
         */
        final public  static function getTypeList(){
            return [
                Task::TYPE_HTTP,
                Task::TYPE_SHELL,
                Task::TYPE_SQL_SCRIPT,
                Task::TYPE_PHP_SCRIPT,
            ];
        }

        public  static  function  saveData($data){
            if(isset($data['id']) && $data['id']){
                $model = self::where('id',$data['id'])->find();
                $model->save($data);
                return $model;
            }
            return static::create($data);
        }
}