<?php
namespace App\Service;

class     LoggerService
{
private   $logFile;
private $minLevel;
private    $buffer=[];
private $bufferSize=50;
const   LEVELS=['debug'=>0,'info'=>1,'warning'=>2,'error'=>3,'critical'=>4];

public function __construct(  $logFile='/tmp/app.log',  $minLevel='debug'  )
{
$this->logFile=$logFile;
$this->minLevel=$minLevel;
}

public function  debug($message,$context=[]){$this->log('debug',$message,$context);}
public function  info($message,$context=[]){$this->log('info',$message,$context);}
public function  warning($message,$context=[]){$this->log('warning',$message,$context);}
public function  error($message,$context=[]){$this->log('error',$message,$context);}
public function  critical($message,$context=[]){$this->log('critical',$message,$context);}

private function   log(  $level,  $message,  $context=[]  )
{
if(self::LEVELS[$level]<self::LEVELS[$this->minLevel]){
return;
}
$entry=[
'timestamp'=>date('Y-m-d H:i:s'),
'level'=>strtoupper($level),
'message'=>$this->interpolate($message,$context),
'memory'=>memory_get_usage(  true  ),
'peak_memory'=>memory_get_peak_usage(  true  ),
];
$this->buffer[]=$entry;
if(count(  $this->buffer  )>=$this->bufferSize){
$this->flush(  );
}
}

private function   interpolate(  $message,  $context  )
{
$replace=[];
foreach(  $context  as  $key=>$val  ){
if(!is_array($val)&&(!is_object($val)||method_exists($val,'__toString'))){
$replace['{'.$key.'}']=(string)$val;
}
}
return strtr(  $message,  $replace  );
}

public function   flush(  )
{
if(empty(  $this->buffer  )){return;}
$lines=[];
foreach(  $this->buffer   as   $entry  ){
$lines[]=json_encode($entry);
}
file_put_contents($this->logFile,implode("\n",$lines)."\n",FILE_APPEND|LOCK_EX);
$this->buffer=[];
}

public function  __destruct(  ){$this->flush();}
}
