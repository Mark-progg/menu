<?php
include('../auth2.php');
//Справочник операторов
if($_SERVER['REQUEST_METHOD']=='GET'){
  $stmt=oci_parse($conn,"select ltrim(s.uname,'0') uname, substr(p.name,0,instr(p.name,' ',-1)) name from staff s, persons p, positions pos, ASSIGNMENTS ass 
  where p.person_id=s.person_id and p.person_sid=s.person_sid and (s.uname like '%'|| :str||'%' or p.name like '%'||:str||'%') AND ass.uname=s.uname AND pos.pos_id=ass.pos_id AND pos.dep_id=3763 and ass.end_date is null");
  oci_bind_by_name($stmt,":str",$_GET['str']);
  oci_execute($stmt);
  oci_fetch_all($stmt,$data,0,-1,OCI_ASSOC+OCI_FETCHSTATEMENT_BY_ROW);
//  while($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS)){
//    echo "<option value=".$row['UNAME'].">".$row['UNAME']." - ".$row['NAME']."</option>";
//  }
  $hint="";
  if(count($data)==0) $hint="Не найдено";
  header('Content-Type: application/json; charset=utf-8');
  $arr=array('action'=>"user",'hint'=>$hint,'data'=>$data);
  echo json_encode($arr);
}