<?php
include('./auth2.php');
#session_start();
$ami="/var/www/menu/bin/ami";
$phonenumber=trim($_POST['phonenumber']);
if (strlen($phonenumber)>10) {
  if(substr($phonenumber,0,4)!='9810'){
    $num='9810'.$phonenumber;
  }else{
    $num=$phonenumber;
  }
}elseif (strlen($phonenumber)>7) {
    $num='98'.$phonenumber;
}elseif (strlen($phonenumber)>4) {
  $num='9'.$phonenumber;
}else{
  $num=$phonenumber;
}

$stmt=oci_parse($conn,"select ph.phone_number from phones ph,staff s where ph.person_id=s.person_id and ph.person_sid=s.person_sid and s.uname=substr(user,3) and ph.phone_number like '15%'");
oci_execute($stmt);
$cnt=oci_fetch_all($stmt,$state,null,1,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC);
echo $state[0]['PHONE_NUMBER'].' Исходящий звонок на '.$num.'...';
# if(($state[0]['PAUSE']==0)&&(@$_SESSION['admin']==0)){
#   echo "not allowed";
# }else{
   error_log($ami." -o ".$ast_ip." 5038 local/".$state[0]['PHONE_NUMBER']."@sl-sip ".$num." /var/www/menu/tmp/".$num);
   exec($ami." -o ".$ast_ip." 5038 local/".$state[0]['PHONE_NUMBER']."@sl-sip ".$num." /var/www/menu/tmp/".$num);
# }
 oci_free_statement($stmt);
 
oci_close($conn);
?>