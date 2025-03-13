<?php
include('./auth2.php');
include('./checkadmin.php');
$stmt=oci_parse($conn,"SELECT s.uname,p.name,ph.phone_number
                         FROM persons p ,
                              staff s,
                              phones ph,
                              assignments ass,
                              positions pos 
                         where s.PERSON_ID=p.PERSON_ID
                              and s.PERSON_SID=p.PERSON_SID
                              and ph.PERSON_ID=p.PERSON_ID
                              and ph.PERSON_SID=p.PERSON_SID
                              and ass.UNAME=s.UNAME
                              and ass.BEG_DATE is not null
                              and ass.END_DATE is null
                              and pos.POS_ID=ass.POS_ID
                              and (pos.DEP_ID in (3763, 3794) or s.uname='016018')
                              and (s.UNAME like '%'||:tt||'%' or p.name like '%'||initcap(:tt)||'%' or ph.PHONE_NUMBER like '%'||:tt||'%')");
oci_bind_by_name($stmt,":tt",$_GET['search']);
oci_execute($stmt);
while($row=oci_fetch_array($stmt,OCI_ASSOC)){
  echo "<option value=".$row['UNAME'],">".$row['UNAME']." - ".$row['NAME']." - ".$row['PHONE_NUMBER']."</option>";
}
oci_free_statement($stmt);
oci_close($conn);
?>