<?php
include('./auth2.php');
$stmt=oci_parse($conn,"select to_char(c.calldate,'HH24:MI:SS') time,
                              c.dst
                          from cdr c,
                            phones ph,
                            staff s 
                          where s.uname=substr(user,3)
                            and ph.person_id=s.person_id
                            and ph.person_sid=s.person_sid
                            and c.src=ph.phone_number
                            and c.calldate between trunc(sysdate) and sysdate
                            and c.dcontext='sl-sip'
                            and c.channel like 'SIP/15%");
oci_execute($stmt);
echo "<table>";
while ($row=oci_fetch_array($stmt,OCI_ASSOC)){
  echo "<tbody class=grey onclick=call('".$row['DST']."')><tr><td>".$row['TIME']."</td><td>".$row['DST']."</td></tr></tbody>";
}
echo "</table>";
oci_free_statement($stmt);

oci_close($conn);
?>