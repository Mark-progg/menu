<html>
<head>
<?php include('auth2.php'); ?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link href="nsftoolsDatepicker.css" rel="stylesheet" type="text/css">
<LINK href="style.css" rel="stylesheet" type="text/css">
<style>
select{
  background:transparent;
}
.up{
  background:green;
}
.down{
  background:red;
  transform:rotate(180deg);
}
.middle{
  background:grey;
  transform:rotate(-90deg);
}
/*.comment{
  display:none;
  width:150px;
  height:150px;
  position:absolute;
  z-index:5;
}*/
.menuitem{
   background:#ffebcd;
   cursor:default;
   margin:2px;
}
.menuitem:hover{
   background:#ddc9ab;
}
.comment{
  visibility:hidden;
}
.bold{
  font-weight:bold;
  display:inline-block;
  margin-right:35px;
}
#log table{
  z-index:99;
  position:fixed;
  top:200px;
  left:200px;
  background-color:rgba(0,0,0,0.8);
  color:white;
  border-collapse:collapse;
  margin:4px;
}
#log table td{
  border:1px solid white;
}
</style>
<script src="./script.js" type="text/javascript"></script>
<script language=javascript>
//ту гет урл
function filter(){
  inputs=document.getElementById('t_head').getElementsByTagName('input');
  string='?';
  for(i=0;i<inputs.length;i++){
    if(inputs[i].type=="checkbox"){
      string+=inputs[i].name+'='+inputs[i].checked+'&';
    }else{
      string+=inputs[i].name+'='+inputs[i].value+'&';
    }
  }
  return document.location.href.split('?')[0]+string;
}
</script>
<script type="text/javascript" src="./nsftoolsDatepicker.js"></script>
</head>
<body onload="onLoad()" onclick="cleanMenus();cleanCommBox()">
<?php
include('./header.php');
$pagesize=300;
if((!isset($_GET['page'])) or ($_GET['page']==1)) $_GET['page']=1;
//запросик
  $stmt=oci_parse($conn,"select callid, nvl(nvl(client,client1),'-') client,agent,evnt,data from (SELECT callid,
                                max(data1) keep(DENSE_RANK FIRST ORDER BY time) client,
                                max(data2) keep(DENSE_RANK FIRST ORDER BY time) client1,
                                decode(max(agent) keep(DENSE_RANK last ORDER BY time asc),'NONE','-',substr(max(agent) keep(DENSE_RANK last ORDER BY time asc),instr(max(agent) keep(DENSE_RANK last ORDER BY time asc),'/')+1)) agent,
                                decode(max(event) keep(DENSE_RANK last ORDER BY time asc),'MARK','Оценка','COMPLETECALLER','Закончил клиент','COMPLETEAGENT','Закончил оператор','ENTERIVR','Вход в меню','ENTERQUEUE','Вход в очередь','ABANDON','Не дождался ответа','PRESS','Последний выбор','EXITWITHTIMEOUT','Выкинуло по времени','CONNECT','Соединено','RINGNOANSWER','Ожидание ответа',max(event) keep(DENSE_RANK last ORDER BY time asc)) evnt,
                                CASE WHEN max(event) keep(DENSE_RANK last ORDER BY time asc) IN ('COMPLETECALLER','COMPLETEAGENT','TRANSFER') then max(data2) keep(DENSE_RANK LAST ORDER BY time asc) 
                                    WHEN max(event) keep(DENSE_RANK last ORDER BY time asc)='PRESS' THEN max(data1) keep(DENSE_RANK LAST ORDER BY time asc)
                                    WHEN max(event) keep(DENSE_RANK last ORDER BY time asc)='ABANDON' THEN max(data3) keep(DENSE_RANK LAST ORDER BY time asc)
                                end data
                          FROM queue_log 
                          WHERE queuename LIKE 'sl-%' AND callid!='NONE' AND event NOT IN ('PAUSE','UNPAUSE','CONFIGRELOAD','PAUSEALL','UNPAUSEALL','QUEUESTART','PENALTY')
                          GROUP BY callid
                        UNION
                          SELECT c.uniqueid, dst,'', src, 'Исходящий', TO_char(billsec) 
                          FROM cdr c 
                          WHERE dcontext='sl-sip')
                      where callid between to_char((to_date(nvl(:sd,to_char(trunc(sysdate),'DD/MM/YYYY')),'DD/MM/YYYY') - to_date('01011970','ddmmyyyy'))*24*60*60) and to_char((to_date(nvl(:ed,to_char(trunc(sysdate)+1,'DD/MM/YYYY')),'DD/MM/YYYY') - to_date('01011970','ddmmyyyy'))*24*60*60)
                        and (client like '%'||:cli or client1 like '%'||:cli or nvl(:cli,'*')='*') 
                        and (agent like '%'||:ag or nvl(:ag,'*')='*')");
  oci_bind_by_name($stmt,":sd",@$_GET['startdate']);
  oci_bind_by_name($stmt,":ed",@$_GET['enddate']);
  oci_bind_by_name($stmt,":ag",@$_GET['agent']);
  if(@$_GET['empty']=="true") $_GET['client']="";
  oci_bind_by_name($stmt,":cli",@$_GET['client']);
  oci_execute($stmt);
$page=($_GET['page']-1)*$pagesize;
$cnt=oci_fetch_all($stmt,$data,$page,$pagesize,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC+OCI_RETURN_NULLS);
//для фильтра автомата
//$onchange="onchange=\"document.location.href=filter()\"";
$onchange="";
echo "<div id=log></div>";
echo "<table id=middle border=1 frame=void rules=all>
        <thead id=t_head>
          <td>C <input class=date id=startdate name=startdate onclick=\"displayDatePicker(this.name);\" ".$onchange."  value=".@$_GET['startdate'].">
              по <input class=date id=enddate name=enddate onclick=\"displayDatePicker(this.name);\" ".$onchange." value=".@$_GET['enddate']."></td>
          <td><input id=client name=client placeholder=Клиент ".$onchange." value=".@$_GET['client']."><input type=checkbox name='empty' ";
if (@$_GET['empty']=="true") echo "checked";
echo ">Пусто</td><td><input id=agent name=agent placeholder=Оператор ".$onchange." value=".@$_GET['agent']."></td>
<td>Последний статус</td>
<td><button type=button onclick=\"document.location.href=filter()\">Фильтр</button><button type=button onclick=\"document.location.href=document.location.href.split('?')[0]\">X</button></td>
        </thead>";
echo "<tbody id=paging><tr><td colspan=7 align=right>";
//листинг по стр
if (@$_GET['page']>1) echo "<a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']-1)."'\">Пред.</a>";
if ($cnt==$pagesize) echo " <a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']+1)."'\">След.</a>";
echo "</td></tr></tbody>";
foreach($data as $row){
  //Инфа о записи
  echo "<tbody id='".$row['CALLID']."'><tr>
            <td onclick=loadExternalContent('./showlog.php?cid=".$row['CALLID']."',document.getElementById('log'))>".date("Y-m-d H:i:s",$row['CALLID']-3600)."</td>
            <td style='cursor:pointer' <!-- onclick=\"document.getElementById('client').value=".$row['CLIENT'].";\"  >".$row['CLIENT']."</td>
            <td style='cursor:pointer;' onclick=\"document.getElementById('agent').value=".$row['AGENT'].";\" >".$row['AGENT']."</td>
            <td>".$row['EVNT']."</td>
            <td><span>".$row['DATA']."</span></td>";
  echo "</td></tr></tbody>";
}
echo "<tbody id=paging><tr><td colspan=7 align=right>";
//листинг по стр
#echo "Входящих на странице: <p class=bold>".$CC["in"]."</p> Исходящих на странице: <p class=bold>".$CC["out"]."</p> ";
if (@$_GET['page']>1) echo "<a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']-1)."'\">Пред.</a>";
if ($cnt==$pagesize) echo " <a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']+1)."'\">След.</a>";
echo "</td></tr></tbody>";
echo "</table>";
oci_free_statement($stmt);

oci_close($conn);
?>
</body>
</html>

