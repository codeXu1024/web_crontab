<?php

namespace Codexu1024\WebCrontab\Orm\Enum;

class Task
{
    const TYPE_HTTP = 'Http';
    const TYPE_SHELL = 'Shell';
    const TYPE_SQL_SCRIPT = 'SqlScript';
    const TYPE_PHP_SCRIPT = 'PHPScript';


    const STATUS_ENABLE = 2;

    const STATUS_PAUSE = 1;
    const STATUS_DISABLE = 0;


}