<?php
include('../auth2.php');
//Поиск записей разговоров
if(!empty($_GET['str']) && intval($_GET['str'])){
//  $_GET['str']="'".$_GET['str']."'";
  $sql="SELECT to_char(call_date,'DD.MM.YYYY') call_date,call_id,agent,phonenum, substr(filename,instr(filename,'/',1,4)+1) filename FROM callrecords WHERE phonenum=:str and call_date > sysdate - 62";
  $stmt=oci_parse($conn,$sql);
  oci_bind_by_name($stmt,":str",$_GET['str'],-1,SQLT_CHR);
  oci_execute($stmt);
  oci_fetch_all($stmt,$data,0,-1,OCI_ASSOC+OCI_RETURN_NULLS+OCI_FETCHSTATEMENT_BY_ROW);
//  var_dump($sql);
//  var_dump($_GET['str']);
  $arr=array('action'=>"records",'data'=>$data);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($arr);
}

?>