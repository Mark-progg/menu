<?php
include("./auth2.php");

if($_SERVER["REQUEST_METHOD"]=="GET"){
  if(! isset($_GET["url"])){
    echo "Bad Request url";
    exit;
  }
  echo "<form action='./discuss.php' method=post>";
  echo "<input type=hidden name=url value='".$_GET['url']."'>";
  echo "Коментарий:<br>";
  echo "<textarea name=comment></textarea>";
  echo "<input type=submit>";
  echo "</form>";
}elseif($_SERVER["REQUEST_METHOD"]=="POST"){
  if(! isset($_POST["url"])){
    echo "Bad Request url";
    exit;
  }
#    $url=rawurldecode($_POST['url']);
#  var_dump($_SESSION);
  $to="vidman@starline.ru,ace@starline.ru";
#  $to="ace@starline.ru";
  $body="Запись разговора:<br><a href='".$_POST["url"]."'>".$_POST["url"]."</a><br><br>Коментарий от пользователя ".$_SESSION['name'].":<br>".$_POST["comment"];
  $from="root@pbx.ultrastar.ru";
  $subject="Комментарий к оценке разговора";
  $headers  = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
  mail($to,$subject,$body,$headers,'-f'.$from);
  echo "Комментарий отправлен.";
}else{
  echo "Bad Request";
}
oci_close($conn);
?>
