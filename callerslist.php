<?php
include('./auth2.php');
$arr=Array("1"=>"Приложение","2"=>"Пользователь","4"=>"Пользователь","3"=>"Установщик","5"=>"Установщик","all"=>"Всем");
//$stmt=oci_parse($conn,"select * from main.pbx_current_calls");
$stmt=oci_parse($conn,"SELECT SUBSTR(qn,10) qn, substr(numtodsinterval((sysdate-to_date(substr(tim,0,19),'YYYY-MM-DD HH24:MI:SS')),'day'),12,8) tim, substr(tim,0,19)||replace(SESSIONTIMEZONE,':') tim1, dat FROM 
                        (SELECT max(queuename) keep(DENSE_RANK last ORDER BY time) qn, 
                                min(time) keep(dense_rank first order by decode(event,'ENTERQUEUE',1,2),time) tim,
                                min(data1) keep(DENSE_RANK first ORDER BY time) dat,
                                max(event) keep(DENSE_RANK last ORDER BY time) last_evnt
                          FROM queue_log WHERE time > to_char(sysdate-1/72,'YYYY-MM-DD HH24:MI:SS') 
                             and queuename LIKE 'sl-%'
                         GROUP BY callid)
                       WHERE last_evnt in ('ENTERQUEUE','RINGNOANSWER')");
oci_execute($stmt);
//echo "<table>";
while ($row=oci_fetch_array($stmt,OCI_ASSOC)){
  echo "<div class=caller id='cli".$row['DAT']."'><span class=queue>".$arr[$row['QN']]."</span><span>".$row['DAT']."</span><span id='event".$row['DAT']."' class=last_event data-ts='".$row['TIM1']."'>".$row['TIM']."</span></div>";
}
//echo "</table>";
oci_free_statement($stmt);

oci_close($conn);

?>