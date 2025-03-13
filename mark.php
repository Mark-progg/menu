<?php
include('auth2.php');
header('Content-Type: application/json; charset=utf-8');
if(!empty($_GET['channelid']))
$stmt=oci_parse($conn,"select callid,substr(agent,5) agent,data1 from queue_log where callid=:callid and event='MARK'");
oci_bind_by_name($stmt,":callid",$_GET['channelid']);
oci_execute($stmt);
$row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS);
//var_dump($row);
//if($row!=false){
   echo json_encode(array('action'=>"mark",'data'=>$row));
//}

?>