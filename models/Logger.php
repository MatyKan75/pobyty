<?php

/**
 * Třída Logger umožňuje zapisovat informace o běhu aplikace do log souboru.
 * Ladící informace se zapisují pouze tehdy, je-li 
 */
class Logger {
    
    private $file;
    private $debug=false;

    public function open($dbg){
        $this->file = fopen("app.log","w+");
        $this->debug = $dbg;
    }

    public function info($msg){
        $this->writeLog("",$msg);
    }

    public function warning($msg){
        $this->writeLog("!",$msg);
    }

    public function error($msg){
        $this->writeLog("!",$msg);
    }

    public function debug($msg){
        if($this->debug){
            $this->writeLog(">",$msg);
        }
    }

    public function writeLog($type,$msg) {
        $now = date("Y-m-d H:i:s");
        fwrite($this->file,"[$now] $type$msg \n");
        fflush($this->file);
    }
}

?>