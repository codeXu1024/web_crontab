<?php
if(!function_exists('consoleOutputOK')){
    function consoleOutputOK($txt){
        echo "\033[32;40m{$txt}\033[0m";
    }
}

if(!function_exists('outputFail')){
    function consoleOutputFail($txt){
        echo "\033[31;40m{$txt}\033[0m";
    }
}


if(!function_exists('checkLoadSwow')){
    function checkLoadSwow(){
        if(extension_loaded('swow')){
            if(class_exists('Swow\Coroutine')){
                return true;
            }
        }
        return false;
    }
}



