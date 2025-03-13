<?php
include('auth2.php');
$ami="./bin/ami";
exec($ami." -c ".$ast_ip." 5038 'database show blacklist' ./test|grep blacklist|awk -F\: {'print \$2\" \"\$3'}|sed -e \"s:\/blacklist\/::g\"",$out);
if(@$_SESSION['ban_access']==1){
  echo "<div><input id=bl_phonenum name=bl_phonenum placeholder='Номер телефона' size=14> <input id=bl_reason name=bl_reason placeholder='Причина бана'><button onclick='add2bl()'>Ok</button></div>"; 
}
echo "<div id=bl><table>";
//var_dump($out);
foreach($out as $row){
  $ar=preg_split('/\s{10,}/', trim($row));//explode(' ',$row);
  echo "<tr><td>".$ar[0]."</td><td>".$ar[1];
  if(@$_SESSION['ban_access']==1){
    echo "<span class=red style='cursor:pointer' onclick='delFromBl(\"".trim($ar[0])."\")'> x </span>";
  }
  echo "</td></tr>";
}
echo "</table></div>";
$res=oci_close($conn);
if(!$res){
  error_log("Error closing oci session with ".oci_error($conn));
}
?>