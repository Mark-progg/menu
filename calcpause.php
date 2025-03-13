<?php
include('auth2.php');
echo "<script type='text/javascript'>
function DND(reason){
  if(confirm(\"Вы хотите включить/выключить паузу('+reason+')\")){
     loadExternalContent('./dnd2.php?reason='+reason);
  }
}
</script>";
              
$in_row=6;
$stmt=oci_parse($conn,"select substr(user,3) usr,
        a.agent,
	lpad(extract(hour from numtodsinterval(sum(a.dd),'day')),2,'0') || ':' ||
	lpad(extract(minute from numtodsinterval(sum(a.dd),'day')),2,'0') || ':' ||
	lpad(round(extract(second from numtodsinterval(sum(a.dd),'day'))),2,'0') p_time,
	a.data1 rsn,
	case when (ps.reason=a.data1 and ps.pause=1) then '1' else '0' end n,
	lpad(extract(hour from numtodsinterval(max(a.dd)  keep(dense_rank last order by a.time),'day')),2,'0') || ':' ||
	  lpad(extract(minute from numtodsinterval(max(a.dd)  keep(dense_rank last order by a.time),'day')),2,'0') || ':' ||
          lpad(round(extract(second from numtodsinterval(max(a.dd)  keep(dense_rank last order by a.time),'day'))),2,'0') l_time,
	to_number(max(a.dd) keep(dense_rank last order by a.time)) last_time
from (
   select time,agent,
	nvl(to_date(substr(lead(time,1) over(order by time),1,19),'YYYY-MM-DD HH24:MI:SS'),sysdate)-to_date(substr(time,1,19),'YYYY-MM-DD HH24:MI:SS') dd,
	event,
	data1 
    from queue_log,phones ph, staff s
    where event in ('PAUSE','UNPAUSE') 
	and queuename='sl-group-all' 
	and time between to_char(trunc(sysdate),'YYYY-MM-DD HH24:MI:SS') 
		and to_char(sysdate,'YYYY-MM-DD HH24:MI:SS')
	and substr(agent,5)=ph.phone_number
	and ph.person_id=s.person_id
	and ph.person_sid=s.person_sid
	and s.uname=substr(user,3)
    order by time) a, phone_status ps
where ps.phone_number=substr(a.agent,5)
--  and event != 'UNPAUSE' 
group by a.agent,a.data1,(case when (ps.reason=a.data1 and ps.pause=1) then '1' else '0' end)
order by decode(rsn,'work',1,'recall',2,'postcall',3,'break',4,'dinner',5,'workout',6,7)");
$res=oci_execute($stmt);
$t['0.4']=0.0004629629;
$t['1']=0.0006944444;
$t['7']=0.0048611111;
$t['9']=0.00625;
$t['10']=0.006944444;
$t['20']=0.013888888;

$st=array('busy','postcall','recall','break','dinner');
$ru_st=array('Дела','Поствыз.','Перезвон','Перерыв','Обед');
$st_limit=array(0,$t['1'],$t['10'],$t['10'],0,0);
$arr=array();
$sum=array();
$actv_per="";
while($row=oci_fetch_array($stmt,OCI_ASSOC)){
  $sum[@$row['RSN']]=$row['P_TIME'];
  $arr[@$row['RSN']]=$row['L_TIME'];
  $usr=$row['USR'];
  if($row['N']==1){
     $actv_per[0]=$row['RSN'];
     $actv_per[1]=$row['LAST_TIME'];
  };
}
echo "<div class=inline>".@$usr."</div>";
$i=0;
foreach($st as $rsn){
  $style="";
  $class="";
  $i++;
  if(@$arr[$rsn]=="") $arr[$rsn]='00:00:00';
  if(@$sum[$rsn]=="") $sum[$rsn]='00:00:00';
  if(@$actv_per[0]==$rsn){
    $class.="active_per ";
    if(str_replace($st,$st_limit,$rsn)!="0"){
      $p[0]=intval((($actv_per[1]/floatval(str_replace($st,$st_limit,$rsn)))*100));
      $p[1]=100-$p[0]."%";
      $p[0].="%";
    }
    if((@$p[0]>100)||(str_replace($st,$st_limit,$rsn)=="0")){
      $p[0]="100";
      $p[1]="0%";
      $p[0].="%";
    }
    $perc="(left, red ".$p[0].", transparent ".@$p[0].")";
    $style='style="background:-webkit-linear-gradient'.$perc.';background:-moz-linear-gradient'.$perc.';background:-o-linear-gradient'.$perc.';background:-ms-linear-gradient'.$perc.';background:linear-gradient'.$perc.';';
//    if((floatval($actv_per[1])>$min[7])&&(floatval($actv_per[1])<$min[9])){
//      $class.="blink";
//    }
//    if((floatval($actv_per[1])>$min[9])&&(floatval($actv_per[1])<$min[10])){
//      $class.="blinkfast";
//    }
//      var_dump($p);
      if((floatval($actv_per[1])>floatval(str_replace($st,$st_limit,$rsn)))&&(str_replace($st,$st_limit,$rsn)!="0")){
        $class.="blinkveryfast";
      }
//    }else{
//      $class.="ring ";
//    }
  }else{
    $class="";
    $style="";
  }
#  $onclick=" onclick='loadExternalContent(\"./dnd2.php?reason=\"+this.id,\"\")'";
  $onclick=" onclick='DND(this.id)'";
  echo "<div id=".$rsn." class='inline button ".$class."' ".$style." title=\"".str_replace($st,$ru_st,$rsn)." Всего:".$sum[$rsn]."\"   ".$onclick."><img class=pic src='./images/".$rsn.".png'>".$arr[$rsn]."</div>";
//  if(($i%$in_row)==0) echo "<br>";
}
$st=array('workout','work');
$ru_st=array('Закончить работу','Начать работу');
if(@$actv_per[0]=='workout'){
  $rsn='work';
  $sum_id='workout';
  $class="active_per";
  $p[0]="100%";
  $perc="(left, red ".$p[0].", transparent ".@$p[0].")";
  $style='style="background:-webkit-linear-gradient'.$perc.';background:-moz-linear-gradient'.$perc.';background:-o-linear-gradient'.$perc.';background:-ms-linear-gradient'.$perc.';background:linear-gradient'.$perc.';';
}else{
  $rsn='workout';
  $sum_id='work';
  $class="";
  $style="";
}
if(!isset($sum[$sum_id])) $sum[$sum_id]="00:00:00";
echo "<div id=".$rsn." class='inline button ".$class."' ".$style." title=\"".str_replace($st,$ru_st,$rsn)."\"   ".$onclick."><img class=pic src='./images/".$rsn.".png'>".$sum[$sum_id]."</div>";
oci_free_statement($stmt);

$res=oci_close($conn);
#if(!$res){
#  error_log("Closing oci session with ".oci_error($conn)) ;
#}else{
#  error_log("Success closing oci session");
#}
?>