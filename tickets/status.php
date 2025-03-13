<?php
include_once('../auth2.php');
header('Content-Type: application/json; charset=utf-8');
$arr=array('action'=>"status");
if(!empty($_GET['id']) && !empty($_GET['status'])){
  $stmt=oci_parse($conn,"INSERT INTO pbx.iss_prop(issue_id,TYPE_id,value,prop_date) values(:id,:stat,(SELECT name FROM pbx.types WHERE type_id=:stat),sysdate) returning type_id,value into :tid,:val");
  oci_bind_by_name($stmt,":id",$_GET['id']);
  oci_bind_by_name($stmt,":stat",$_GET['status']);
  oci_bind_by_name($stmt,":tid",$tid,16);
  oci_bind_by_name($stmt,":val",$val,16);
  $res=oci_execute($stmt);
  if($res){
    $arr['id']=$tid;
    $arr['name']=$val;
    $arr['S']=array("Статус заявки ".$_GET['id']." изменен.");
  }else{
    $arr['E']=array("Ошибка смены статуса");
  }
//  echo "<script type=text/javascript> st=document.querySelector('#status'); st.class='inline s'".$tid."';st.innerHTML='".$val."';</script>";
//  var_dump($val);
}
  echo json_encode($arr);

?>