<?php
include('../auth2.php');

//Загрузка зависимых опций из справочника
if(!empty($_GET['detale_id'])){
  $sql="select distinct d3.detale_id,d3.name 
  from pbx.detale d1, pbx.REL_DETALE r1,
     pbx.detale d2, pbx.rel_detale r2,
     pbx.detale d3
  where r1.FROM_ID=d1.detale_id and d2.detale_id=r1.to_id
    and r2.from_id=d2.detale_id and d3.detale_id=r2.to_id
    and d1.DETALE_ID=:id";
  $stmt=oci_parse($conn,$sql);
  oci_bind_by_name($stmt,":id",$_GET['detale_id']);
  oci_execute($stmt);
  while($row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC)){
    echo "<option value=".$row['DETALE_ID'];
//    if(!empty($_GET['match_id']) && $_GET['match_id']==$row['DETALE_ID']) echo " selected";
    echo ">".$row['NAME']."</option>";
  }
}