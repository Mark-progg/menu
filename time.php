<?php
include('auth2.php');
header('Content-Type: application/json; charset=utf-8');
$stmt=oci_parse($conn,"select to_char(sysdate,'YYYY-MM-DD HH24:MI:SS')||SESSIONTIMEZONE tim from dual");
oci_execute($stmt);
$row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS);
//var_dump($row);
echo json_encode(array('action'=>"time",'time'=>$row['TIM']));
?>