<?php
include('./auth2.php');
include('./checkadmin.php');
$stmt=oci_parse($conn,"select dn.D_NODE_ID,s.uname,p.name, dn.REF_ID 
			from dostup d,
			      d_nodes dn,
			      phones ph,
			      persons p,
			      staff s
			where dn.DOSTUP_ID=d.DOSTUP_ID
			  and d.CLASS_ID=4240
			  and ph.phone_number(+)=dn.ref_id
			  and p.person_id(+)=ph.person_id
			  and p.person_sid(+)=ph.person_sid
			  and s.person_id(+)=ph.person_id
			  and s.person_sid(+)=ph.person_sid
			  and d.uname=:tt
                          and ref_id is not null
                        order by ref_id");
oci_bind_by_name($stmt,":tt",$_GET['user']);
oci_execute($stmt);
//var_dump($stmt);
$cnt=oci_fetch_all($stmt,$arr,null,null,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC);
//var_dump($cnt);
//var_dump($arr);
echo "<input id=fullacl type=checkbox name=full value='*' onclick='addfullaccess(this)' tabindex=2";
if(@$arr[0]['REF_ID']=='*'){
  echo " checked=true> Полный доступ<br><fieldset id=list disabled=true>";
  array_shift($arr);
}else{
  echo "> Полный доступ<br><fieldset id=list>"; 
}
foreach($arr as $row){
  echo "<div id=".$row['REF_ID'].">".$row['UNAME']." - ".$row['NAME']." - ".$row['REF_ID']." <a href='javascript:void(0)' onclick='delfromlist(this.parentNode.id)'>Удалить</a></div>";
}
oci_free_statement($stmt);

oci_close($conn);
?>
<input id=searchoper size=45 name=searchoper onkeyup="searchuser(event,this,'s_searchoper')" tabindex=3 placeholder="Выбирите подчиненных операторов"><br>
      <select id=s_searchoper name=s_searchoper size=10 onkeyup="selectKeyUp(event,this,'searchoper','')" onclick="choose(this,'searchoper','')"></select></fieldset>
