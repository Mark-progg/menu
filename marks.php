<html>
<head>
<?php include('auth2.php'); ?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<link href="nsftoolsDatepicker.css" rel="stylesheet" type="text/css">
<LINK href="style.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="./script.js"></script>
<script type="text/javascript">
function prevmonth(){
  mon=document.getElementById('month');
  mon.value=parseInt(mon.value)-1;
  window.location.href=window.location.href.split('?')[0]+'?month='+mon.value;
}
function nextmonth(){
  mon=document.getElementById('month');
  mon.value=parseInt(mon.value)+1;
  window.location.href=window.location.href.split('?')[0]+'?month='+mon.value;
}

</script>
</head>
<body onload="onLoad()" onclick="cleanMenus()"> 
<?php
include('header.php');
  $m=0;
  if(isset($_GET['month'])) $m=$_GET['month'];
  $m1=$m+1;
  $stmt=oci_parse($conn,"select p.name,t.agent,count(distinct t.call_id) c_callid,sum(t.val) s_mark, round(sum(t.val)/count(distinct t.call_id),1) mark,sum(t.val1) v1,sum(t.val2) v2,sum(t.val3) v3,sum(t.val4) v4,sum(t.val5) v5
                          from  (select c.call_id,
                                        c.agent,
                                        sum(cp.VALUE) val,
                                        count(instr(cp.options,'S')) s_opts,
                                        sum(case when cp.seq=1 and cp.value=0 then 1 else 0 end) val1,
                                        sum(case when cp.seq=2 and cp.value=0 then 1 else 0 end) val2,
                                        sum(case when cp.seq=3 and cp.value=0 then 1 else 0 end) val3,
                                        sum(case when cp.seq=4 and cp.value=0 then 1 else 0 end) val4,
                                        sum(case when cp.seq=5 and cp.value=0 then 1 else 0 end) val5
                                    from callrecords c,
                                      callrec_prop cp 
                                    where c.CALL_DATE between trunc(ADD_MONTHS(sysdate,nvl(:tt,'0')),'MM') and trunc(add_months(sysdate,nvl(:tt1,'+1')),'MM')
                                      and cp.call_id=c.call_id
--                                      and nvl(instr(cp.OPTIONS,'S'),0)!=0
                                      and nvl(instr(cp.OPTIONS,'D'),0)=0
                                    group by c.call_id,c.agent) t,
                                 phones ph,
                                 staff s,
                                 persons p,
                                 assignments ass
                            where t.agent=ph.PHONE_NUMBER
--                              and t.s_opts=5
                              and s.PERSON_ID=ph.PERSON_ID
                              and s.PERSON_SID=ph.PERSON_SID
                              and p.person_id=ph.person_id
                              and p.person_sid=ph.person_sid
                              and ass.uname=s.uname
                              and ass.beg_date is not null
                              and (ass.end_date is null)
--                              and ((s.uname=substr(user,3) 
--                                      or MAIN.CHECK_PARAMETER('FSP','FSP_GRANT')='TRUE') 
--                                      or t.agent=(select dn.ref_id 
--                                                  from dostup d, 
--                                                        d_nodes dn 
--                                                  where dn.dostup_id=d.dostup_id 
--                                                    and d.class_id=4240 
--                                                    and d.uname=substr(user,3)))
                            group by p.name,t.agent
                            order by p.name");


  oci_bind_by_name($stmt,":tt",$m);
  oci_bind_by_name($stmt,":tt1",$m1);
  oci_execute($stmt);
  echo "<table id=middle border=1 frame=void rules=all>";
  echo "<tr><td align=center colspan=3><img class='pic2' src='./images/leftarrow.png' onclick='prevmonth()'><span class='doublebold'> ".date("F Y",strtotime("$m month"))." </span><input id=month type=hidden value=".$m."><img class='pic2' src='./images/rightarrow.png' onclick='nextmonth()'></td><td></td><td colspan=5>Кол-во замечаний по критериям</td></tr>";
  echo "<tr><td>ФИО</td><td>Номер</td><td>Кол-во прослушанных<br> звонков</td><td>Средний балл</td><td title='Позитивный настрой сотрудника в течение всего разговора, участие в решении вопроса (желание помочь).'>1</td><td title='Речь грамотная, вежливая.'>2</td><td title='Активное ведение разговора.'>3</td><td title='Соблюдение алгоритма разговора.'>4</td><td title='Техническая грамотность и/или знание мат. части.'>5</td></td>";
  $sum=0;
  $s_mark=0;
  $sv1=0;
  $sv2=0;
  $sv3=0;
  $sv4=0;
  $sv5=0;
while($row=oci_fetch_array($stmt,OCI_ASSOC)){
  echo "<tbody style='cursor:pointer;' onclick='window.location.href=\"http://menu.starline.ru/records.php?marks=true&agent=".$row['AGENT']."&startdate=".date("01/m/Y",strtotime("$m month"))."&enddate=".date("01/m/Y",strtotime("$m1 month"))."\"'><tr><td>".$row['NAME']."</td><td>".$row['AGENT']."</td><td>".$row['C_CALLID']."</td><td>".$row['MARK']."</td><td>".$row['V1']."</td><td>".$row['V2']."</td><td>".$row['V3']."</td><td>".$row['V4']."</td><td>".$row['V5']."</td></tr><tbody>";
  $sum+=$row['C_CALLID'];
  $s_mark+=$row['S_MARK'];
  $sv1+=$row['V1'];
  $sv2+=$row['V2'];
  $sv3+=$row['V3'];
  $sv4+=$row['V4'];
  $sv5+=$row['V5'];
}  
echo "<tr><td>Итого:</td><td></td><td>".$sum."</td><td>".round($s_mark/$sum,2)."</td><td>".$sv1."</td><td>".$sv2."</td><td>".$sv3."</td><td>".$sv4."</td><td>".$sv5."</td></tr>";
echo "</table>";
oci_free_statement($stmt);

oci_close($conn);
?>
</body>
</html>