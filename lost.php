<?php
include("auth2.php");
$stmt=oci_parse($conn,"select count(distinct event_date) 
                        from event_log el,phones ph,staff s
                        where CLASS_ID=39 
                          and el.EVENT_DATE between trunc(sysdate) and sysdate 
                          and el.action='LOST incomming call'
                          and el.USERNAME=ph.PHONE_NUMBER
                          and s.PERSON_ID=ph.PERSON_ID
                          and s.PERSON_SID=ph.PERSON_SID
                          and s.uname=substr(user,3)");
oci_execute($stmt);

$cnt=oci_fetch_all($stmt,$row,null,1,OCI_FETCHSTATEMENT_BY_ROW+OCI_NUM);
#var_dump($cnt);
#var_dump($row);
echo $row[0][0];
oci_free_statement($stmt);

oci_close($conn);
?>