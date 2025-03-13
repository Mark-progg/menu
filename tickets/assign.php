<?php
include('../auth2.php');
//Назначить исполнителя заявки
header('Content-Type: application/json; charset=utf-8');
$res=false;
$log="";
$arr=array("action"=>"assign");
if(!empty($_GET['uname']) && !empty($_GET['iss_id'])){
  $stmt=oci_parse($conn,"insert into pbx.iss_prop(issue_id,type_id,value,prop_date,long_text,uname) values(:iss_id,160,:uname,sysdate,EMPTY_BLOB(),substr(user,3))");
  oci_bind_by_name($stmt,":iss_id",$_GET['iss_id']);
  oci_bind_by_name($stmt,":uname",$_GET['uname']);
  $res=oci_execute($stmt);
  if($res){
    $arr["S"]=array("Заявка назначена на ".$_GET['uname']);
  }else{
    $arr["E"]=array("Ошибка. Заявка не назначена.");
  }
  $arr["data"]=$res;
}

echo json_encode($arr);
?>