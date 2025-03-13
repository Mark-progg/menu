<?php
include('auth2.php');
 $stmt=oci_parse($conn,"select count(*) 
                          from queue_log q,
                               staff s,
                               phones ph 
                          where q.event='CONNECT' 
                            and q.agent='SIP/'||ph.phone_number 
                            and s.uname=substr(user,3)
                            and ph.person_id=s.person_id
                            and ph.person_sid=s.person_sid 
                            and q.time between to_char(trunc(sysdate),'YYYY-MM-DD HH24:MI:SS') and to_char(sysdate,'YYYY-MM-DD HH24:MI:SS')");
 oci_execute($stmt);
 $row=oci_fetch_array($stmt,OCI_NUM); 
 echo  $callnum=$row[0];
 oci_free_statement($stmt);
 
 oci_close($conn);
?>