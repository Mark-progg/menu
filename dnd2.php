<?php
include("./auth2.php");
$tz=new DateTimeZone("Etc/GMT-3");
$now= new DateTime("now",$tz);
$s1=new DateTime("12:30",$tz);
$s2=new DateTime("17:00",$tz);
$e1=new DateTime("15:00",$tz);
$e2=new DateTime("19:00",$tz);
$time=array(1=>array(new DateTime("12:30",$tz),new DateTime("15:00",$tz)),
            2=>array(new DateTime("17:00",$tz),new DateTime("19:00",$tz))
      );
$w_opers=array(1=>array(1,4),
               2=>array(5,6),
               3=>array(7,10),
               4=>array(11,13),
               5=>array(14,16),
               6=>array(17,19),
               7=>array(20,23)
         );
function check_range($val,$arr){
  foreach($arr as $key=>$range){
    if(count($range)!=2) return 999;
    if(($val>=$range[0])&&($val<=$range[1])){
      return $key;
    }else{
      continue;
    }
  }
  return 999;
}
$ami='/var/www/menu/bin/ami';
$reason='';
$queue_list=array("sl-group-1",
                  "sl-group-2",
                  "sl-group-3",
                  "sl-group-4",
                  "sl-group-5",
                  "sl-group-all");

if((isset($_GET['reason']))||(@$_GET['reason']!='')){
  $reason.=" reason ".$_GET['reason'];
}
if ((@$_SESSION['admin']=="1")&&(isset($_GET['exten']))){
  $user=$_GET['exten'];
  $reason=' reason admin';
}else{
#  $user=$_SESSION['phone_number'];
}
$stmt=oci_parse($conn,"select ps.phone_number,
                              ps.pause,
                              ps.reason,
                              check_parameter('FSP','BUSY_PAUSE_ACCESS') param
                         from phone_status ps,
                              phones ph,
                              staff s
                         where (s.uname=substr(nvl(:tt,user),3) or ph.phone_number=:tt)
                           and ph.person_id=s.person_id
                           and ph.person_sid=s.person_sid
                           and ps.phone_number=ph.phone_number
                           and substr(ph.phone_number,1,2)='15'");
oci_bind_by_name($stmt,":tt",$user);
oci_execute($stmt);
$cnt=oci_fetch_all($stmt,$state,null,1,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC);
oci_free_statement($stmt);

#/var/www/menu-new/bin/ami -c 10.4.0.100 5038 'queue show sl-group-all' ./temp|grep 1503|awk {'print $5'}
#$s=exec($ami.' -c 10.4.0.100 5038 "queue show sl-group-all"|grep '.$user.'|awk {"print $5"}');

#error_log($cnt);
#var_dump($state);
$exten=$state[0]['PHONE_NUMBER'];
if ($state[0]['PAUSE']==1){
 echo "Unausing member ".$exten;
// system('$ami -c pbx.ultrastar.ru 5038 "queue unpause member SIP/'.$username.'" ./test') ;
  $operation="unpause";
  $reason=" reason work";
}else{
 if($_GET['reason']=="postcall"){
   echo "Not allowed";
   exit;
 }
 
// var_dump(($now<=$e1));
// var_dump($_GET['reason']);
// var_dump($now);
// var_dump($e1);
/* if(($_GET['reason']=="break")&&((($now>=$s1)&&($now<=$e1))||(($now>=$s2)&&($now<=$e2)))){
   echo "Not allowed in this time";
   exit;
 }*/
 $stmt=oci_parse($conn,"select sum(case when ps.reason not in ('workout','admin','busy') then 1 else 0 end),
      sum(case when ps.reason in ('dinner','break','recall') then 1 else 0 end),
      sum(case when ps.reason in ('break') then 1 else 0 end),
      sum(case when ps.reason in ('recall') then 1 else 0 end)
from phone_status ps,
      phones ph,
      staff s,
      positions pos,
      assignments ass
  where s.uname=ass.uname
    and pos.pos_id=ass.pos_id
--    and ass.beg_date is not null
    and ass.end_date is null
--    and pos.dep_id in (3763,3794,3937)
    and pos.dep_id in (3763,3794)
    and pos.st_type_id not in (28421,28422,28451)
    and ph.person_id=s.person_id
    and ph.person_sid=s.person_sid
    and ps.phone_number=ph.phone_number
    and substr(ph.phone_number,1,2)='15'");
oci_execute($stmt);
 $cnt=oci_fetch_all($stmt,$p,null,1,OCI_FETCHSTATEMENT_BY_ROW+OCI_NUM);
 oci_free_statement($stmt);
# if(($_GET['reason']=="recall")&&($p[0][3]>=1)&&((($now>=$s1)&&($now<=$e1))||(($now>=$s2)&&($now<=$e2)))){
 if( ((check_range($now,$time)!=999) && ($_GET['reason']=='break')) || 
//     (($p[0][1]>=check_range($p[0][1],$w_opers)) && ($_GET['reason']!='workout') && ($_GET['reason']!='busy')) || 
     (($_GET['reason']=='break') && ($p[0][2]>=3)) || 
     (($_GET['reason']=='recall') && ($p[0][3]>=3)) ){
   echo "Not allowed in this time";
   exit;
 }
 $c=check_range($p[0][0],$w_opers);
// error_log("Allowed only ".$c."/".$p[0][0]." operators in pause now");
 if(($p[0][1]>=$c) && ($_GET['reason']!='workout') && ($_GET['reason']!='busy')){
   echo "Allowed only ".$c." operators in pause now";
//   error_log("Allowed only ".$c."/".$p[0][1]." operators in pause now");
   exit;
 }
      
 
 $stmt=oci_parse($conn,"select case when max(t.dep_id) in (3937,4019) then -1 else nvl(1/24/6-to_number(sysdate-to_date(substr(max(t.e_time),1,19),'YYYY-MM-DD HH24:MI:SS')),0) end
                          from (
                            select pos.dep_id,
                                   q.data1,
                                   lead(q.time) over(partition by q.agent order by q.time) e_time 
                            from queue_log q,
                                 phones ph,
                                 staff s,
                                 assignments ass,
                                 positions pos
                            where substr(q.agent,5)=ph.phone_number
                              and ph.person_id=s.person_id
                              and ph.person_sid=s.PERSON_SID
                              and s.uname=ass.uname
                              and pos.POS_ID=ass.POS_ID
                              and ass.end_date is null
                              and (s.uname=substr(nvl(:tt,user),3) or ph.phone_number=:tt) 
                              and q.time between to_char(trunc(sysdate),'YYYY-MM-DD HH24:MI:SS') and to_char(sysdate,'YYYY-MM-DD HH24:MI:SS') 
                              and q.event in ('PAUSE','UNPAUSE')
                              and q.queuename='sl-group-all') t
                          where t.data1 in ('dinner','break','recall')");
  oci_bind_by_name($stmt,":tt",$user);
  oci_execute($stmt);
  $cnt=oci_fetch_all($stmt,$l,null,1,OCI_FETCHSTATEMENT_BY_ROW+OCI_NUM);
  oci_free_statement($stmt);
  
/*  echo round($p[0][0]/3)." ".$p[0][1]." ".$l[0][0]." ";
  
 if( ($p[0][1]>round($p[0][0]/3)) || (($p[0][2]>=2)&&($reason==" reason brake")) || (($p[0][3]>=3)&&($reason==" reason recall")) ){
  echo "Pausing not allowed with".$reason;
  exit;
 }*/
/* if(($l[0][0]>0)&&(@$_SESSION['admin']!=1)&&($reason!=" reason workout")&&($reason!=" reason work")&&($reason!=" reason dinner")){ //&&($reason!=" reason busy")
   echo "Less than 10 minutes from the last pause";
   exit;
 }*/
 if(($reason==" reason busy")&&($state[0]['PARAM']!="TRUE")){ 
   echo "Pausing not allowed with reaon ".$reason;
   exit;
 }
 echo  "Pausing member ".$exten;
 $operation="pause";
}
foreach($queue_list as $queue){
  exec($ami.' -c '.$ast_ip.' 5038 "queue '.$operation.' member SIP/'.$exten.' queue '.$queue.' '.$reason.'" ./test &> /dev/null') ;
}
oci_close($conn);

?>