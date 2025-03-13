<html>
<meta charset="utf-8">
<body>
<?php
include('auth2.php');
//echo "<pre>";
//var_dump($_SESSION);
$stid=oci_parse($conn,"select p.name,ph.phone_number,ps.status,ps.pause,NUMTODSINTERVAL(sysdate-last_event,'day') lastchange from persons p,staff s,phones ph,phone_status ps where p.person_id=s.person_id and ph.person_id=s.person_id and ps.phone_number=ph.phone_number and s.uname=ltrim(USER,'WT') order by ph.phone_number desc");     
oci_execute($stid);
//echo "<table border=1>";
while($row = oci_fetch_array($stid, OCI_ASSOC)){
echo "<tr><td>".$row['NAME']."</td><td>".$row['PHONE_NUMBER']."</td><td>",$row['STATUS'],"</td><td>".$row['PAUSE']."</td><td>".$row['LASTCHANGE']."</td></tr>";
}
//echo "</table>";
oci_free_statement($stid);

oci_close($conn);
?>
</body>
</html>
