<?php
include("auth2.php");
$stmt=oci_parse($conn,"select to_char((numtodsinterval(callid,'second')+TO_DATE('1970-01-01', 'YYYY-MM-DD')+3/24),'HH24:MI:SS') cid,
                            phone_number
  from (
    select callid,max(event) keep(dense_rank last order by time) event,min(data2) keep(dense_rank first order by time) phone_number,max(data3) keep(dense_rank last order by time) data3
      from queue_log 
      where callid between to_char((trunc(sysdate) - TO_DATE('1970-01-01', 'YYYY-MM-DD')-3/24)*86400) 
                       and to_char((sysdate - TO_DATE('1970-01-01','YYYY-MM-DD')-3/24)*86400)
          and queuename like 'sl-group%'
          and event!='PRESS'
      group by callid)
   where event in ('EXITWITHTIMEOUT','EXITWITHKEY','ABANDON') AND NOT (event='ABANDON' AND  data3<15)
   order by cid desc");

oci_execute($stmt);
$cnt=oci_fetch_all($stmt,$arr,null,null,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC);
  

              
echo '<div class="left"><div class=inline style="margin-right:1em;">'.$cnt.'</div><div class=inline>Пропущенных</div></div>';
foreach($arr as $row){
//while ($row=$db->fetch_array()){
  echo '<div class=grey id="missed'.$row['PHONE_NUMBER'].'" onclick=call("'.$row['PHONE_NUMBER'].'")><div class=inline style="margin-right:1em;">'.$row['CID'].' </div><div class=inline style=width:8em;>'.$row['PHONE_NUMBER'].'</div></div>';
//  if (isset($row['file'])){
//    echo '<tr><td colspan=2 align=right> <a href="http://pbx.ultrastar.ru:8000/'.$row['file'].'.wav">Play message</a>';
//    echo '</td></tr>';
//  }
}
//echo '</table>';
oci_free_statement($stmt);

oci_close($conn);
?>
