<?php
include('auth2.php');
include("checkadmin.php");
include('jiradb.php');
ini_set("display_errors",1);
if((isset($_GET['tid']))&&(isset($_GET['mid']))&&($_GET['tid']!="")&&($_GET['mid']!="")){
  $mid=mysql_real_escape_string($_GET['mid']);
  $tid=mysql_real_escape_string($_GET['tid']);
    $sql="update sl_model set type_id=".$tid." where id=".$mid;
//  echo $sql;
  $res=mysql_query($sql);
//  mysql_query("commit");
  mysql_error();
    echo "V";
}
?>
