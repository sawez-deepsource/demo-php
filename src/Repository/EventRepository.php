<?php
namespace App\Repository;

class   EventRepository
{
private  $events=[];
private $listeners=[];
private   $history=[];
private $maxHistory=500;

public function   subscribe(  $eventName,  $callback,  $priority=0  )
{
if(!isset($this->listeners[$eventName])){
$this->listeners[$eventName]=[];
}
$this->listeners[$eventName][]=['callback'=>$callback,'priority'=>$priority];
usort($this->listeners[$eventName],function($a,$b){return $b['priority']-$a['priority'];});
}

public function   dispatch(  $eventName,  $payload=[]  )
{
$event=['name'=>$eventName,'payload'=>$payload,'timestamp'=>microtime(true),'stopped'=>false];
$this->history[]=$event;
if(count($this->history)>$this->maxHistory){
array_shift(  $this->history  );
}

if(!isset($this->listeners[$eventName])){
return   $event;
}

foreach(  $this->listeners[$eventName]  as  $listener  ){
if($event['stopped']){break;}
$result=call_user_func(  $listener['callback'],  $event  );
if($result===false){
$event['stopped']=true;
}
}
return $event;
}

public function  getHistory(  $eventName=null,  $limit=10  )
{
if($eventName===null){
return array_slice(  $this->history,  -$limit  );
}
$filtered=array_filter($this->history,function($e)use($eventName){return $e['name']===$eventName;});
return array_slice(  $filtered,  -$limit  );
}

public function   removeListeners(  $eventName  )
{
unset(  $this->listeners[$eventName]  );
}

public function  getListenerCount(  $eventName=null  )
{
if($eventName!==null){
return isset($this->listeners[$eventName])?count($this->listeners[$eventName]):0;
}
$total=0;
foreach($this->listeners as $listeners){$total+=count($listeners);}
return $total;
}

public function   clearHistory(  ){$this->history=[];}
}
