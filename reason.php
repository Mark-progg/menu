<?php
include("./auth2.php");
header('Content-Type: application/json; charset=utf-8');
ini_set("date.timezone","Europe/Moscow");
$stmt=oci_parse($conn,"select * from phone_status where phone_number=:num");
oci_bind_by_name($stmt,":num",@$_GET['num']);
oci_execute($stmt);
$row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC);
echo json_encode($row);
oci_close($conn);
?>