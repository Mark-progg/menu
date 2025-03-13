<?php
include("auth2.php");
#var_dump($_GET);
$stmt=oci_parse($conn,"select to_char(cr.call_date,'hh24:mi:ss') call_time,
                                to_char(cr.call_date,'DD/MM/YYYY') call_date, 
                                cr.agent,
                                cr.phonenum,
                                (select sum(value) from callrec_prop where call_id=cr.call_id group by call_id) sum_val,
                                pp.value email,
                                cp.seq,
                                p.name,
                                cp.value,
                                cp.note
                            from callrecords cr,
                              CALLREC_PROP cp, 
                              phones ph,
                              person_prop pp,
                              persons p,
                              staff s
                            where cp.CALL_ID=cr.CALL_ID 
                              and cr.call_id=:tt 
                              and ph.PHONE_NUMBER=cr.agent
                              and pp.PERSON_ID=ph.PERSON_ID
                              and pp.PERSON_SID=ph.PERSON_SID
                              and s.uname=cp.uname
                              and p.person_id=s.person_id
                              and p.person_sid=s.person_sid
                              and pp.PROP_ID=958");
//                            group by cr.call_date,cr.agent,cr.PHONENUM,(select sum(cp.value) from callrec_prop where call_id=cr.call_id group by call_id),pp.value,cp.note,cp.seq,p.name,cp.value,cp.note");
oci_bind_by_name($stmt,":tt",$_GET['call_id']); 
oci_execute($stmt);
$cnt=oci_fetch_all($stmt,$row,null,null,OCI_FETCHSTATEMENT_BY_COLUMN + OCI_ASSOC);
$body="";
$from="root@pbx.ultrastar.ru";
$subject="Оценка разговора";
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
$url="http://".$_SERVER['SERVER_NAME']."/records.php?startdate=".$row['CALL_DATE'][0]."&enddate=".$row['CALL_DATE'][0]."&agent=".$row['AGENT'][0]."&client=".$row['PHONENUM'][0];
$codedurl=rawurlencode($url);
$discussurl=
var_dump($codedurl);
#while($row=oci_fetch_array($stmt,OCI_ASSOC)){
if($cnt>0){
  $to=$row['EMAIL'][0].', malyshkin@starline.ru, novak.av@starline.ru, novak.rv@starline.ru, vidman@starline.ru, sizov.aa@starline.ru, salkov@starline.ru, rudnitskii@starline.ru';
#  $to='ace@starline.ru';
  $body="<html><body>Вам поставили оценку ".$row['SUM_VAL'][0]." за разговор с 
                <a href='".$url."'>".$row['PHONENUM'][0]."</a>
              ".$row['CALL_DATE'][0]." ".$row['CALL_TIME'][0]."<br><br>";
  for($i=0;$i<$cnt;$i++){
    $body.=$row['NAME'][$i].":".$row['VALUE'][$i]."<br>";
    if(($row['NOTE'][$i]!="")||($row['NOTE'][$i]!="null")||($row['NOTE'][$i]!=NULL)) $body.=$row['NOTE'][$i]."<br>";
  }
  $body.="Если у Вас имеется комментарий к оценке, перейдите по <a href='http://".$_SERVER['SERVER_NAME']."/discuss.php?url=".$codedurl."'>ссылке</a></body></html>";
  mail($to,$subject,$body,$headers,'-f'.$from);
}
oci_free_statement($stmt);

oci_close($conn);
?>