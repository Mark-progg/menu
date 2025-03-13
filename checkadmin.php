<?php
$stmt=oci_parse($conn,"select check_parameter('FSP','FSP_GRANT') from dual");
oci_execute($stmt);
oci_fetch_all($stmt,$row,null,null,OCI_FETCHSTATEMENT_BY_ROW+OCI_NUM);
//var_dump($row);
if($row[0][0]=="FALSE") die("You don't have any access to this page");
oci_free_statement($stmt);


?>