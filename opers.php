<html>
<meta charset="utf-8">
<body>
<?php
#
#0 = Idle
#1 = In Use
#2 = Busy
#4 = Unavailable
#8 = Ringing
#16 = On Hold
#
include('auth2.php');
$stid=oci_parse($conn,"select p.name,ph.phone_number from persons p,staff s,phones ph where p.person_id=s.person_id and ph.person_id=s.person_id and s.name=:st;");
oci_bind_by_name($stid, ":st", $_SESSION['username']


$stid=oci_parse($conn,"select * from phone_status where REASON != :st ");
$st="workout";
oci_bind_by_name($stid, ":st", $st);
oci_execute($stid);
echo "<table>";
while($row = oci_fetch_array($stid, OCI_ASSOC)){
echo "<tr><td>".$row['PHONE_NUMBER']."</td><td>".$row['PAUSE']."</td><td>".$row['REASON']."</td><td>".$row['STATUS']."</td><td>".$row['LAST_EVENT']."</td></tr>";
}
echo "</table>";
oci_free_statement($stid);

oci_close($conn);
?>
</body>
</html>
