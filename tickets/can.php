<?php

//Соединение с can.starline.ru для загрузки авто
  $canhost="be01-prod-can.sl.netlo";
  $candb="can";
  $canuser="jira";
  $canpass="322";
  
  $can=new mysqli($canhost,$canuser,$canpass,$candb);
   ->set_charset("utf8");
//  var_dump($can->connect_error);
  
?>