<?php
include("auth2.php");
//include("./tickets/can.php");
//$data=array("231451"=>array("status"=>"closed","subject"=>"Выбор оборудования","car_man"=>"Audi","car_model"=>"A1","car_year"=>"2010","sl_model"=>"","sl_reason"=>"Выбор оборудования"),
//            "231462"=>array("status"=>"closed","subject"=>"Нет координат в приложении","car_man"=>"Skoad","car_model"=>"Fabia","car_year"=>"2018","sl_model"=>"A93","sl_reason"=>"GPS нет координат"),
//            "235462"=>array("status"=>"closed","subject"=>"Не снимается с охраны","car_man"=>"Porshe","car_model"=>"Cayene","car_year"=>"2014","sl_model"=>"X96","sl_reason"=>"Брелок не работает"),
//            "324526"=>array("status"=>"closed","subject"=>"нет привязки к аккаунту","car_man"=>"Volvo","car_model"=>"XC40","car_year"=>"2022","sl_model"=>"A63","sl_reason"=>"starline-online/проблемы с аккаунтом"),
//            "992831"=>array("status"=>"open","subject"=>"Не заводится по автозапуску","car_man"=>"Mustang","car_model"=>"200","car_year"=>1998,"sl_model"=>"S96","sl_reason"=>"Автозапуск"),
//            "283754"=>array("status"=>"open","subject"=>"Как подключить","car_man"=>"Tesla","car_model"=>"ModelZ","car_year"=>"2023","sl_model"=>"A91","sl_reason"=>"Как подключить"));
$sql="select c.client_id, c.name, cp.phone_num, t.type_id,t.name t_name from PBX.CLI_PHONES cp, PBX.CLIENTS c, pbx.types t where cp.CLIENT_ID=c.CLIENT_ID and t.type_id=c.TYPE_ID and cp.phone_num=:phone";
$stmt=oci_parse($conn,$sql);
oci_bind_by_name($stmt,':phone',$_GET['client']);
oci_execute($stmt); 
$row=oci_fetch_array($stmt,OCI_ASSOC);
echo "<div class=label>Клиент";
//echo "<button type=button onclick='loadExternalContent(\"/tickets/client.php\",document.querySelector(\"#clientheader\"))'>Выбрать клиента</button>";
echo "<img class=close id=close src='/images/close.png' onclick='document.querySelector(&quot;#modal&quot;).innerHTML=&quot;&quot;'></div><form action=/tickets/client.php method=post><table><tbody id=clientheader>";
if($row!=null){
  echo "<tr><td>Имя</td><td>".$row['NAME']."</td></tr>
  <tr><td>Тип клиента</td><td>".$row['T_NAME']."</td></tr>
  <tr><td>Номер телефона</td><td><input name=CLIphone disabled value='".$row['PHONE_NUM']."'></td></tr>";
//  echo " <tr><td>".$_GET['query']."</td></tr>";
}else{
  echo "<tr><td>Имя</td><td><input name=CLIname></td></tr>
  <tr><td>Тип клиента</td><td>";
  $sql="select type_id,name from pbx.types where class_id=1";
  $stmt=oci_parse($conn,$sql);
  oci_execute($stmt);
  while($typ=oci_fetch_array($stmt,OCI_ASSOC)){
    echo "<input type=radio name=CLItype id=type".$typ['TYPE_ID']." value=".$typ['TYPE_ID'];
//     error_log($cli['TYPE_ID']." - ".$typ['TYPE_ID']);
//    if(!empty($cli['TYPE_ID']) && $cli['TYPE_ID']==$typ['TYPE_ID']) echo " checked ";
//    if(!empty($cli['CLIENT_ID'])) echo $flag;
    echo "><label for=type".$typ['TYPE_ID'],">".$typ['NAME']."</label>";
  }
//  echo "<td><input type=radio name=CLItype id=client value=1 checked><label for=client>Клиент</label><input type=radio name=CLItype id=master value=3><label for=master>Установочный центр</label></td>";
  echo "</td></tr>
  <tr><td>Номер телефона</td><td><input name=CLIphone value=".$_GET['client']."></td>
  ";
//  echo "<input type=text name=s_CITY data-dir='tickets/city' onkeyup='search(event,this,true)'><br>
//        <select class=searchlist size=5 name=CITY onkeyup='selKey(event,this)' onclick='this.style.visibility=\'hidden\'' onfocusout='choose(this);hide(this);'>
//  </select>";
  echo "<td><button type=button onclick=postform(this.form.action,this.form,document.querySelector('#clientheader'))>Сохранить клиента</button></td>
  </tr>";
}
echo "</tbody></table></form>";
echo "<span class=label>Заявки</span>";
if($row!=null){
    $sql="SELECT i.iss_id,i.iss_date
    ,nvl(c.name,' ') name
    ,nvl(p.phone_num,' ') phone_num
    ,nvl(max(ip.value) keep(DENSE_RANK LAST ORDER BY ip.prop_date),' ') car
    ,max(s.name) keep(DENSE_RANK LAST ORDER BY s.prop_date) status
    ,max(s.type_id) keep(DENSE_RANK LAST ORDER BY s.prop_date) status_id
    ,max(f.value) keep(dense_rank last order by f.prop_date) subject
    --,ip.value car
    ,nvl(a.name,' ') model
    ,nvl(b.name,' ') reason
    ,nvl(e.name,' ') type
    FROM pbx.issues i
    JOIN pbx.clients c ON c.CLIENT_ID=i.client_id
    LEFT JOIN pbx.cli_phones p ON p.client_id=i.client_id
    LEFT JOIN pbx.iss_prop ip ON ip.issue_id=i.iss_id  AND ip.type_id=63
    LEFT JOIN (SELECT ip1.issue_id, t.type_id, t.name, ip1.prop_date FROM  pbx.iss_prop ip1, pbx.types t WHERE t.type_id=ip1.type_id AND t.class_id=100) s ON s.issue_id=i.iss_id
    LEFT JOIN (SELECT id.iss_id,d.name FROM pbx.issue_detale id, pbx.detale d WHERE d.detale_id=id.detale_id AND d.type_id=22) a ON a.iss_id=i.iss_id
    LEFT JOIN (SELECT id1.iss_id,d1.name FROM pbx.issue_detale id1,pbx.detale d1 WHERE d1.detale_id=id1.detale_id AND d1.type_id=20) b ON b.iss_id=i.iss_id
    LEFT JOIN (SELECT id2.iss_id,d2.name FROM pbx.issue_detale id2,pbx.detale d2 WHERE d2.detale_id=id2.detale_id AND d2.type_id=43) e ON e.iss_id=i.iss_id
    LEFT JOIN (SELECT ip2.issue_id, ip2.value, ip2.prop_date FROM  pbx.iss_prop ip2 WHERE ip2.type_id=65) f ON f.issue_id=i.iss_id
    WHERE i.client_id=:cli_id
    GROUP BY i.iss_id,i.iss_date,c.name,p.phone_num,a.name,b.name,e.name
    order by i.iss_date";
    $stmt1=oci_parse($conn,$sql);
    oci_bind_by_name($stmt1,":cli_id",$row['CLIENT_ID']);
    oci_execute($stmt1);
    oci_fetch_all($stmt1,$data,0,-1,OCI_ASSOC+OCI_FETCHSTATEMENT_BY_ROW);
  echo "<table id=ticketlist><thead><tr><td class=status>Статус</td><td class=subject>Тема</td><td class=car>Авто</td><td class=model>Модель</td><td class=reason>Причина</td></tr></thead>";
//echo "<tbody>";
  $_GET['img']=0;
  foreach($data as $row1){
    echo "<tbody><tr><td class='s".$row1["STATUS_ID"]." status'>".$row1['ISS_ID']."</td>";
    echo "<td>".$row1['SUBJECT']."</td>";
    $_GET['CAR']=$row1['CAR'];
    echo "<td>";
    include('./tickets/car.php');
//    echo "<td>".$row1['CAR']."</td>";
    echo "</td>";
    echo "<td>".$row1['MODEL']."</td>";
    echo "<td>".$row1['REASON']."</td>
    <td><button type=button onclick=createTicket('".$row1['ISS_ID']."','".$_GET['channelid']."')>Создать из текущей</button></td></tr></tbody>";
  }
}else{
  echo "<table><tr><td>У этого клиента нет заявок. </td></tr>";
}
echo "<td><button type=button onclick=createTicket(null,'".$_GET['channelid']."')>Создать новую</button></td></tr></table>";