<?php

class Yr_Exception extends Exception
{
    static protected $_mode = "prod";

    static public function handle(Exception $e)
    {
                            //500
        if (headers_sent() === false) {
                header("Content-Type: text/html; charset=UTF-8");
        }

        if (self::$_mode !== "prod") { // Only show debug information if NOT in production


            echo get_class($e)." code ". $e->getCode() ." with message '".$e->getMessage()."' in file ". $e->getFile() .":".$e->getLine() . PHP_EOL . PHP_EOL;
            //echo "<pre>".debug_print_backtrace()."</pre>";
        }

        throw new RuntimeException("<p xml:lang=\"no\">Teknisk feil ved henting av værdata fra <a href=\"http://yr.no\">yr.no</a></p><p>{$e->getMessage()}</p>");

    }

    static public function getMode()
    {
        return self::$_mode;
    }

    static public function setMode($mode)
    {
        self::$_mode = $mode;
    }

}
