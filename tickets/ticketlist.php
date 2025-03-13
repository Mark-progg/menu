<?php
include('../auth2.php');
//Список заявок
include('./can.php');
header('Content-Type: application/json; charset=utf-8');
$f_arr=array();
foreach($_GET as $key=>$val){
  if(!empty($val)) array_push($f_arr,"lower(".$key.") like '%'||lower(:".$key.")||'%'");
}
$f_str="";
if(count($f_arr)>0) $f_str="where ".implode($f_arr,' and ');
$stmt=oci_parse($conn,"select * from (
SELECT 
i.iss_id
,nvl(max(ip1.value) keep(dense_rank last order by ip1.prop_date),' ') subject
,to_char(i.iss_date,'DD.MM.YYYY') iss_date
--,ltrim(i.creator,'0')||' - '||
,substr(pe.name,0,instr(pe.name,' ',1)) creator
,nvl(c.name,' ') name
,nvl(p.phone_num,' ') phone_num
,max(ip.value) keep(DENSE_RANK LAST ORDER BY ip.prop_date) car1
,max(s.name) keep(DENSE_RANK LAST ORDER BY s.prop_date) status
,max(s.type_id) keep(DENSE_RANK LAST ORDER BY s.prop_date) status_id
--,ip.value car
,nvl(a.name,' ') model
,nvl(b.name,' ') reason
,nvl(e.name,' ') type
FROM pbx.issues i
JOIN pbx.clients c ON c.CLIENT_ID=i.client_id
LEFT JOIN pbx.cli_phones p ON p.client_id=i.client_id
LEFT JOIN pbx.iss_prop ip ON ip.issue_id=i.iss_id  AND ip.type_id=63
LEFT JOIN pbx.iss_prop ip1 ON ip1.issue_id=i.iss_id  AND ip1.type_id=65
LEFT JOIN (SELECT ip1.issue_id, t.type_id, t.name, ip1.prop_date FROM  pbx.iss_prop ip1, pbx.types t WHERE t.type_id=ip1.type_id AND t.class_id=100) s ON s.issue_id=i.iss_id
LEFT JOIN (SELECT id.iss_id,d.name FROM pbx.issue_detale id, pbx.detale d WHERE d.detale_id=id.detale_id AND d.type_id=22) a ON a.iss_id=i.iss_id
LEFT JOIN (SELECT id1.iss_id,d1.name FROM pbx.issue_detale id1,pbx.detale d1 WHERE d1.detale_id=id1.detale_id AND d1.type_id=20) b ON b.iss_id=i.iss_id
LEFT JOIN (SELECT id2.iss_id,d2.name FROM pbx.issue_detale id2,pbx.detale d2 WHERE d2.detale_id=id2.detale_id AND d2.type_id=43) e ON e.iss_id=i.iss_id
LEFT JOIN staff s ON s.uname=i.creator
LEFT JOIN persons pe ON pe.person_id=s.person_id AND pe.person_sid=s.person_sid
WHERE i.client_id!=0
GROUP BY i.iss_id,i.iss_date,pe.name,c.name,p.phone_num,a.name,b.name,e.name
order by i.iss_date) ".$f_str);
//error_log($f_str);
foreach($_GET as $key=>$val){
  if(!empty($val)) oci_bind_by_name($stmt,":".$key,$_GET[$key]);
}
oci_execute($stmt);
$arr=array('action'=>"ticketlist");
oci_fetch_all($stmt,$data,0,-1,OCI_ASSOC+OCI_FETCHSTATEMENT_BY_ROW+OCI_RETURN_NULLS);
//error_log($can);
//error_log("can error ".$can->errno);
  $stmt1=$can->prepare("select b.name,m.name,m.id,year_from,year_to,image from model m,brand b where m.id=? and b.id=m.id_brand");
foreach($data as $key=>$row){
//  error_log("can error ".$can->errno);
  if(!empty($row['CAR1'])){
  error_log("car ".$row['CAR1']);
    $stmt1->bind_param("i",$row['CAR1']);
    $stmt1->execute();
    $stmt1->bind_result($bname,$mname,$model_id,$year_from,$year_to,$image);
    $stmt1->fetch();
    $data[$key]['CAR']="<div data-id='".$model_id."' class='inline'>".$bname." ".$mname." ".$year_from."-".$year_to."</div>";
  }else{
    $data[$key]['CAR']="";
  }
}
$arr['data']=$data;
echo json_encode($arr);
//var_dump($arr);
?>