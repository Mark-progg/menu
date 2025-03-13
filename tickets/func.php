<?php
//не используется
function createFormField($inpname,$value=null,$opts=['required'=>false,'readonly'=>false,'disabled'=>false],$dataset=['dir'=>"",'minchar'=>3],$selname,$seldataset=[],$event=null){
  echo "<div class=input";
  if(!empty($event))echo " onclick='".$event."'";
  echo ">";
  echo "<input type=text";
  if(!empty($value)){ 
    echo " value='".$value."'";
    $opts['readonly']=true;
    $opts['disabled']=true;
  }else{
    $opts['readonly']=false;
    $opts['disabled']=false;
  }
  foreach($opts as $key=>$val){
    if($val) echo " ".$key."=".$val;
  }
  if(!empty($dataset['dir'])){
    echo " onkeyup='search(event,this,false)' autocomplete=off onfocusOut=hide(this.parentNode.querySelector('.searchlist'))";
    foreach($dataset as $key=>$val){ 
      echo " data-".$key."='".$val."'";
    }
    echo ">";
    echo "<br><select class=searchlist size=5 name='".$name."' onkeyup='selKey(event,this)' onclick='choose(this,null,event)' onfocusout='choose(this);hide(this);' ";
    foreach($seldataset as $key=>$val){
      echo " data-".$key."='".$val."'";
    }
    foreach($opts as $key=>$val){ 
      if($val) echo " ".$key."=".$val;
    }
    echo ">";
    echo "</select>";
  }else{
    echo " name='".$name."'>";
  }
  echo "</div>";
}
?>