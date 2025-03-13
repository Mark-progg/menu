<html>
<?php
include('auth2.php');
include('checkadmin.php');
include('jiradb.php');
?>
<meta charset="utf-8">
<head>
<LINK href="style.css" rel="stylesheet" type="text/css">
<script src="./script.js" type="text/javascript"></script>
<style>
.rotate > div{
  transform:rotate(-90deg);
  white-space: nowrap;
  height:10px;
  width:30px;
  margin-top:280px;
  transform-origin:0 0;
}
thead > td{
  width:30px;
}
.inline{
  display:inline-block;
}
.right{
  float:right;
  cursor:pointer;
  border:1px solid black;
  text-align:center;
  width:1em;
}
.rt{
  text-align:center;
}
</style>
<script type="text/javascript">
function toggle_type_reason(elem){
    loadExternalContent('/togglereason.php?rid='+elem.id.split("-")[0]+'&tid='+elem.id.split("-")[1],elem);
}
function toggle_type_model(elem){
  tt=elem.parentNode.getElementsByClassName("rt");
  for(i=0;i<tt.length;i++){
    tt[i].innerHTML="";
  }
  loadExternalContent('/togglemodel.php?mid='+elem.id.split("-")[0]+'&tid='+elem.id.split("-")[1],elem);
}
function disabl(id){
 var f=document.createElement('form');
 f.setAttribute('method',"POST");
 f.setAttribute('action',"./jirareasons.php");
 i=document.createElement('input');
 i.name="reason_id";
 i.value=id;
 f.appendChild(i);
 document.body.appendChild(f);
 f.submit();
}
</script>
</head>
<body>
<?php
if($_SERVER['REQUEST_METHOD']=="POST"){
  if((isset($_POST['reason_name']))&&(@$_POST['reason_name']!="")){
    $i=mysql_query("insert into sl_reason_name(name,reason_cat_id) values('".mysql_real_escape_string($_POST['reason_name'])."',3)");
    echo mysql_error();
  }else if((isset($_POST['reason_id']))&&(@$_POST['reason_id']!="")){
    $i=mysql_query("update sl_reason_name set enabled=0 where id=".mysql_real_escape_string($_POST['reason_id']));
    echo mysql_error();
  }
}
include('header.php');
//mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
$r=mysql_query("select * from sl_reason_name where enabled=1 order by name asc");
$m=mysql_query("select * from sl_model where enabled=1 order by name asc");
$t=mysql_query("select * from sl_type");
$reasons=array();
while($row=mysql_fetch_array($r,MYSQL_ASSOC)){
  $reasons[$row['id']]=$row;
}
$models=array();
while($row=mysql_fetch_array($m,MYSQL_ASSOC)){
  $models[$row['id']]=$row;
}
$types=array();
while($row=mysql_fetch_array($t,MYSQL_ASSOC)){
  $types[$row['id']]=$row['name'];
}
 

$dd=mysql_query("select s.reason_id,type_id from sl_reasons s,sl_reason_name r where r.enabled=1 and r.id=s.reason_id");
$arr=array();
while($row=mysql_fetch_array($dd,MYSQL_ASSOC)){
  $arr[$row['reason_id']][$row['type_id']]="V";
}
echo "<table style=border-spacing:30px;'><tr><td valign=top><table border=1 style='border-collapse:collapse;'>";
echo "<thead><td>";
echo "<form action=./jirareasons.php method=post><input type=text name=reason_name><button>Добавить</button></form>";
echo "</td>";
foreach($types as $tid=>$tname){
  echo "<td class=rotate width=30 id=t".$tid."><div>".$tname."</div></td>";
}
echo "</thead>";
foreach($reasons as $rid=>$rarr){
  echo "<tr";
  if(count(@$arr[$rid])==0){
    echo " style='background-color:pink'";
  }
  echo "><td id='r".$rid."'><div class=inline>".@$rarr['name']."</div>";
  if(count(@$arr[$rid])==0){
    echo "<div class='inline right' onclick='disabl(".$rid.")'>X</div>";
  }
  echo "</td>";
  foreach($types as $tid=>$tname){
    echo "<td id='".$rid."-".$tid."' class=rt style='cursor:pointer' onclick=toggle_type_reason(this)>".@$arr[$rid][$tid]."</td>";
  }
  echo "</tr>";
}
echo "</table></td><td valign=top><table border=1 style='border-collapse:collapse;'><thead><td></td>";
foreach($types as $tid=>$tname){
  echo "<td class=rotate id=t".$tid."><div>".$tname."</div></td>";
}
echo "</thead>";
foreach($models as $mid=>$marr){
  echo "<tr><td id='m".$mid."'>".$marr['name']."</td>";
  foreach($types as $tid=>$tname){
    echo "<td class=rt id='".$mid."-".$tid."' onclick=toggle_type_model(this)>";
    if($tid==$marr['type_id']){
      echo "V";
    }
    echo "</td>";
  }
  echo "</tr>";
}
echo "</table></td></tr></table>";
?>
</body>
</html>