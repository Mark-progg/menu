<?php
include('auth2.php');
$stmt=oci_parse($conn,"select max(clients) keep( dense_rank last ORDER by EVENT_DATE) from phone_statistics where event_date between sysdate - 1/24/60 and sysdate");
oci_execute($stmt);
$cnt=oci_fetch_all($stmt,$row,null,1,OCI_FETCHSTATEMENT_BY_ROW+OCI_NUM);
#var_dump($cnt);
#var_dump($row[0]);
echo $row[0][0];
oci_free_statement($stmt);

oci_close($conn);
?>