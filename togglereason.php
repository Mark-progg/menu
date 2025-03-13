<?php
include('auth2.php');
include("checkadmin.php");
include('jiradb.php');
ini_set("display_errors",1);
if((isset($_GET['tid']))&&(isset($_GET['rid']))&&($_GET['tid']!="")&&($_GET['rid']!="")){
  $rid=$_GET['rid'];
  $tid=$_GET['tid'];
  $res=mysql_query("select count(*) cnt from sl_reasons where reason_id=".$rid." and type_id=".$tid);
  $arr=mysql_fetch_array($res);
//  var_dump($arr[0]);
  if($arr[0]!="0"){
    $sql="delete from sl_reasons where reason_id=".$rid." and type_id=".$tid.";";
  }else{
    $sql="insert into sl_reasons values(".$rid.",".$tid.");";
  }
//  echo $sql;
  $res=mysql_query($sql);
//  mysql_query("commit");
  mysql_error();
  $res=mysql_query("select count(*) cnt from sl_reasons where reason_id=".$rid." and type_id=".$tid);
  $arr=mysql_fetch_array($res);
//  var_dump($arr[0]);
  if($arr[0]!="0"){
    echo "V";
  }
}
?>
