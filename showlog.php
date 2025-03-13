<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<?php
include("./auth2.php");
ini_set("date.timezone","Europe/Moscow");
$stmt=oci_parse($conn,"select time,
                              queuename,
                              decode(event,
                                       'MARK','Оценка',
                                       'COMPLETECALLER','Закончил клиент',
                                       'COMPLETEAGENT','Закончил оператор',
                                       'ENTERIVR','Вход в меню',
                                       'ENTERQUEUE','Вход в очередь',
                                       'ABANDON','Не дождался ответа',
                                       'PRESS','Последний выбор',
                                       'EXITWITHTIMEOUT','Выкинуло по времени',
                                       'CONNECT','Соединение',
                                       'RINGNOANSWER','Звонок без ответа',
                                       event) event,
                              agent,
                              data1,
                              data2,
                              data3
                     from queue_log where callid=:tt
                     order by time asc");
oci_bind_by_name($stmt,":tt",$_GET["cid"]);
oci_execute($stmt);
echo "<table><tr onclick=\"document.getElementById('log').innerHTML='';\"><td>Закрыть</td></tr>
   <tr><td>Время</td><td>Очередь</td><td>Событие</td><td>Оператор</td><td colspan=3>Данные</td></tr>";
while($row=oci_fetch_array($stmt,OCI_ASSOC)){
   echo "<tr><td>".$row['TIME']."</td><td>".$row['QUEUENAME']."</td><td>".$row['EVENT']."</td><td>".$row['AGENT']."</td><td>".$row['DATA1']."</td><td>".$row['DATA2']."</td><td>".$row['DATA3']."</td></tr>\n";
}
echo "</table>";
oci_free_statement($stmt);

oci_close($conn);
#var_dump($result) ;
?>
