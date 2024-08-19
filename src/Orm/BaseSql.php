<?php

namespace Codexu1024\WebCrontab\Orm;

use think\facade\Db;

class BaseSql
{
    private  function   createJobTable($table){
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `{$table}`  (
         `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL DEFAULT '',
          `status` tinyint(1) NOT NULL DEFAULT '2' COMMENT '0 禁用 1暂停 2启用',
          `rule` char(100) NOT NULL DEFAULT '' COMMENT '定时任务规则',
          `type` enum('Http','SqlScript','PHPScript','Shell') NOT NULL COMMENT 'http:执行http请求 ;shell:执行shell ;sqlscript:执行mysql语句;phpscript:执行php类文件(需要支持PSR4)',
          `remark` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '' COMMENT '任务备注',
          `time_out` int(3) NOT NULL DEFAULT '30' COMMENT '执行超时时间',
          `command` varchar(255) NOT NULL DEFAULT '' COMMENT '执行命令',
          `params` varchar(255) DEFAULT NULL COMMENT '执行参数',
          `http_method` tinyint(1) DEFAULT NULL COMMENT 'http 执行方式 1 get 2. post',
          `create_time` int(12) NOT NULL DEFAULT '0' COMMENT '创建时间',
          `update_time` int(12) NOT NULL DEFAULT '0' COMMENT '更新时间',
          `delete_time` int(12) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`) USING BTREE
        ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '定时器任务表' ROW_FORMAT = DYNAMIC;
SQL;
        return $sql;
    }

    private  function   createTaskLogTable($table){
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `{$table}`  (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tid` int(11) NOT NULL COMMENT '任务id',
          `status_code` int(4) NOT NULL COMMENT '使用http状态码  200 成功  其他失败',
          `result_content` varchar(255) DEFAULT NULL COMMENT '返回结果',
          `create_time` int(12) NOT NULL COMMENT '创建时间',
          `update_time` int(12) NOT NULL DEFAULT '0' COMMENT '更新时间',
          PRIMARY KEY (`id`) USING BTREE
        ) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '任务执行日志表' ROW_FORMAT = DYNAMIC;
SQL;
        return $sql;
    }

    public function  createTable(){
        $SqlConf = Db::getConfig('connections')['mysql'];
        //创建表名
        $task_table = $SqlConf['prefix'].'web_crontab_task';
        $exists = Db::table($task_table)->find();
        if(!$exists){
            Db::execute($this->createJobTable($task_table));
            try {
                Db::table($task_table)->find();
            } catch (\Exception $e) {
                consoleOutputOK('同步定时任务表失败');
            }
        }

        $task_table = $SqlConf['prefix'].'web_crontab_task_log';
        $exists = Db::table($task_table)->find();
        if(!$exists) {
            Db::execute($this->createTaskLogTable($task_table));
            try {
                Db::table($task_table)->find();
            } catch (\Exception $e) {
                consoleOutputFail('同步定时任务日志表失败');
            }
        }
    }

}