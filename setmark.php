<?php
include('auth2.php');
#var_dump($_GET);
//if(@$_GET['mark']==1) $_GET['comment']="";
$stmt=oci_parse($conn,"begin
  set_raiting(:cid, :seq, :mark, :opt, :comment);
  end;");
oci_bind_by_name($stmt,":cid",$_GET['cid']);
oci_bind_by_name($stmt,":seq",$_GET['seq']);
oci_bind_by_name($stmt,":mark",$_GET['mark']);
oci_bind_by_name($stmt,":opt",$_GET['opt']);
oci_bind_by_name($stmt,":comment",$_GET['comment']);
//oci_bind_by_name($stmt,":opt",@$_GET['opt'])
oci_execute($stmt);
//var_dump($_GET);
oci_free_statement($stmt);

oci_close($conn);
?>