<meta charset="utf-8">

<?php
function clearHTML($str){
// Создаем DOM объект
  $dom = new DOMDocument();
// Переводим в UTF8 для корректной работы с кириллицей
  $dom->loadHTML('<meta charset="utf8">' . $str);
//  $dom->loadHTML(mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8'));
// Проходим по всем элементам и удаляем все атрибуты которые начинаются на 'on*'
  foreach ($dom->getElementsByTagname('*') as $element) {
    foreach (iterator_to_array($element->attributes) as $name => $attribute) {
//    foreach ($element->attributes as $name => $attribute) {
      if (substr_compare($name, 'on', 0, 2, TRUE) === 0) {
        $element->removeAttribute($name);
      }
    }
  }
//   $dom->getELementsByTagName('body')->item(0)->setAttribute('onclick','clickInput(this.parentNode)');
// DOM создает полную страницу с заголовком и т.д.
// Поэтому вытаскиваем body
  $body = $dom->getELementsByTagName('body')->item(0);
//  $body->setAttribute('onclick','clickInput(this.parentNode)');
// Сохраняем содержимое, обрезав сам тег body
  return(substr($dom->saveHTML($body),6,-7));

}
include('../auth2.php');
include('./func.php');
if($_SERVER['REQUEST_METHOD']=="POST"){
//  if(empty($_POST['CLIENT_ID'])){
    include('client.php');
//  }
  if(empty($_POST['ISS_ID']) && !empty($_POST['CLIENT_ID'])){
    $stmt=oci_parse($conn,"insert into pbx.issues(iss_id, client_id, iss_date, creator) values(pbx.ISSUES_SEQ.NEXTVAL, :client_id, sysdate, :username) return iss_id into :issue_id");
    oci_bind_by_name($stmt,":client_id",$_POST['CLIENT_ID']);
    oci_bind_by_name($stmt,":username",$_SESSION['uid']);
    oci_bind_by_name($stmt,":issue_id",$iss_id,-1,SQLT_INT);
    oci_execute($stmt);
    $stmt=oci_parse($conn,"insert into pbx.iss_prop(issue_id,type_id,value,prop_date) values(:issue_id,'120','В работе',sysdate)");
    oci_bind_by_name($stmt,":issue_id",$iss_id);
    oci_execute($stmt);
    echo "Заявка ".$iss_id." создана";
  }else{
    $iss_id=$_POST['ISS_ID'];
    if(!empty($_POST['CLIENT_ID'])){
      $stmt=oci_parse($conn,"update pbx.issues set client_id=:cli_id where iss_id=:iss_id");
      oci_bind_by_name($stmt,":cli_id",$_POST['CLIENT_ID']);
      oci_bind_by_name($stmt,":iss_id",$iss_id);
      oci_execute($stmt);
    }
  }
  if($iss_id){
    if(!empty($_POST['DETALE']) && is_array($_POST['DETALE'])){
      $stmt_1=oci_parse($conn,"DELETE FROM pbx.issue_detale id WHERE id.iss_id=:iss_id AND detale_id IN (SELECT detale_id FROM pbx.detale WHERE type_id=:type_id)");
      $stmt1=oci_parse($conn,"insert into pbx.issue_detale(iss_id,detale_id) values(:iss_id,:detale_id)");
      oci_bind_by_name($stmt_1,":iss_id",$iss_id);
      oci_bind_by_name($stmt1,":iss_id",$iss_id);
      foreach($_POST['DETALE'] as $type_id=>$detale_id){
        oci_bind_by_name($stmt_1,":type_id",$type_id);
        oci_bind_by_name($stmt1,":detale_id",$detale_id);
        oci_execute($stmt_1);
        oci_execute($stmt1);
      }
    }
    $stmt2=oci_parse($conn,"select type_id, name from pbx.types where class_id=60");
    oci_execute($stmt2);
//    $stmt_3=oci_parse($conn,"delete from pbx.iss_prop where type_id=:type_id and issue_id=:iss_id");
    $lob = oci_new_descriptor($conn, OCI_DTYPE_LOB);
    $stmt3=oci_parse($conn,"insert into pbx.iss_prop(issue_id,type_id,value,prop_date,long_text) values(:issue_id,:type_id,:value,sysdate,EMPTY_BLOB()) RETURNING long_text INTO :blobdata");
    oci_bind_by_name($stmt3,":issue_id",$iss_id);
    $empty=null;
    while($row=oci_fetch_array($stmt2,OCI_ASSOC+OCI_RETURN_NULLS)){
//      error_log($iss_id."-".$row['TYPE_ID']." ".$row['NAME']." ".$_POST[$row['NAME']]);
        oci_bind_by_name($stmt3,":type_id",$row['TYPE_ID']);
        oci_bind_by_name($stmt3, ":blobdata", $lob, -1, OCI_B_BLOB);
      if(!empty($_POST[$row['NAME']])){
          $_POST[$row['NAME']]=$_POST[$row['NAME']];
//        oci_bind_by_name($stmt_3,":type_id",$row['TYPE_ID']);
//        oci_execute($stmt_3);
        if(mb_strlen($_POST[$row['NAME']])>=1000){
//          error_log("long");
//          oci_bind_by_name($stmt3,":long_text",$_POST[$row['NAME']]);
          oci_bind_by_name($stmt3,":value",$empty);
          oci_execute($stmt3,OCI_NO_AUTO_COMMIT);
          $lob->save($_POST[$row['NAME']]);
          oci_commit($conn);
        }else{
//          error_log("short");
          oci_bind_by_name($stmt3,":value",$_POST[$row['NAME']]);
          oci_execute($stmt3);
        }
      }
    }
    if(!empty($_POST['CHANNELID'])){
      $stmt5=oci_parse($conn,"insert into pbx.iss_prop values(:issue_id,80,:value,sysdate,EMPTY_BLOB())");
      oci_bind_by_name($stmt5,":issue_id",$iss_id);
      oci_bind_by_name($stmt5,":type_id",$row['TYPE_ID']);
      oci_bind_by_name($stmt5,":value",$_POST['CHANNELID']);
      oci_execute($stmt5);
    }
  }
  
  exit;
}
$flag="";
if(isset($_GET['id'])){
  $iss=oci_parse($conn,"select i.iss_id, 
    to_char(i.iss_date,'DD.MM.YYYY HH24:MI:SS') iss_date, 
    trim(LEADING 0 from i.creator) creator,
    p.name
  from pbx.issues i, main.staff s, main.persons p
    where iss_id=:id
    and s.uname=i.creator
    and p.person_id=s.person_id
    and p.person_sid=s.person_sid");
  oci_bind_by_name($iss,":id",$_GET['id']);
  oci_execute($iss);
  $owner=oci_parse($conn,"select  t.type_id, 
    max(t.name) keep(DENSE_RANK LAST ORDER BY p.prop_date) t_name, 
    max(p.value) keep(DENSE_RANK LAST ORDER BY p.prop_date) p_val, 
    max(p.prop_date), max(u.name) keep(DENSE_RANK LAST ORDER BY p.prop_date) p_date
  from pbx.iss_prop p 
    left outer join pbx.types t on t.type_id=p.type_id
    left outer join main.staff s on s.uname=p.value
    left outer join main.persons u on u.person_id=s.person_id and u.person_sid=u.person_sid
  where p.issue_id=:id and p.type_id=122
  GROUP BY t.type_id");
  oci_bind_by_name($owner,":id",$_GET['id']);
  oci_execute($owner);
  $status=oci_parse($conn,"SELECT max(t.type_id) keep(dense_rank last order by ip1.prop_date) type_id, max(t.name) keep(dense_rank last order by ip1.prop_date) name FROM pbx.types t, pbx.iss_prop ip1 WHERE t.class_id=100 AND ip1.type_id=t.type_id and ip1.issue_id=:id group by ip1.issue_id");
  oci_bind_by_name($status,":id",$_GET['id']);
  oci_execute($status);
  $det=oci_parse($conn,"SELECT d.detale_id,d.name,t.type_id
  FROM pbx.issue_detale id, pbx.detale d, pbx.types t
  WHERE id.iss_id=:id
    AND d.detale_id=id.detale_id
      AND t.TYPE_id=d.type_id");
  oci_bind_by_name($det,":id",$_GET['id']);
  oci_execute($det);
//  $prop=oci_parse($conn,"SELECT t.name,max(ip.value) keep(dense_rank last order by ip.prop_date) value,max(ip.long_text) keep(dense_rank last order by ip.prop_date) long_text
//  FROM pbx.iss_prop ip, pbx.types t
//  WHERE ip.issue_id=:id
//    AND ip.type_id=t.type_id
//    and t.type_id!=6
//  group by t.name");
  $prop=oci_parse($conn,"select t1.name, t1.value, ip1.long_text LONG_TEXT, to_char(t1.prop_date,'DD.MM.YYYY HH24:MI:SS') prop_date, p.name pname from 
  (SELECT ip.issue_id,ip.type_id,max(ip.prop_date) prop_date,t.name,max(ip.value) keep(dense_rank last order by ip.prop_date) value
    FROM pbx.iss_prop ip, pbx.types t
      WHERE ip.issue_id=:id
        AND ip.type_id=t.type_id
        and t.type_id!=6
      group by ip.issue_id,ip.type_id,t.name) t1
    join pbx.iss_prop ip1  on ip1.prop_date=t1.prop_date and ip1.issue_id=t1.issue_id and ip1.type_id=t1.type_id
    LEFT OUTER JOIN staff s ON s.uname=lpad(t1.value,6,'0') AND t1.type_id=160
    LEFT OUTER JOIN persons p ON p.person_id=s.person_id AND p.person_sid=s.person_sid");
  oci_bind_by_name($prop,":id",$_GET['id']);
//  oci_define_by_name($prop,'NAME',$name);
//  oci_define_by_name($prop,'VALUE',$value);
//  $long_text=oci_new_descriptor($conn, OCI_D_LOB);
//  error_log(gettype($long_text));
//  oci_define_by_name($prop,'LONG_TEXT',$long_text,OCI_B_BLOB);
  oci_execute($prop);
  if(empty($_GET['channelid'])){
    $records=oci_parse($conn,"SELECT cr.call_id, cr.filename FROM pbx.iss_prop ip,callrecords cr WHERE ip.issue_id=:id AND ip.type_id=80 AND cr.call_id=ip.value");
    oci_bind_by_name($records,":id",$_GET['id']);
  }else{
     $records=oci_parse($conn,"SELECT cr.call_id, cr.filename FROM callrecords cr where cr.call_id=:channelid");
     oci_bind_by_name($records,":channelid",$_GET['channelid']);
  }
  
  oci_execute($records);
//  $ISS=array();
  $ISS=oci_fetch_array($iss);
  $DETAIL=array();
  while($row=oci_fetch_array($det)){
    $DETAIL[$row['TYPE_ID']]=array("NAME"=>$row['NAME'],"ID"=>$row['DETALE_ID']);
  }
//  oci_fetch_all($det,$DETAIL,0,-1,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC);
  $PROP=array();
  $PROP_L=array();
  $PROP_DATE=array();
  while($row=oci_fetch_array($prop,OCI_ASSOC+OCI_RETURN_LOBS+OCI_RETURN_NULLS)){
//  while(oci_fetch($prop)OCI_RETURN_LOBS){
//  var_dump($row);
//    $PROP[$name]=$value;
//    $PROP_L[$name]=$long_text->read(400);
//    $long_text->free();
    $PROP[$row['NAME']]=$row['VALUE'];
    if(!empty($row['PNAME'])) $PROP[$row['NAME']].=' - '.$row['PNAME'];
    $PROP_L[$row['NAME']]=$row['LONG_TEXT'];
    array_push($PROP_DATE,$row['PROP_DATE']);
  }
  $REC=array();
  while($row=oci_fetch_array($records)){
    $file=array_reverse(explode('/',$row['FILENAME']));
    $f_path=$file[2]."/".$file[1]."/".$file[0];
    $REC[$row['CALL_ID']]=$f_path;
  }
//  oci_fetch_all($prop,$PROP,0,-1,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC);
  if(empty($_GET['action']) || $_GET['action']!='copy'){
    $flag=" readonly=true disabled=true";
  }else{
    $flag=" readonly=true";
  }
  $row=oci_fetch_array($status,OCI_ASSOC);
}
//$_GET['client']='9817133264';
echo "<form action='ticket.php' method=post>";
echo "<div class=title>";
if(!empty($_GET['id']) && (empty($_GET['action']) || $_GET['action']!='copy')){
  echo "Заявка №".$_GET['id'];
  echo "<input type=hidden name=ISS_ID id=ISS_ID value=".$_GET['id']." ".$flag.">";
}else{
  echo "Создание заявки";
//  echo " <input type=hidden name=STATUS  value=120><div class='inline s121' id=status oncontextmenu=contextmenu(event,this)>В работе";
}
  echo '<div class="input inline" style="margin-left:2em;" onclick=clickInput(this)><input type=text name=SUBJECT style="font-size:1em;" placeholder="Тема" value=';
  if(!empty($_GET['id'])){ 
    echo "'".$PROP['SUBJECT']."' ".$flag;
  }else{
    echo "'' required";
  }
  echo '></div>';
  echo " <div oncontextmenu=contextmenu(event,this) id=status class='inline s";
  if(!empty($row)){
    echo $row['TYPE_ID']."'>".$row['NAME'];
  }else{
    echo "'>Неизвестно";
  }
$stmt=oci_parse($conn,"select * from pbx.types where class_id=100");
oci_execute($stmt);
echo "</div><div class='context inline' id=statusmenu>";
while($row=oci_fetch_array($stmt,OCI_ASSOC)){
  echo "<div id=".$row['TYPE_ID']." class='s".$row['TYPE_ID']."' onclick='setStatus(event,this)'>".$row['NAME']."</div>";
}
echo "</div>";
echo "<img class=close id=close src='/images/close.png' onclick='closeModal()'></div>";
echo "<div><div id=ticketheader class=inline><div class=section><span class=label></span>";
echo "<table><tr><td>Автор:</td><td>";
if(!empty($ISS)){
  echo $ISS['CREATOR']." - ".$ISS['NAME'];
}else{
  echo ltrim($_SESSION['uid'],'0')." - ".$_SESSION['name'];
}
echo "</td></tr><tr><td>Исполнитель:</td><td>";
if(!empty($PROP['ASSIGNEE'])){
  $value=$PROP['ASSIGNEE'];
}else if(empty($ISS)){
  $value=ltrim($_SESSION['uid'],'0')." - ".$_SESSION['name'];
}else{
  $value='Не назначен';
}
createFormField('ASSIGNEE',$value,$opts=['required'=>false,'readonly'=>true,'disabled'=>true],$dataset=['dir'=>"user",'minchar'=>3],$seldataset=[],$event="clickInput(this,event)");
/*echo "<div class=input onclick=clickInput(this)><input type=text style='fonty-size:1em;' data-dir=user onkeyup='search(event,this,true)' autocomplete=off ";
echo $flag.">";
echo "<br><select class=searchlist name=ASSIGNEE size=5 onkeyup='selKey(event,this)' onclick='this.style.visibility=\'hidden\'' onfocusout='choose(this);hide(this);' ".$flag.">";
if(empty($PROP['ASSIGNEE']) && empty($ISS)) echo "<option value='".$_SESSION['uid']."' selected></option>";
echo "</select></div>";*/
echo "</td></tr><tr>";
if(!empty($ISS)){
  echo "<td>Создана:</td><td>".$ISS['ISS_DATE']."</td>";
}  
echo "</td></tr><tr>";
if(count($PROP_DATE)>0) echo "<td>Изменено:</td><td>".max($PROP_DATE)."</td>";
echo "</tr></table></div>";
echo "<div class=section onclick=clickInput(this,event)><span class=label>Клиент</span>";
echo "<table id=ticketHeader>";
if(!empty($_GET['id'])){
 $stmt=oci_parse($conn,"select c.client_id, c.name, t.name type, t.type_id, p.phone_num 
 from pbx.issues i 
 left outer join pbx.clients c  on c.client_id=i.client_id 
 left outer join pbx.cli_phones p on p.client_id=i.client_id 
 left outer join pbx.types t  on t.type_id=c.type_id
 where i.iss_id=:id");
 oci_bind_by_name($stmt,":id",$_GET['id']);
}else if(!empty($_GET['client'])){
  $sql="select c.client_id, c.name, cp.phone_num, c.type_id from PBX.CLI_PHONES cp, PBX.CLIENTS c, pbx.types t where cp.CLIENT_ID=c.CLIENT_ID and t.type_id=c.TYPE_ID and cp.phone_num=:phone";
  $stmt=oci_parse($conn,$sql);
  oci_bind_by_name($stmt,':phone',$_GET['client']);
}else if(!empty($_GET['cliid'])){
  $sql="select c.client_id, c.name, cp.phone_num, c.type_id  from  PBX.CLIENTS c left outer join PBX.CLI_PHONES cp on cp.CLIENT_ID=c.CLIENT_ID left outer join pbx.types t on t.type_id=c.TYPE_ID where  c.client_id=:id";
  $stmt=oci_parse($conn,$sql);
  oci_bind_by_name($stmt,':id',$_GET['cliid']);
}
if(!empty($stmt)){
  oci_execute($stmt);
  $cli=oci_fetch_array($stmt,OCI_ASSOC);
}
echo "<tr><td>Имя</td><td>";
if(!empty($cli['CLIENT_ID'])){
  echo "<input type=hidden name=CLIENT_ID value=".$cli['CLIENT_ID']." readonly>";
//  echo "<div class=input><input type=text name=CLIname value='".$cli['NAME']."' ".$flag.">";
  $value=$cli['NAME'];
}else{
  $value="";
//  echo "<div class=input><input type=text name=CLIname required>";
}
//echo "</div>";
createFormField('CLIname',$value,$opts=['required'=>true,'readonly'=>true,'disabled'=>true],$dataset=[],$seldataset=[],$event="");
echo "</td></tr>
<tr><td>Тип клиента</td><td><div class=input>";
//if(!empty($cli['CLIENT_ID'])){
//  echo $cli['TYPE'];
//}else{
//   echo "<input type=radio name=CLItype id=type2 value=2><label for=type2>Клиент</label><input type=radio name=CLItype id=type3 value=3><label for=type3>Установочный центр</label>";
   $sql="select type_id,name from pbx.types where class_id=1";
   $stmt=oci_parse($conn,$sql);
   oci_execute($stmt);
   while($typ=oci_fetch_array($stmt,OCI_ASSOC)){
     echo "<input type=radio name=CLItype id=type".$typ['TYPE_ID']." value=".$typ['TYPE_ID']."  required";
//     error_log($cli['TYPE_ID']." - ".$typ['TYPE_ID']);
     if(!empty($cli['TYPE_ID']) && $cli['TYPE_ID']==$typ['TYPE_ID']) echo " checked ";
     if(!empty($cli['CLIENT_ID'])) echo $flag;
     echo "><label for=type".$typ['TYPE_ID'],">".$typ['NAME']."</label><br>";
   }
//}
echo "</div></td></tr>
<tr><td>Номер телефона</td><td>";
//  echo "<div class=input>";
//  echo "<input type=text name=CLIphone data-dir=client onkeyup='search(event,this,false)' autocomplete=off required onfocusOut=hide(this.parentNode.querySelector('.searchlist')) ";
$value="";
if(!empty($cli['CLIENT_ID']) && !empty($cli['PHONE_NUM'])){
  echo $value=$cli['PHONE_NUM'];
}else{
  if(!empty($_GET['client'])) $value=$_GET['client'];
}
//echo "><br><select class=searchlist name=s_CLIphone size=5 onkeyup='selKey(event,this)' onclick='choose(this);hide(this);' onfocusout='choose(this);hide(this);'></select>";
//echo "</div>";
createFormField('CLIphone',$value,$opts=['required'=>true,'readonly'=>true,'disabled'=>true],$dataset=['dir'=>"client",'minchar'=>3],$seldataset=[],$event="");
echo "</td></tr>
<tr><td>Город</td><td><div class=input>";
if(!empty($PROP['CITY'])){
  $stmt=oci_parse($conn,"select ct.city_id,ct.city_name,rg.region_name,cn.note from main.pbx_city ct, main.pbx_regions rg, main.pbx_countries cn where ct.region_id=rg.region_id and rg.country_id=cn.country_id and ct.city_id=:cityid");
  oci_bind_by_name($stmt,":cityid",$PROP['CITY']);
  oci_execute($stmt);
  $row=oci_fetch_array($stmt,OCI_ASSOC);
  $city_name=$row['CITY_NAME'].", ".$row['REGION_NAME'].", ".$row['NOTE'];
}
echo "<input type=text name=s_CITY data-dir=city onkeyup='search(event,this,true)' ";
if(!empty($PROP['CITY'])) echo "value='".$city_name."' ".$flag;
echo "><br><select class=searchlist size=5 name=CITY onkeyup='selKey(event,this)' onclick='this.style.visibility=\'hidden\'' onfocusout='choose(this);hide(this);' ".$flag.">";
if(!empty($PROP['CITY'])) echo "<option value=".$PROP['CITY']." selected>".$city_name."</option>";
echo "</select>";

echo "</div></td></tr></table></div>";

$stmt2=oci_parse($conn,"SELECT detale_id,name FROM pbx.detale WHERE TYPE_id=22 order by name asc");
oci_execute($stmt2);
$stmt3=oci_parse($conn,"select detale_id,name from pbx.detale where type_id=43 order by detale_id asc");
oci_execute($stmt3);

echo "<div class=section><span class=label>Детали</span><table>";
echo '<tr><td>Тип обращения</td><td><div class=input onclick=clickInput(this)><input type=text name=s_TYPE size=32 onkeyup="search(event,this,true)" autocomplete=off required';
if(isset($_GET['id']) && isset($DETAIL[43]['ID'])) echo " value='".$DETAIL[43]['NAME']."' ".$flag;
echo '><br>
<select class=searchlist size=5 name="DETALE[43]" onkeyup="selKey(event,this)" onchange="" onclick="this.style.visibility=\'hidden\'" onfocusout="choose(this);hide(this);" '.$flag.'>';
while($row=oci_fetch_array($stmt3,OCI_ASSOC+OCI_RETURN_NULLS)){
  echo "<option value=".$row['DETALE_ID'];
  if(!empty($row['DETALE_ID']) && !empty($DETAIL[43]) && $row['DETALE_ID']==$DETAIL[43]['ID']){
    echo " selected";
  }
  echo ">".$row['NAME']."</option>";
  }
echo '</select></div></td></tr>
<tr><td><label for=s_MODEL>Модель оборудования</label></td><td><div class=input onclick=clickInput(this)><input type=text id=s_MODEL name=s_MODEL data-minchar=2 size=32 onkeyup="search(event,this,true)" autocomplete=off required';
if(isset($_GET['id']) && isset($DETAIL[22]['ID'])) echo " value='".$DETAIL[22]['NAME']."' ".$flag;
echo '><br>
<select class=searchlist size=5 name=DETALE[22] data-next=20 onkeyup="selKey(event,this)" onchange="" onclick="this.style.visibility=\'hidden\'" onfocusout="choose(this);hide(this);" '.$flag.'>';
while($row=oci_fetch_array($stmt2,OCI_ASSOC+OCI_RETURN_NULLS)){
  echo "<option value=".$row['DETALE_ID'];
  if(!empty($row['DETALE_ID']) && !empty($DETAIL[22]) && $row['DETALE_ID']==$DETAIL[22]['ID']) echo " selected";
  echo ">".$row['NAME']."</option>";
}
echo '</select></div></td></tr>
<tr><td>Причина обращения</td><td><div class=input onclick=clickInput(this)><input type=text name=s_REASON size=32 onkeyup="search(event,this,true)" autocomplete=off required';
if(isset($_GET['id']) && isset($DETAIL[20]['ID'])) echo " value='".$DETAIL[20]['NAME']."' ".$flag;
echo '><br>
<select class=searchlist size=5 name=DETALE[20] onkeyup="selKey(event,this)" onchange="" onclick="this.style.visibility=\'hidden\'" onfocusout="choose(this);hide(this);" '.$flag.'>';
$stmt4=oci_parse($conn,"select distinct d3.detale_id,d3.name 
from pbx.detale d1, pbx.REL_DETALE r1,
     pbx.detale d2, pbx.rel_detale r2,
     pbx.detale d3
where r1.FROM_ID=d1.detale_id and d2.detale_id=r1.to_id
  and r2.from_id=d2.detale_id and d3.detale_id=r2.to_id
  and d1.DETALE_ID=:id");
//  var_dump($DETAIL[22]['ID']);
oci_bind_by_name($stmt4,":id",$DETAIL[22]['ID']);
oci_execute($stmt4);
while($row=oci_fetch_array($stmt4,OCI_ASSOC+OCI_RETURN_NULLS)){ 
  echo "<option value=".$row['DETALE_ID'];
  if($row['DETALE_ID']==$DETAIL[20]['ID']) echo " selected";
  echo ">".$row['NAME']."</option>";
}
      
//echo "<option value=".$DETAIL[20]['ID']." selected></option>";
echo '</select></div></td>
</td></tr>
<tr><td>Логин</td><td><div class=input onclick=clickInput(this)><input type=text name=LOGIN ';
if(!empty($_GET['id'])) echo " value='".$PROP['LOGIN']."' ".$flag;
echo '></div></td></tr>';
echo '</tr><tr><td>Записи разговоров</td><td>';
if(!empty($_GET['channelid'])){
  echo '<input type=hidden name=CHANNELID value="'.$_GET['channelid'].'"><br>';
  echo "Запись появится после окончания разговора.<br>";
}
if(isset($_GET['id']) && count($REC)>0){
    foreach($REC as $recordid=>$file){
      echo "<div id='".$recordid."'><audio controls='' src='https://fsprec.starline.ru:8000/".$file.".mp3' type='audio/wave'>
        <source src='https://fsprec.starline.ru:8000/".$file.".wav' type='audio/wave'>
        <source src='https://fsprec.starline.ru:8000/".$file.".mp3' type='audio/mpeg'>
        Ваш браузер не поддерживает тег audio.
        <a href='https://fsprec.starline.ru:8000/".$file.".mp3'>Загрузить файл</a><br>
        <a href='https://fsprec.starline.ru:8000/".$file.".wav'> еще ссылка</a>
      </audio></div>";
    }
}else{
//    echo "Запись появится после окончания разговора.<br>";
    if(empty($cli['PHONE_NUM'])) $cli['PHONE_NUM']="'+document.querySelector('input[name=CLIphone]').value+'";
    echo "Можно добавить из списка <a data-dir=records data-str=CLIphone onclick=\"get('./record.php?str=".$cli['PHONE_NUM']."',document.querySelector('#CHANNELID'))\">завершенных звонков</a><br><select class=searchlist id=CHANNELID name=CHANNELID></select>";
}

echo '<td></tr><tr><td>Время перезвона:</td><td><div class=input onclick=clickInput(this)><input type=datetime-local name=RECALLTIME patern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" ';
if(!empty($PROP['RECALLTIME'])) echo 'value="'.$PROP['RECALLTIME'].'" '.$flag.'';
echo "></div></td></tr></table></div>";
echo "<button type=button id=submit onclick='submitForm(this.form,null,false,reload)' disabled>Сохранить</button></div>";
echo "<div id=ticketdetails class=inline>";
echo "<div class=section onclick=clickInput(this)><span class=label>Автомобиль</span><table><tr><td><div id=cars class=collapsed>";
include("can.php");
$stmt1=$can->prepare("select id,name,logo_link,is_popular from brand order by is_popular desc, name asc");
$stmt1->execute();
$stmt1->bind_result($brand_id,$brand_name,$brand_logo,$is_popular);
$allbrands="";
echo "<div>Марка <img class='collapse up' src='/images/collapse.png' onclick='this.parentNode.parentNode.classList.toggle(\"collapsed\"); this.classList.toggle(\"up\")'></div>";
while($stmt1->fetch()){
  if($brand_logo != NULL && $is_popular!=0){
    echo "<img data-id='".$brand_id."' class=brand_logo src=https://can.starline.ru/data/logos/".$brand_logo." onclick=loadbrand(this.dataset.id)>";
  }else{
    $allbrands.="<div data-id='".$brand_id."' class=brand_name onclick=loadbrand(this.dataset.id)>".$brand_name."</div>";
  }
}
echo "<div onclick=this.nextElementSibling.classList.toggle('hide')><a>Все бренды</a></div>";
echo "<div id=allbrands class=hide>".$allbrands."</div>";
echo "</div><div id=car_model>";
if(isset($_GET['id'])){
  $stmt1=$can->prepare("select b.name,m.id,m.name,year_from,year_to,image from model m,brand b where m.id=? and b.id=m.id_brand");
//  var_dump($PROP['CAR']);
  $stmt1->bind_param("i",$PROP['CAR']);
  $stmt1->execute();
  $stmt1->bind_result($bname,$model_id,$mname,$year_from,$year_to,$image);
  while($stmt1->fetch()){
    echo "<div data-id='".$model_id."' class='inline car_model ok' onclick='selectCar(this)'><div><img src=https://can.starline.ru/data/model_images/thumb_128_96_".$image."></div>".$bname." ".$mname." ".$year_from."-".$year_to."</div>";
  }
}
echo "</div><input type=hidden name=CAR";
if(isset($_GET['id'])) echo " value='".$PROP['CAR']."'";
echo " ".$flag.">";
echo "<table><tr><td>Тип КПП</td><td><div class=input onclick=clickInput(this)>";
foreach(array('None'=>'Нет','MT'=>'Механическая','AT'=>'Автоматическая') as $k=>$v){
  echo "<input type=radio id=r".$k." name=TRANSMISSION value=".$k;
  if(!empty($PROP['TRANSMISSION']) && $PROP['TRANSMISSION']==$k) echo " checked ";
  echo  $flag."><label for=r".$k.">".$v."</label>";
}
echo "</div></td></tr></table></td></tr></table>";
echo "</div>";
echo "<div class=section><span class=label>Описание</span>";
echo '<table">';

echo '<tr>';
//echo '<td>Описание</td>';
echo '<td>';
echo '<div class=input style="height:55em" onclick=clickInput(this)>'; //onPointerUp=clickInput(this)>';
echo '<iframe class=ckeditor id=BODY ';
if(isset($_GET['id']) && !empty($PROP_L['BODY'])){
  echo 'srcdoc="'.htmlentities(clearHtml($PROP_L['BODY'])).'">'.$PROP_L['BODY'];
}else if(isset($_GET['id']) && !empty($PROP['BODY'])){
   echo ' srcdoc="'.htmlentities(clearHtml($PROP['BODY'])).'">'.$PROP['BODY'];
}else{
 echo ">";
}
echo '</iframe>';
echo '<div class="overlay"></div>';
echo '</div></td></table>';
echo "</div></div></div></div>";
echo "</form>";
?>
