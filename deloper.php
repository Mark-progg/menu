<?php
include('./auth2.php');
include('./checkadmin.php');
$stmt=oci_parse($conn,"begin set_dostup(:user, 1,  'PHONE_RATING_RW', :oper); end;");
oci_bind_by_name($stmt,":user",$_GET['user']);
oci_bind_by_name($stmt,":oper",$_GET['oper']);
$result=oci_execute($stmt);
oci_free_statement($stmt);

oci_close($conn);
?>