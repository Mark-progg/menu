<?php

//Соединение с can.starline.ru для загрузки авто
  $canhost="be01-prod-can.sl.netlo";
  $candb="can";
  $canuser="jira";
  $canpass="8vaXzPYv3R8Y";
  
  $can=new mysqli($canhost,$canuser,$canpass,$candb);
  $can->set_charset("utf8");
//  var_dump($can->connect_error);
  
?>