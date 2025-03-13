<?php
include("./auth2.php");
ini_set("date.timezone","Europe/Moscow");
$arr=Array("1"=>"Приложение","2"=>"Пользователь","4"=>"Пользователь","3"=>"Установщик","5"=>"Установщик","all"=>"Всем");

$stmt=oci_parse($conn,"select 
        decode(t1.dep_id,3937,'РП-','')||t1.name name, t1.phone_number,	t1.beg_date, t1.status,
	to_char(t1.last_event,'YYYY-MM-DD HH24:MI:SS')||SESSIONTIMEZONE last_event_ts,
	lpad(extract(hour from numtodsinterval(sysdate-t1.last_event,'day')),2,'0') || ':' || lpad(extract(minute from numtodsinterval(sysdate-t1.last_event,'day')),2,'0') || ':' || lpad(round(extract(second from numtodsinterval(sysdate-t1.last_event,'day'))),2,'0') last_event,
	t1.pause,t1.reason,
	lpad(extract(hour from numtodsinterval(sysdate-max(t2.p_end),'day')),2,'0') || ':' || lpad(extract(minute from numtodsinterval(sysdate-max(t2.p_end),'day')),2,'0') || ':' || lpad(round(extract(second from numtodsinterval(sysdate-max(t2.p_end),'day'))),2,'0') last_pause,
	lpad(extract(hour from numtodsinterval(sum(case when t2.rsn='break' then t2.dd end),'day')),2,'0') || ':' || lpad(extract(minute from numtodsinterval(sum(case when t2.rsn='break' then t2.dd end),'day')),2,'0') || ':' || lpad(round(extract(second from numtodsinterval(sum(case when t2.rsn='break' then t2.dd end),'day'))),2,'0') p_break,
	lpad(extract(hour from numtodsinterval(sum(case when t2.rsn='recall' then t2.dd end),'day')),2,'0') || ':' || lpad(extract(minute from numtodsinterval(sum(case when t2.rsn='recall' then t2.dd end),'day')),2,'0') || ':' || lpad(round(extract(second from numtodsinterval(sum(case when t2.rsn='recall' then t2.dd end),'day'))),2,'0') p_recall,
	lpad(extract(hour from numtodsinterval(sum(case when t2.rsn='postcall' then t2.dd end),'day')),2,'0') || ':' ||	lpad(extract(minute from numtodsinterval(sum(case when t2.rsn='postcall' then t2.dd end),'day')),2,'0') || ':' || lpad(round(extract(second from numtodsinterval(sum(case when t2.rsn='postcall' then t2.dd end),'day'))),2,'0') p_postcall,
        t3.data connected, SUBSTR(t3.qn,10) qn,
        (select count(data1) from queue_log t4 where event='MARK' and substr(t4.agent,5)=t1.phone_number and t4.time > to_char(t1.beg_date,'YYYY-MM-DD HH24:MI:SS') and data1='5') marks
from 
  (select pos.dep_id,
       p.name,  
       ph.phone_number,
       ps.status,
       ps.last_event,
       ps.pause,
       ps.reason,
       wl.beg_date
     from phones ph
     join staff s on ph.person_id=s.person_id and ph.person_sid=s.person_sid
     join assignments ass on ass.uname=s.uname and (ass.end_date is null or s.uname = '020998')
     join positions pos on pos.pos_id=ass.pos_id and pos.dep_id in (3763, 3794, 3937) and pos.st_type_id not in (28421,28422,28451)
     join persons p on p.person_id=s.person_id and p.person_sid=s.person_sid
     join phone_status ps on ps.phone_number=ph.phone_number
     left outer join worklogs wl on wl.beg_date > sysdate-2 and wl.end_date is null and wl.UNAME=s.uname and wl.pt_id=1
     where substr(ph.phone_number,1,2)='15'
     order by phone_number) t1  
left outer join (
   select agent,
        time,
	to_date(substr(nvl(lead(time,1) over(order by time),to_char(sysdate,'YYYY-MM-DD HH24:MI:SS')),1,19),'YYYY-MM-DD HH24:MI:SS') p_end,
        to_date(substr(nvl(lead(time,1) over(order by time),to_char(sysdate,'YYYY-MM-DD HH24:MI:SS')),1,19),'YYYY-MM-DD HH24:MI:SS')-to_date(substr(time,1,19),'YYYY-MM-DD HH24:MI:SS') dd,
        event,
	data1 rsn
    from queue_log 
    where event in ('PAUSE','UNPAUSE') 
	and queuename='sl-group-all' 
	and time between to_char(trunc(sysdate-2),'YYYY-MM-DD HH24:MI:SS') 
		and to_char(sysdate,'YYYY-MM-DD HH24:MI:SS')
    order by time) t2 on substr(t2.agent,5)=t1.phone_number and t2.time > to_char(t1.beg_date,'YYYY-MM-DD HH24:MI:SS')
  LEFT OUTER JOIN (
    SELECT callid,
           min(time) time,
           max(agent) keep(DENSE_RANK LAST ORDER BY time) agent,
           min(data1) keep(DENSE_RANK FIRST ORDER BY time) data,
           min(event) keep(DENSE_RANK last ORDER BY time) event,
           min(queuename) keep(dense_rank last order by time) qn
    FROM queue_log 
    WHERE time > to_char(sysdate-1/24/2,'YYYY-MM-DD HH24:MI:SS') 
    GROUP BY callid
    HAVING max(agent) keep(DENSE_RANK LAST ORDER BY time)!='NONE' 
       AND min(event) keep(DENSE_RANK last ORDER BY time) IN ('CONNECT')) t3 ON substr(t3.agent,5)=t1.phone_number and t3.time > to_char(t1.beg_date,'YYYY-MM-DD HH24:MI:SS')
   group by decode(t1.dep_id,3937,'РП-','')||t1.name,
	t1.phone_number,
	t1.beg_date,
	t1.status,
	t1.last_event,
	t1.pause,
	t1.reason,
	t3.data,
	t3.qn
   order by 
   decode(reason,'work',1,'recall',1,'postcall',1,'break',4,'dinner',5,'workout',6,7),
   t1.phone_number");
oci_execute($stmt);
$t['0.4']=0.0004629629;
$t['1']=0.0006944444;
$t['7']=0.0048611111;
$t['9']=0.00625;
$t['10']=0.006944444;
$t['20']=0.013888888;
$st=array('busy','postcall','recall','break','dinner','workout','work');
$ru_st=array('Дела','Поствыз.','Перезвон','Перерыв','Обед','Домой','Работа');
$st_limit=array(0,$t['0.4'],$t['10'],$t['10'],0,0,0);
echo "<table class=left>";
while($row=oci_fetch_array($stmt,OCI_ASSOC)){
   echo "<tbody class='member' ><td  onclick=\"adminPauseMember(this)\" class='button";
   if(@$row['PAUSE']==1 && $row['REASON']!=="recall" && $row['REASON']!=="postcall"){
     echo " orange";
   }else{
#
#0 = Idle
#1 = In Use
#2 = Busy
#4 = Unavailable
#8 = Ringing
#16 = On Hold
#
     switch($row['STATUS']){
       case 0: echo " idle";
        break;
       case 1: echo " InUse";
        break;
       case 2: echo " Busy";
        break;
       case 16: echo " OnHold";
        break;
       case 8: echo " ring";
        break;
       default: echo " unavail";
        break;
     }
   }
  if((@$row['PAUSE']==1)&&($row['REASON']=='workout')) echo " workout"; //&&(@$_SESSION['admin']!=1)
   if (@$row['PAUSE']==1) echo " paused";
   echo "' id='member".$row['PHONE_NUMBER']."' data-number='".$row['PHONE_NUMBER']."'>";
   echo $row['NAME'];
   echo" ".$row['PHONE_NUMBER'];
   echo "<div class='stars inline'>";
   for($i=0;$i<$row['MARKS'];$i++){
     echo "<img class=star style='scale:1'>";
   }
   echo "</div>";
   if(@$row['REASON']=="") $row['REASON']="empty";
   echo "<img class=statusimg width=15 height=15 src='./images/".@$row['REASON'].".png'>";
   if(@$row['CONNECTED']!=""){
     echo "<div class=caller id='cli".$row['CONNECTED']."' style='scale:1;'>";
     echo "<span class=queue>".$arr[$row['QN']]."</span><span>".$row['CONNECTED']."</span><span id='event".$row['DATA']."' class=last_event data-ts='".$row['LAST_EVENT_TS']."'>".$row['LAST_EVENT']."</span>";
     echo "</div>";
   }
   echo "</td><td class='time last_event' title='".str_replace($st,$ru_st,@$row['REASON'])."' id='event".$row['PHONE_NUMBER']."' data-ts='".$row['LAST_EVENT_TS']."'><span>".$row['LAST_EVENT']."</span></td><td>";
   echo "<td class='time last_pause' title='Время с последнего изменения статуса' id='pause".$row['PHONE_NUMBER']."'><img height=16 width=16 src='./images/sandclock.png'><span>".$row['LAST_PAUSE']."</span></td>";
   if(@$_SESSION['admin']==1){
    echo "<td title='".str_replace($st,$ru_st,'break')."' id='break".$row['PHONE_NUMBER']."'";
      if($row['REASON']=="break") echo " class=active_per";
      echo "><img width=16 height=16 src='./images/break.png'><span>".$row['P_BREAK']."</span></td>
        <td title='".str_replace($st,$ru_st,'recall')."' id='recall".$row['PHONE_NUMBER']."'";
      if($row['REASON']=="recall") echo " class=active_per";  
      echo "><img width=16 height=16 src='./images/recall.png'><span>".$row['P_RECALL']."</span></td>
        <td title='".str_replace($st,$ru_st,'postcall')."' id='postcall".$row['PHONE_NUMBER']."'";
      if($row['REASON']=="postcall") echo " class=active_per";
      echo "><img width=16 height=16 src='./images/postcall.png'><span>".$row['P_POSTCALL']."</span></td>";
   }  
   echo "</tr></tbody>\n";
}
echo "</table>";
oci_free_statement($stmt);

$result=oci_close($conn);
#if(!$result){
#  error_log("Error closing oci session with ".oci_error($onn)) ;
#}else{
#  error_log("Success closing oci session");
#}
?>
