<?php
include('./auth2.php');
$stmt=oci_parse($conn,"select to_char(TO_DATE('1970-01-01', 'YYYY-MM-DD')+numtodsinterval(substr(a.callid,1,instr(a.callid,'.',1)-1),'second')+3/24,'HH24:MI:SS') time,
                              a.phone_number 
                        from (select q.callid,
                                    min(data2) keep(dense_rank first order by q.time) phone_number,
                                    max(q.AGENT) keep(dense_rank last order by q.time) agent
                                from queue_log q
                                where q.callid between to_char((trunc(sysdate) - TO_DATE('1970-01-01', 'YYYY-MM-DD')-3/24)*86400) and to_char((sysdate - TO_DATE('1970-01-01','YYYY-MM-DD')-3/24)*86400)
                                   and q.queuename like 'sl-%'
                                   and q.event in ('ENTERQUEUE','CONNECT')
                                group by q.callid) a,phones ph,staff s
                        where a.agent='SIP/'||ph.phone_number
                          and s.person_id=ph.person_id
                          and s.person_sid=ph.person_sid
                          and s.uname=substr(user,3)");
oci_execute($stmt);
echo "<table>";
while ($row=oci_fetch_array($stmt,OCI_ASSOC)){
  echo "<tbody class=grey onclick=call('".$row['PHONE_NUMBER']."')><tr><td>".$row['TIME']."</td><td>".$row['PHONE_NUMBER']."</td></tr></tbody>";
}
echo "</table>";
oci_free_statement($stmt);
oci_close($conn);
?>