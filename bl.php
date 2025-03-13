<?php
  include('auth2.php');
  ini_set("display_errors",1);
  if(@$_GET['reason']!="") $_GET['reason']="\"$_GET[reason]\"";
  if($_GET['action']=='del') $_GET['reason']="";
  $cmd="./bin/ami -c ".$ast_ip." 5038 'database ".$_GET['action']." blacklist ".$_GET['phonenum']." ".@$_GET[reason]."' ./deltest";
  if((isset($_GET['phonenum']))&&(isset($_GET['action'])))  exec($cmd);
  file_get_contents("./deltest");
//  echo "$_GET[phonenum] $_GET[action] ok";
//  echo $out[0];
oci_close($conn);
?>
