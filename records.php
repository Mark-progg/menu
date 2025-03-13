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
audio{
  height:1em;
  width:20em;
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
</style>
<script src="./script.js" type="text/javascript"></script>
<script language=javascript>
function showPlayer(elem,link){
  fqdn='https://fsprec.starline.ru:8000/'+link;
  elem.innerHTML='<audio controls src="'+fqdn+'.mp3" type="audio/wave"><source src="'+fqdn+'.wav" type="audio/wave"><source src="'+fqdn+'.mp3" type="audio/mpeg">Ваш браузер не поддерживает тег audio.<a href="'+fqdn+'".mp3">Загрузить файл</a><br><a href="'+fqdn+'.wav"> еще ссылка</a></audio>';
  elem.removeAttribute('onclick');
}
function marktoggle(cid,comm,elem){
  cnt1=countmarks(cid);
  elem.classList.remove('middle');
  comment=prompt('Комментарий:',comm)
//  if(comment==null) return;
  if(!Number(elem.alt)){
    elem.classList.remove('down');
    elem.classList.add('up');
  }else{
    elem.classList.remove('up');
    elem.classList.add('down');
  }
  elem.alt=Number(!Number(elem.alt));
  elem.title=comment;
  loadExternalContent('./setmark.php?cid='+cid+'&seq='+elem.id+'&mark='+Number(elem.alt)+'&comment='+comment+'&opt=','');
  cnt2=countmarks(cid);
  if((cnt2==4)&&(cnt1!=cnt2)){
    showShareBtn(cid);
  }else{
    elem.parentNode.getElementByClassName("sharebtn")[0].remove();
  }
}
function countmarks(cid){
  cnt=0;
  marks=document.getElementById(cid).getElementsByClassName('mark');
  for(i=0;i<marks.length;i++){
    if(marks[i].alt!="") cnt++;
//  if(!marks[i].classList.contains("moddle")) cnt++;
  }
  return cnt;
}
function showShareBtn(cid){
  elem=document.getElementById(cid).children[0].lastChild;
//    checkbox=document.createElement('input');
//    checkbox.type='checkbox';
//    checkbox.setAttribute('name','shared');
//    checkbox.setAttribute('onclick','sharemarks('+cid+',this)');
//    elem.parentNode.appendChild(checkbox);
//    elem.parentNode.appendChild(document.createTextNode('Поделиться '));
    btn=document.createElement('img');
    btn.src='./images/mail.png';
    btn.setAttribute('onclick','loadExternalContent("./mail.php?call_id='+cid+'","")');
    btn.classList.add('pic');
    btn.classList.add('sharebtn');
    btn.setAttribute('title','Уведомить письмом');
    elem.appendChild(btn);
}

function sharemarks(cid,elem){
  marks=elem.parentNode.getElementsByTagName('img');
  opt='';
  if(elem.checked) opt='S';
  for(i=0;i<marks.length;i++){
    loadExternalContent('./setmark.php?cid='+cid+'&seq='+marks[i].id+'&mark='+marks[i].alt+'&opt='+opt,'');
  }
}
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

function sendmail(id){
    loadExternalContent('./mail.php?call_id='+id,'');
}

function toggleCommentBox(event,elem){
  cleanCommBox();
  event.stopPropagation();
  switch (elem.nextSibling.style.visibility) {
    case 'visible':
      elem.nextSibling.style.visibility="hidden";
      break;
    case 'hidden':
      elem.nextSibling.style.visibility="visible";
      break;
    default:
      console.log(elem.nextSibling.style.visibility);
//      elem.nextSibling.style.visibility="visible";

  }
  return false;
}

function setmark(cid,seq,mark){
  cnt1=countmarks(cid);
  elem=document.getElementById(cid).getElementsByClassName('mark')[seq-1];
  elem.classList.remove('middle');
  if(mark==0){
    comment=prompt('Комментарий:',elem.title)
    if(comment==null) return;
    elem.classList.remove('up');
    elem.classList.add('down');
  }else{
    elem.classList.remove('down');
    elem.classList.add('up');
    comment='';
  }
  elem.alt=mark;
  elem.title=comment;
  loadExternalContent('./setmark.php?cid='+cid+'&seq='+seq+'&mark='+mark+'&comment='+comment+'&opt=','');  
  cnt2=countmarks(cid);
  if((cnt2==4)&&(cnt1!=cnt2)){
    showShareBtn(cid);
  }else{
    elem.parentNode.getElementsByClassName("sharebtn")[0].remove();
  }
}

function delmark(cid,seq){
  elem=document.getElementById(cid).getElementsByClassName('mark')[seq-1];
  elem.classList.remove('up');
  elem.classList.remove('down');
  elem.classList.add('middle');
  loadExternalContent('./setmark.php?cid='+cid+'&seq='+seq+'&mark='+elem.alt+'&comment=&opt=D','');
  elem.alt='';
  elem.title='';
  elem.parentNode.getElementsByClassName("sharebtn")[0].remove();
}

</script>
<script type="text/javascript" src="./nsftoolsDatepicker.js"></script>
</head>
<body onclick="cleanMenus();cleanCommBox()">
<?php
include('./header.php');
$pagesize=300;
if((!isset($_GET['page'])) or ($_GET['page']==1)) $_GET['page']=1;
//запросик
/*  $sql="select * from (select cr.call_id,
                         cr.filename,
                         cr.agent,
                         q.event,
                         cr.phonenum,
                         cr.direction,
                         lpad(extract(hour from numtodsinterval(nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec),'second')),2,'0') || ':' ||
                            lpad(extract(minute from numtodsinterval(nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec),'second')),2,'0') || ':' ||
                            lpad(extract(second from numtodsinterval(nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec),'second')),2,'0') billsec,
                         nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec) bs,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select value from callrec_prop where call_id=cr.call_id and seq=1 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select value from callrec_prop where call_id=cr.call_id and seq=1 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end t1,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select value from callrec_prop where call_id=cr.call_id and seq=2 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select value from callrec_prop where call_id=cr.call_id and seq=2 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end t2,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select value from callrec_prop where call_id=cr.call_id and seq=3 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select value from callrec_prop where call_id=cr.call_id and seq=3 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end t3,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select value from callrec_prop where call_id=cr.call_id and seq=4 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select value from callrec_prop where call_id=cr.call_id and seq=4 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end t4,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select value from callrec_prop where call_id=cr.call_id and seq=5 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select value from callrec_prop where call_id=cr.call_id and seq=5 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end t5,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select note from callrec_prop where call_id=cr.call_id and seq=1 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select note from callrec_prop where call_id=cr.call_id and seq=1 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end s1,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select note from callrec_prop where call_id=cr.call_id and seq=2 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select note from callrec_prop where call_id=cr.call_id and seq=2 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end s2,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select note from callrec_prop where call_id=cr.call_id and seq=3 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select note from callrec_prop where call_id=cr.call_id and seq=3 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end s3,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select note from callrec_prop where call_id=cr.call_id and seq=4 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select note from callrec_prop where call_id=cr.call_id and seq=4 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end s4,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select note from callrec_prop where call_id=cr.call_id and seq=5 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select note from callrec_prop where call_id=cr.call_id and seq=5 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end s5,
                         (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID and s.uname=(case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select uname from callrec_prop where call_id=cr.call_id and seq=1 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select uname from callrec_prop where call_id=cr.call_id and seq=1 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end)) u1,
                         (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID and s.uname=(case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select uname from callrec_prop where call_id=cr.call_id and seq=2 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select uname from callrec_prop where call_id=cr.call_id and seq=2 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end)) u2,
                         (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID and s.uname=(case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select uname from callrec_prop where call_id=cr.call_id and seq=3 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select uname from callrec_prop where call_id=cr.call_id and seq=3 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                           end)) u3,
                         (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID and s.uname=(case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select uname from callrec_prop where call_id=cr.call_id and seq=4 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
                           else (select uname from callrec_prop where call_id=cr.call_id and seq=4 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
                         end)) u4,
--                         (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID and s.uname=(case when (aa.ref_id=cr.agent or aa.ref_id='*') then (select uname from callrec_prop where call_id=cr.call_id and seq=5 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
--                           else (select uname from callrec_prop where call_id=cr.call_id and seq=5 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
--                         end)) u5,
                         (select sum(case nvl(instr(options,'S'),0) when 0 then 0 else 1 end) from callrec_prop where call_id=cr.call_id and prop_id=6925 group by call_id) shared,
                         case when (aa.ref_id=cr.agent or aa.ref_id='*') then '1' else '0' end adm
                       from callrecords cr
                       left join cdr c on (c.uniqueid like substr(cr.call_id,1,15)||'%' and dst like '%'||cr.phonenum and dstchannel is not null)
                       left join queue_log q on (q.CALLID=cr.CALL_ID  and q.EVENT in ('COMPLETEAGENT','COMPLETECALLER','TRANSFER') )
                       left join (select dn.ref_id from dostup d, d_nodes dn
                         where dn.dostup_id=d.dostup_id
                           and d.class_id=4240 and d.uname=substr(user,3)) aa on (aa.ref_id=cr.agent or aa.ref_id='*')
                       where ((:cli is null and cr.call_date > nvl(to_date(:sd,'dd/mm/yyyy'),trunc(sysdate))) or (:cli is not null and cr.call_date > nvl(to_date(:sd,'dd/mm/yyyy'),trunc(sysdate,'year'))))
                         and cr.call_date < nvl(to_date(:ed,'dd/mm/yyyy')+1,sysdate)
--                        ((cr.call_date between nvl(to_date(:sd,'dd/mm/yyyy'),trunc(sysdate)) and nvl(to_date(:ed,'dd/mm/yyyy'),trunc(sysdate))+1) or (nvl(:cli,'none')!='none') or (nvl(:cli,'*')!='*') and nvl(:sd,'none')='none' )
                         and (cr.agent=:ag or nvl(:ag,'*')='*')
                         and (cr.phonenum like '%'||:cli||'%' or nvl(:cli,'*')='*')
                       order by call_id desc) a
                  where ((a.t1 is not null or a.t2 is not null or a.t3 is not null or a.t4 is not null or a.t5 is not null) or nvl(:marks,'false')='false')
                    and (to_number(a.bs)>to_number(:time)*60 or nvl(:time,'*')='*')
                  order by call_id";
  */                
  $sql="select distinct call_id,
     filename,
     agent,
     event,
     phonenum,
     direction,
     billsec,
     bs,
     t1,t2,t3,t4,t5,
     u1,u2,u3,u4
     s1,s2,s3,s4,s5,
     adm
   from (SELECT c.calldate,
      c.uniqueid call_id,
      nvl(cr.filename,'/mnt/disk0/monitor/'||to_char(c.calldate,'YYYY')||'/'||to_char(c.calldate,'MM')||'/'||c.uniqueid) filename,
      CASE WHEN length(c.src)=4 
        THEN c.src
        ELSE substr(q.agent,instr(q.agent,'/')+1,4)
      END agent,
      q.event,
      CASE when LENGTH(c.src)>4 
        THEN c.src 
      ELSE CASE WHEN length(c.dst)=8
          THEN substr(c.dst,1)
        WHEN length(c.dst)=10 
          THEN c.dst
        WHEN length(c.dst)=12
          THEN substr(c.dst,3)
        WHEN length(c.dst)>=14
          THEN substr(c.dst,4)
        ELSE c.dst
        END
      END phonenum,
      CASE WHEN length(c.src)=4 THEN 'out' ELSE 'in' END direction,
      lpad(extract(hour from numtodsinterval(nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec),'second')),2,'0') || ':' ||
        lpad(extract(minute from numtodsinterval(nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec),'second')),2,'0') || ':' ||
        lpad(extract(second from numtodsinterval(nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec),'second')),2,'0') billsec,
      nvl(case when q.event='TRANSFER' then q.data4 else q.data2 end,c.billsec) bs,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select value from callrec_prop where call_id=c.uniqueid and seq=1 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select value from callrec_prop where call_id=c.uniqueid and seq=1 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end t1,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select value from callrec_prop where call_id=c.uniqueid and seq=2 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select value from callrec_prop where call_id=c.uniqueid and seq=2 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end t2,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select value from callrec_prop where call_id=c.uniqueid and seq=3 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select value from callrec_prop where call_id=c.uniqueid and seq=3 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end t3,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select value from callrec_prop where call_id=c.uniqueid and seq=4 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select value from callrec_prop where call_id=c.uniqueid and seq=4 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end t4,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select value from callrec_prop where call_id=c.uniqueid and seq=5 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select value from callrec_prop where call_id=c.uniqueid and seq=5 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end t5,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select note from callrec_prop where call_id=c.uniqueid and seq=1 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select note from callrec_prop where call_id=c.uniqueid and seq=1 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end s1,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select note from callrec_prop where call_id=c.uniqueid and seq=2 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select note from callrec_prop where call_id=c.uniqueid and seq=2 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end s2,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select note from callrec_prop where call_id=c.uniqueid and seq=3 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select note from callrec_prop where call_id=c.uniqueid and seq=3 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end s3,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select note from callrec_prop where call_id=c.uniqueid and seq=4 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select note from callrec_prop where call_id=c.uniqueid and seq=4 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end s4,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select note from callrec_prop where call_id=c.uniqueid and seq=5 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select note from callrec_prop where call_id=c.uniqueid and seq=5 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end s5,
      (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID 
        and s.uname=(case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select uname from callrec_prop where call_id=c.uniqueid and seq=1 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select uname from callrec_prop where call_id=c.uniqueid and seq=1 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end)) u1,
      (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID 
        and s.uname=(case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select uname from callrec_prop where call_id=c.uniqueid and seq=2 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select uname from callrec_prop where call_id=c.uniqueid and seq=2 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end)) u2,
      (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID 
        and s.uname=(case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select uname from callrec_prop where call_id=c.uniqueid and seq=3 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select uname from callrec_prop where call_id=c.uniqueid and seq=3 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end)) u3,
      (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID 
        and s.uname=(case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select uname from callrec_prop where call_id=c.uniqueid and seq=4 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select uname from callrec_prop where call_id=c.uniqueid and seq=4 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end)) u4,
      (select p.name from persons p,staff s where s.person_id=p.person_id and s.PERSON_SID=p.PERSON_SID 
        and s.uname=(case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') 
        then (select uname from callrec_prop where call_id=c.uniqueid and seq=5 and prop_id=6925 and nvl(instr(options,'D'),0)=0)
        else (select uname from callrec_prop where call_id=c.uniqueid and seq=5 and prop_id=6925 and nvl(instr(options,'S'),0)!=0 and nvl(instr(options,'D'),0)=0)
      end)) u5,
      (select sum(case nvl(instr(options,'S'),0) when 0 then 0 else 1 end) from callrec_prop where call_id=c.uniqueid and prop_id=6925 group by call_id) shared,
      case when (aa.ref_id=(CASE WHEN length(c.src)=4 THEN c.src ELSE substr(q.agent,instr(q.agent,'/')+1,4) END) or aa.ref_id='*') then '1' else '0' end adm
    FROM cdr c
    left join queue_log q on (q.CALLID=c.uniqueid  and q.EVENT in ('COMPLETEAGENT','COMPLETECALLER','TRANSFER') )
    LEFT JOIN callrecords cr ON cr.call_id=c.uniqueid
    left join (select dn.ref_id from dostup d, d_nodes dn
        where dn.dostup_id=d.dostup_id
        and d.class_id=4240 and d.uname=substr(user,3)) aa on (aa.ref_id=(CASE WHEN length(c.src)=4 
          THEN c.src
          ELSE substr(c.dstchannel,instr(c.dstchannel,'/')+1,4)
          END) or aa.ref_id='*')
    WHERE (c.channel LIKE '%old%' OR c.dstchannel LIKE '%old%' ) 
      AND c.dstchannel IS NOT NULL
      and c.billsec>0
      and calldate > nvl(to_date(:sd, 'dd/mm/yyyy'), trunc(sysdate, 'year'))
    order by c.calldate) a
  where (to_number(a.bs)>to_number(:time)*60 or nvl(:time,'*')='*')
    and ((a.t1 is not null or a.t2 is not null or a.t3 is not null or a.t4 is not null or a.t5 is not null) or nvl(:marks,'false')='false')
    AND ((:cli is null and calldate > nvl(to_date(:sd,'dd/mm/yyyy'),trunc(sysdate))) or (:cli is not null and calldate > nvl(to_date(:sd,'dd/mm/yyyy'),trunc(sysdate,'year'))))
    and calldate < nvl(to_date(:ed,'dd/mm/yyyy')+1,sysdate)
    AND (a.agent=:ag or nvl(:ag,'*')='*')
    and (a.phonenum like '%'||:cli||'%' or nvl(:cli,'*')='*')
    and agent is not NULL";
      
      
      
  $stmt=oci_parse($conn,$sql);
  oci_bind_by_name($stmt,":sd",@$_GET['startdate']);
  oci_bind_by_name($stmt,":ed",@$_GET['enddate']);
  oci_bind_by_name($stmt,":ag",@$_GET['agent']);
  if(@$_GET['empty']=="true") $_GET['client']="";
  oci_bind_by_name($stmt,":cli",@$_GET['client']);
  oci_bind_by_name($stmt,":marks",@$_GET['marks']);
  oci_bind_by_name($stmt,":time",@$_GET['time']);
  oci_execute($stmt);
$page=($_GET['page']-1)*$pagesize;
$cnt=oci_fetch_all($stmt,$data,$page,$pagesize,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC+OCI_RETURN_NULLS);
//$data=array();
/*$data=array_fill(0,300,array("calldate"=>'0000-00-00 00:00:00',
                              "call_id"=>'0000000000.000000',
                              "agent"=>'0000',
                              "event"=>'0000000000000000',
                              "phonenum"=>'0000000000000000',
                              "direction"=>'000',
                              "billsec"=>'00:00:00',
                              "bs"=>'000',
                              "t1"=>'000',
                              "t2"=>'000',
                              "t3"=>'000',
                              "t4"=>'000',
                              "t5"=>'000',
                              "s1"=>'000',
                              "s2"=>'000',
                              "s3"=>'000',
                              "s4"=>'000',
                              "s5"=>'000',
                              "u1"=>'000',
                              "u2"=>'000',
                              "u3"=>'000',
                              "u4"=>'000',
                              "u5"=>'000',
                              "adm"=>'000'
                            )
                );
$i=0;
while($row=oci_fetch_array($stmt,OCI_ASSOC+OCI_RETURN_NULLS)){
//  var_dump($row);
//  foreach($row as $key=>$val){
    $data[$i]=$row;
//  }
  $i++;
}
$cnt=count($data);*/
//var_dump($data);
//для фильтра автомата
//$onchange="onchange=\"document.location.href=filter()\"";
$onchange="";
//echo "<menu type=context id=marks style='visibility:hidden'>
//        <menuitem label=good onclick='setmark(markedcallid,markseq,1)'></menuitem>
//        <menuitem label=bad onclick='setmark(markedcallid,markseq,0)'></menuitem>
//        <menuitem label=delete onclick='delmark(markedcallid,markseq)'></menuitem>
//      </menu>";
$divmenu="<div id=menu class='comment inline'>
            <div class=menuitem onclick=setmark(markedcallid,markseq,1)>good</div>
            <div class=menuitem onclick=setmark(markedcallid,markseq,0)>bad</div>
            <div class=menuitem onclick=delmark(markedcallid,markseq)>delete</div>
          </div>";
//Фильтр
#var_dump($_GET) ;
echo "<table id=middle border=1 frame=void rules=all>
        <thead id=t_head>
          <td>C <input class=date id=startdate name=startdate onclick=\"displayDatePicker(this.name);\" ".$onchange."  value=".@$_GET['startdate'].">
              по <input class=date id=enddate name=enddate onclick=\"displayDatePicker(this.name);\" ".$onchange." value=".@$_GET['enddate']."></td>
          <td><input id=agent name=agent placeholder=Оператор ".$onchange." value=".@$_GET['agent']."></td>
          <td></td>
          <td><input id=client name=client placeholder=Клиент ".$onchange." value=".@$_GET['client']."><input type=checkbox name='empty' ";
if (@$_GET['empty']=="true") echo "checked";
echo "></td>
          <td>более <input name='time' size=2 value=".@$_GET['time'].">мин.</td>
          <td style='width:20em'>Файл</td>
          <td><input type=checkbox name=marks ";
if(@$_GET['marks']=="true") echo "checked";
echo " ".$onchange." >Оценки <button type=button onclick=\"document.location.href=filter()\">Фильтр</button><button type=button onclick=\"document.location.href=document.location.href.split('?')[0]\">X</button></td>
        </thead>";
echo "<tbody id=paging><tr><td colspan=7 align=right>";
//листинг по стр
if (@$_GET['page']>1) echo "<a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']-1)."'\">Пред.</a>";
if ($cnt==$pagesize) echo " <a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']+1)."'\">След.</a>";
echo "</td></tr></tbody>";
$CC=array("in"=>0,"out"=>0);
foreach($data as $row){
  $file=array_reverse(explode('/',$row['FILENAME']));
  $CC[$row['DIRECTION']]++;
  //Инфа о записи
  echo "<tbody id='".$row['CALL_ID']."'><tr>
            <td>".date("Y-m-d H:i:s",$row['CALL_ID']-3600)."</td>
           <td style='cursor:pointer;' onclick=\"document.getElementById('agent').value=".$row['AGENT'].";\" >".$row['AGENT']."</td>
            <td><img class='pic' src=./images/".$row['DIRECTION'].".png><img class='pic' src=./images/".$row['EVENT'].".png></td>
            <td style='cursor:pointer' <!-- onclick=\"document.getElementById('client').value=".$row['PHONENUM'].";\"  >".$row['PHONENUM']."</td>
            <td><span class='";
  if($row['BS']>600) echo "red";
  $f_path=$file[2]."/".$file[1]."/".$file[0];
  echo "'>".$row['BILLSEC']."</span></td>
            <td><div onclick=showPlayer(this,'".$f_path."')>Воспроизвести запись</div></td><td>";
  $set=1;
  $comment="";
  if(($row['ADM']==1)||($row['SHARED']==5)){
  //Оценки
    for($i=1; $i<=4; $i++){
    //  if(($row['SHARED']!=5)&&($row['ADM']==0)) $row['T'.$i]='';
      echo "<img id='".$i."'  class='pic mark";
    //фингер дирекшн
      switch ($row['T'.$i]){
        case "0": 
          echo " down";
          break;
        case "1": 
          echo " up";
          break;
        default: 
          echo " middle"; 
          $set=0;
          break;
      }
      echo "' src='./images/thumb.png'";
      //доступ к проставлению оценки
      if($row['ADM']==1){
       echo " onclick='marktoggle(\"".$row['CALL_ID']."\",\"".$row['S'.$i]."\",this)' oncontextmenu='markedcallid=\"".$row['CALL_ID']."\";markseq=".$i.";toggleCommentBox(event,this);return false;'";
      }
      echo " title='".$row['U'.$i].":\n".$row['S'.$i]."' alt='".$row['T'.$i]."' value='".$row['T'.$i]."'>".$divmenu;
      //сум коммент
      if($row['S'.$i]!="")  $comment.=$row['U'.$i].":<br>    ".$row['S'.$i]."<br>";
    }
  //Шаринг оценок
    if(($set==1)&&($row['ADM']==1)){
#      echo "<input type=checkbox name='shared' onclick='sharemarks(\"".$row['CALL_ID']."\",this)'";
#      if($row['SHARED']==5) echo "checked=true";
#      echo ">Поделиться";
      echo " <img class='pic' src='./images/mail.png' onclick=sendmail('".$row['CALL_ID']."') title='Уведомить письмом'>
            <div class='inline button' id=comm onclick='cleanCommBox();toggleCommentBox(event,this);return false;'>Коментарии</div><div id=comm1 class=comment>".@$comment."</div>";
    }
  }
echo "</td></tr></tbody>";
}
echo "<tbody id=paging><tr><td colspan=7 align=right>";
//листинг по стр
echo "Входящих на странице: <p class=bold>".$CC["in"]."</p> Исходящих на странице: <p class=bold>".$CC["out"]."</p> ";
if (@$_GET['page']>1) echo "<a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']-1)."'\">Пред.</a>";
if ($cnt==$pagesize) echo " <a href=\"javascript:void(0)\" onclick=\"document.location.href=filter()+'&page=".($_GET['page']+1)."'\">След.</a>";
echo "</td></tr></tbody>";
echo "</table>";
oci_free_statement($stmt);

oci_close($conn);
?>
</body>
</html>

