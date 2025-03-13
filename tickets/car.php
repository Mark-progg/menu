<?php
//Справочник авто в can.starline.ru
include_once('/var/www/menu/auth2.php');
include_once('/var/www/menu/tickets/can.php');
if(!empty($_GET['CAR'])){
  $stmt1=$can->prepare("select b.name,m.name,m.id,year_from,year_to,image from model m,brand b where m.id=? and b.id=m.id_brand");
//  error_log($can->error);
//  error_log($_GET['CAR']);
  $stmt1->bind_param("i",$_GET['CAR']);
  $stmt1->execute();  
  $stmt1->bind_result($bname,$mname,$model_id,$year_from,$year_to,$image);  
//var_dump($_GET);
  while($stmt1->fetch()){
//  var_dump($model_id);
    echo "<div data-id='".$model_id."' class='inline";
    if(!empty($_GET['img']) && $_GET['img']!=0){ 
      echo " car_model ok' onclick='selectCar(this)'>";
    }else{
      echo "'>";
    }
    if(!empty($_GET['img']) && $_GET['img']!=0) echo "<div><img src=https://can.starline.ru/data/model_images/thumb_128_96_".$image."></div>";
    echo $bname." ".$mname." ".$year_from."-".$year_to."</div>";
  }
}
?>