<?php
include('auth2.php');
 $stmt=oci_parse($conn,"select count(*) 
 from cdr c,
   staff s,
   phones ph 
 where c.src=ph.phone_number
--   and c.billsec >0
   and s.uname=substr(user,3)
   and ph.person_id=s.person_id
   and ph.person_sid=s.person_sid
   and c.calldate between trunc(sysdate) and sysdate
   and c.dcontext='sl-sip'
   and c.channel like 'SIP/15%'");
 oci_execute($stmt);
 $row=oci_fetch_array($stmt,OCI_NUM);
 echo $row[0];
 oci_free_statement($stmt);
 
oci_close($conn);
?>