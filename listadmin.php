<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<?php include('./auth2.php'); ?>
<LINK href="style.css" rel="stylesheet" type="text/css">
<style>
#searchuser,#searchoper{
  width:400px;
}
#s_searchuser,#s_searchoper{
  width:400px;
  visibility:hidden;
  position:absolute;
  z-index:999;
}
</style>
<script src="script.js" type="text/javascript"></script>
<script language="javascript">
var timer=0;

function searchuser(e,elem,targetid){
  clearTimeout(timer);
  target=document.getElementById(targetid);
  if(elem.value.length>2){
    if (e.keyCode==40){
       target.focus();
       target.selectedIndex=0;
    }else{
      target.options.selectedIndex=0;
      target.style.visibility='visible';
      timer=window.setTimeout(loadExternalContent('./'+elem.id+'.php?search='+elem.value,target),1000);  // загружаю через 1 секунду после последнего нажатия клавиши
      enabl(target);
    }
  }else{
    target.options.selectedIndex=-1;
    hide(target);
  }
}

function choose(elem,targetid,loadid){
  target=document.getElementById(targetid);
  target.value=(elem.options[elem.selectedIndex].text);
  hide(elem);
  target.focus();
  disabl(target);
  disabl(elem);
  if(loadid==''){
    addtolist(elem.value);
  }
  if(loadid!=''){
    loadto=document.getElementById(loadid);
    loadExternalContent('./'+loadid+'.php?user='+elem.value,loadto);
  }
}

function selectKeyUp(e,elem,targetid,loadid){
  target=document.getElementById(targetid);
  console.log(e.keyCode);
  if(e.keyCode==40) return false;
  if(e.keyCode==13){ // Enter
    choose(elem,targetid,loadid);
    return;   
  }
  if(elem.selectedIndex==0&&e.keyCode==38){ // Up
    target.focus();
  }
  if(e.keyCode==8) target.focus();
}

function hide(elem){
  elem.style.visibility='hidden';
}
function enabl(elem){
  elem.removeAttribute('readonly');
  elem.removeAttribute('disabled');
}
function disabl(elem){
  if(elem.type=="select-one"){
    elem.setAttribute('disabled','true');
  }else{
    elem.setAttribute('readonly','true');
  }
}
function addtolist(oper){
  loadExternalContent('./addoper.php?user='+user.value+'&oper='+oper,'');
  loadExternalContent('./operlist.php?user='+user.value,operlist)
}

function delfromlist(oper){
  loadExternalContent('./deloper.php?user='+user.value+'&oper='+oper,'');
  operlist.innerHTML=loadExternalContent('./operlist.php?user='+user.value,operlist)
}
function addfullaccess(elem){
  if(elem.checked){
    addtolist('*');
  }else{
    delfromlist('*');
  }
}

function onLoad(){
  user=document.getElementById('s_searchuser');
  operlist=document.getElementById('operlist');
  notifybox=document.getElementById('notifybox');
}
</script>
</head>
<body onload="onLoad()" onclick="cleanMenus()">
<?php
include('header.php');
echo "<table id=middle><tr><td>";
include('./checkadmin.php');
oci_close($conn);
?>
Старший смены: <input id='searchuser' name='searchuser' size=45 onkeyup="searchuser(event,this,'s_searchuser')" onclick="enabl(this)" autofocus=true tabindex=1 placeholder="Выбирите старшего оператора смены"><br>
<select size=10 id='s_searchuser' name='s_searchuser'  onkeyup="selectKeyUp(event,this,'searchuser','operlist')" onclick="choose(this,'searchuser','operlist')"></select></td></tr>
<tr><td><div id=operlist></div></td></tr>
<tr><td><div id=notifybox></div></td></tr>
</table>
</body>
</html>