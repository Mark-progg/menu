<?
include('../auth.php');
$stmt=oci_parse($conn,"SELECT unique d4.detale_id, d4.name 
FROM pbx.detale d
LEFT OUTER JOIN pbx.rel_detale d1 on d1.from_id=d.detale_id
LEFT OUTER JOIN pbx.detale d2 ON d2.detale_id=d1.to_id
LEFT OUTER JOIN pbx.rel_detale d3 ON d3.from_id=d2.detale_id
LEFT OUTER JOIN pbx.detale d4 ON d4.detale_id=d3.to_id
WHERE d.detale_id=:detale");
oci_bind_by_name($stmt,":detale",$_GET['DETALE']);
oci_execute($stmt);
while($row=oci_fetch($stmt,OCI_ASSOC+OCI_RETURN_NULLS)){
  echo "<option value=".$row['DETALE_ID'],">".$row['NAME'],">";
}