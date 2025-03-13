<?php
putenv("ORACLE_HOME=/opt/oracle10");
putenv("LD_LIBRARY_PATH=/opt/oracle10/lib");
putenv("NLS_LANG=american_america.UTF8");

$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 10.1.10.29)(PORT = 1521)))(CONNECT_DATA=(SID=MASTER)))";
  $conn = oci_connect('wt016018', 'hjvfynbr', $db);
  if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
  }
/*$sql="SELECT ass.uname, max(end_date) keep(DENSE_RANK last ORDER BY ass.beg_date) END_date,ph.phone_number,max(pos.dep_id) keep(DENSE_RANK LAST ORDER BY ass.beg_date) pos_id
FROM ASSIGNMENTS ass
LEFT outer JOIN positions pos ON pos.pos_id=ass.pos_id AND pos.dep_id=3763
INNER JOIN staff st ON st.uname=ass.uname
LEFT OUTER  JOIN phones ph ON ph.person_id=st.person_id AND ph.person_sid=st.person_sid AND length(ph.phone_number)=4
GROUP BY ass.uname, ph.phone_number
HAVING max(pos.dep_id) keep(DENSE_RANK LAST ORDER BY ass.beg_date) = 3763 AND max(end_date) keep(DENSE_RANK last ORDER BY ass.beg_date) IS NOT NULL AND substr(ph.phone_number,0,2)='15'";*/
$sql="select nvl(to_char(max(end_date) keep(DENSE_RANK last ORDER BY ass.beg_date),'DD.MM.YYYY'),'Занят') END_date,ph.phone_number,max(pos.dep_id) keep(DENSE_RANK LAST ORDER BY ass.beg_date) pos_id 
from phones ph
INNER JOIN staff st ON st.person_id=ph.person_id and st.person_sid=ph.person_sid
left outer join assignments ass on ass.uname=st.uname
LEFT outer JOIN positions pos ON pos.pos_id=ass.pos_id --AND pos.dep_id=3763
where ph.phone_number between '1501' and '1599' 
and length(ph.phone_number)=4
GROUP BY  ph.phone_number
order by phone_number";
$stmt=oci_parse($conn,$sql);
oci_execute($stmt);
echo "<table border=1><thead><tr><td>end date</td><td>phone_number</td><td>pos_id</td></tr></thead><tbody>";
while($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS)){
  echo "<tr><td>".$row['END_DATE']."</td><td>".$row['PHONE_NUMBER']."</td><td>".$row['POS_ID']."</td></tr>";
}
echo "</tbody></table>";