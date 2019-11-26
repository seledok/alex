<?php
/**
 * Created by PhpStorm.
 * User: seledok
 * Date: 2019-11-18
 * Time: 11:02
 */

namespace Base;


class Log
{
    protected $log_prefix = null;
    protected $log_type = 'file';
    protected $log_filename = '';
    protected $log_class = '';


    public function setLogType($type)
    {
        $this->log_type = $type;
        return $this;
    }

    public function setLogPrefix($log_prefix)
    {
        $this->log_prefix = $log_prefix;
        return $this;
    }

    public function setLogClass($class)
    {
        $this->log_class = $class;
        return $this;
    }

    public static function get($class)
    {
        $log = new static();
        $log->setLogClass($class);

        return $log;
    }


    public function log($text,$prefix='')
    {

        //$class = get_class($this);
        if(get_class($this) != 'Base\Log')
            $this->log_class = str_replace('\\','-',get_class($this));

        if($prefix)
            $prefix = '_'.$prefix;

        $this->log_filename = $this->log_class.'_'.$prefix.'_'.date('m_d'). '.log';

        if (is_array($text) || is_object($text))
            $result = print_r($text, true);
        else
            $result = $text;


        $alert = (substr($result,0,strlen('Error')) == 'Error' || substr($result,0,strlen('Warning')) == 'Warning' ) ? 1 : 0;


        $method = 'log'.$this->log_type;
        $this->{$method}($result,$alert);

        if($alert)
            $this->logConsole($result);

    }

    protected function logFile($result)
    {
        $handle = fopen(DIR_LOGS . $this->log_filename, "a");
        fwrite($handle, date('H:i').': '.$result."\n");
        fclose($handle);
    }

    protected function logConsole($result)
    {
        if (self::is_console())
            echo $result . "\n";
        else
            echo nl2br($result . "\n");
    }

    protected static function is_console()
    {
        return PHP_SAPI == 'cli' || (!isset($_SERVER['DOCUMENT_ROOT']) && !isset($_SERVER['REQUEST_URI']));
    }
}