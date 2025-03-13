<?php
include("../auth2.php");
include("./can.php");

$stmt=$can->prepare("select min(m.id),b.name,m.name,year_from,year_to,image from model m,brand b where b.id=? and b.id=m.id_brand group by b.name,m.name,year_from,year_to order by m.name");
$stmt->bind_param("i",$_GET['brand_id']);
$stmt->execute();
$stmt->bind_result($model_id,$brand_name,$model_name,$year_from,$year_to,$image);
while($stmt->fetch()){
  echo "<div data-id=".$model_id." class='inline car_model' onclick='selectCar(this)'><div><img src=https://can.starline.ru/data/model_images/thumb_128_96_".$image."></div>".$brand_name." ".$model_name." ".$year_from."-".$year_to."</div>";
}
?>