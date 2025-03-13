<?php

//Справочник клиентов. обновление и поиск
include_once("../auth2.php");
if($_SERVER['REQUEST_METHOD']=="POST"){
//Если id клиента пустой, то подготовить запрос для создания
  if(empty($_POST['CLIENT_ID'])){
    $sql1="INSERT INTO pbx.clients(client_id,name,type_id) values(pbx.pbx_client_seq.nextval,:name,:cli_type) returning client_id INTO :cli_id";
    if(!empty($_POST['CLIname']) && !empty($_POST['CLItype'])) $stmt=oci_parse($conn,$sql1);
//Запрос для добавления телефона
    if(!empty($stmt) && !empty($_POST['CLIphone'])){
      $sql2="INSERT INTO pbx.cli_phones(phone_num,type_id,client_id) values(:phonenum,8,:cli_id)";
      $stmt1=oci_parse($conn,$sql2);
    }
  }else{
//Если id клиента передан, то подготовить запрос для обновления информации
    $cli_id=$_POST['CLIENT_ID'];
//    error_log("got CLIENT_ID ".$_POST['CLIENT_ID']." ".$cli_id);
    $str=array();
    if(!empty($_POST['CLIname'])) array_push($str,"name=:name");
    if(!empty($_POST['CLItype'])) array_push($str,"type_id=:cli_type");
    if(count($str)>0){
      $sql1="update pbx.clients set ".implode($str,',')." where client_id=:cli_id"; 
      $stmt=oci_parse($conn,$sql1);
    }
    if(!empty($_POST['CLIphone'])){
      $sql2="merge into pbx.cli_phones USING dual ON (client_id=:cli_id) WHEN MATCHED THEN UPDATE set phone_num=:phonenum WHEN NOT MATCHED THEN insert(client_id,type_id,phone_num) values(:cli_id,8,:phonenum)"; 
      $stmt1=oci_parse($conn,$sql2);
    }
  }
//  error_log("after parse ".$cli_id);
// биндим переменные
  if(!empty($_POST['CLIname'])) oci_bind_by_name($stmt,":name",$_POST['CLIname']);
  if(!empty($_POST['CLItype'])) oci_bind_by_name($stmt,":cli_type",$_POST['CLItype']);
  if((count($str)>0 || empty($_POST['CLIENT_ID'])) && !empty($stmt)){
    if(empty($_POST['CLIENT_ID'])){
      oci_bind_by_name($stmt,":cli_id",$cli_id,32);
    }else{
      oci_bind_by_name($stmt,":cli_id",$cli_id);
    }
    oci_execute($stmt);
  }
//  error_log("after stmt ".$cli_id);
  if(!empty($stmt1)){
//    error_log("before bind ".$cli_id);
    oci_bind_by_name($stmt1,":phonenum",$_POST['CLIphone']);
    oci_bind_by_name($stmt1,":cli_id",$cli_id);
//    error_log($sql2." ".$_POST['CLIphone']." ".$cli_id);
    oci_execute($stmt1);
  }
//Проверка на инклюд
  if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    echo "<tr><td><input type=hidden name=CLIid id=CLIid value=".$cli_id.">Имя</td><td>".$_POST['CLIname']."</td></tr><tr><td>Номер телефона</td><td>".$_POST['CLIphone']."</td></tr>";
  }else{
    $_POST['CLIENT_ID']=$cli_id;
  }  
}else{
//эта часть для поиска клиента в базе
  if(!empty($_GET['str'])){
    $sql="SELECT c.client_id, c.name c_name,p.phone_num, t.type_id, t.name t_name FROM pbx.clients c, pbx.cli_phones p, pbx.types t WHERE p.client_id=c.client_id AND t.type_id=c.type_id";
    if(!intval($_GET['str'])){
      $sql.=" and c.name like '%'||:str||'%'";
    }else{
      $sql.=" and p.phone_num like '%'|| :str ||'%'";
    }
    $stmt=oci_parse($conn,$sql); 
    oci_bind_by_name($stmt,":str",$_GET['str']);
    oci_execute($stmt);
    oci_fetch_all($stmt,$data,0,-1,OCI_ASSOC+OCI_RETURN_NULLS+OCI_FETCHSTATEMENT_BY_ROW);
    header('Content-Type: application/json; charset=utf-8');
    $arr=array('action'=>"client",'data'=>$data);
    echo json_encode($arr);
  }
}
