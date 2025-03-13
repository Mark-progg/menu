<?php
//Справочник городов
include_once('../auth2.php');
  $sql="select ct.city_id,ct.city_name,rg.region_name,cn.note from main.pbx_city ct, main.pbx_regions rg, main.pbx_countries cn 
  where ct.region_id=rg.region_id and rg.country_id=cn.country_id";
if(!empty($_GET['str'])){
    $sql .=" and lower(ct.city_name) like '%'||lower(:str)||'%'";
    $stmt=oci_parse($conn,$sql);
    oci_bind_by_name($stmt,":str",$_GET['str']);
}else if(!empty($_GET['cityid'])){
  $sql.=" and ct.city_id=:id";
  $stmt=oci_parse($conn,$sql);
  oci_bind_by_name($stmt,":id",$_GET['cityid']);
}else{
  $stmt=oci_parse($conn,$sql);
}
  oci_execute($stmt);
  while($row=oci_fetch_array($stmt,OCI_RETURN_NULLS+OCI_ASSOC)){
    echo "<option value=".$row['CITY_ID'];
//    if(!empty($_GET['match_id']) && $_GET['match_id']==$row['DETALE_ID']) echo " selected";
    echo ">".$row['CITY_NAME'].", ".$row['REGION_NAME'].", ".$row['NOTE']."</option>";
  }
