<!DOCTYPE html>
<html>
<meta charset="utf-8">
<LINK href="style.css" rel="stylesheet" type="text/css">
<LINK href="./ckeditor/contents.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./ckeditor/skins/kama/editor.css">
<script src="./script.js" type="text/javascript"></script>
<script src="./ckeditor/ckeditor.js" type="text/javascript"></script>
<script type="text/javascript" src="./ckeditor/lang/ru.js"></script>
<script type="text/javascript" src="./ckeditor/plugins/styles/styles/default.js"></script>
<script src="./ckeditor/config.js" type="text/javascript"></script>
<head>
<script type="text/javascript">
function search(e,elem,flag=null){
//  console.log(e.keyCode);
  if(!elem.dataset.minchar) elem.dataset.minchar=3;
  if(elem.value.length<elem.dataset.minchar) return;
  elem.classList.remove('ok');
  sel=elem.parentNode.querySelector("select");
  sel.selectedIndex=-1;
  sel.value='';
  opts=sel.options;
  a=0;
  if(e.keyCode==40){
    sel.focus();
    sel.selectedIndex=dopts[0].index;
  }else if(e.keyCode==38 & sel.selectedIndex==0){
    sel.selectedIndex=-1;
    sel.value="";
  }else  if(e.keyCode!=39 && e.keyCode!=37){
    if(typeof elem.dataset.dir !== 'undefined'){
      if(typeof elem.value !== 'undefined'){
        value=elem.value;
      }else if(typeof elem.dataset.str !== 'undefined' && document.querySelector('input[name='+elem.dataset.str+']')){
        value=document.querySelector('input[name='+elem.dataset.str+']').value;
      }
      get(elem.dataset.dir+'.php?str='+elem.value,sel,null);
    }else{
//    if(opts.length>0){
      sel.style.visibility='visible';
      for(i=0;i<opts.length;i++){
        if(opts[i].innerText.toLocaleLowerCase().indexOf(elem.value.toLocaleLowerCase())==-1){
          opts[i].disabled=true;
        }else{
          a++;
          opts[i].disabled=false;
        }
      }
    }
    dopts=elem.parentNode.querySelectorAll("select > option:not(:disabled)");
    if(dopts.length==1){
      if(e.keyCode!=8){
        sel.selectedIndex=dopts[0].index;
        if(flag) sel.onchange(sel);//choose(sel,elem);
      }
    }else if(a==opts.length){
      hide(sel);
    }else{
      sel.style.visibility='visible';
    }
  }
}
function choose(sel,e,inp=sel.parentNode.firstChild){
  console.log(e);
  if(typeof e!=="undefined"){
    e.stopPropagation();
    
  }
  if(sel.selectedIndex!=-1){
    if(sel.name=='s_CLIphone' && Object.keys(sel.selectedOptions[0].dataset).length>0){
      if(typeof sel.selectedOptions[0].dataset.name!=="undefined"){
        document.querySelector('[name=CLIname]').value=sel.selectedOptions[0].dataset.name;
      }
      if(typeof sel.selectedOptions[0].dataset.phone!=="undefined"){
        document.querySelector('[name=CLIphone]').value=sel.selectedOptions[0].dataset.phone;
      }
      if(typeof sel.selectedOptions[0].dataset.type!=="undefined"){
        document.querySelector('#type'+sel.selectedOptions[0].dataset.type+'[name=CLItype]').checked=true;
      } 
      if(!document.querySelector('[name=CLIENT_ID]')){
        cli=document.createElement('input');
        cli.type='hidden';
        cli.name='CLIENT_ID';
        sel.closest('.section').appendChild(cli);
      }
      document.querySelector('[name=CLIENT_ID]').value=sel.selectedOptions[0].value;
    }else{
      setChoosenOption(sel,inp);
//      inp.value=sel.options[sel.selectedIndex].innerHTML;
//      inp.classList.add('ok');
    }
    hide(sel);
    if(typeof sel.dataset.next!=='undefined') load_ref(sel.value,sel.dataset.next);
    inp.setAttribute("readonly","true");
//  }else if(sel.options.length==1){
//    inp.value=sel.options[sel.selectedIndex].innerHTML;
//    inp.classList.add('ok');
  }
}
function selKey(e,elem){
  opts=elem.parentNode.querySelectorAll("select > option:not(:disabled)");
  input=elem.parentNode.querySelector('input');
//  console.log(e.keyCode);
  if(e.keyCode==13){
//    input.value=elem.options[elem.selectedIndex].innerHTML;
//    choose(elem,input);
//    console.log(elem);
    elem.onchange.call(elem,e);
  }else if(e.keyCode==38 && sel.selectedIndex<opts[0].index){
    sel.selectedIndex=-1;
    sel.value=""; 
    input.focus();
    input.classList.remove('ok');
  }
  return false;
}
function load_ref(id,target){
  tgt=document.querySelector('select[name="DETALE['+target+']"]');
  get('./ref.php?detale_id='+id,tgt);
}
function loadbrand(id){
  get("./car_model.php?brand_id="+id,document.querySelector("#car_model"),null);
  document.querySelector("#allbrands").classList.add("hide");
}
function clickInput(elem,event){
  if(typeof event!=="undefined" && typeof event.srcElement.select=="function") event.srcElement.select();
//  console.log(elem);
  if(document.querySelector('#ISS_ID')){
    document.querySelector('#ISS_ID').removeAttribute('readonly');
    document.querySelector('#ISS_ID').removeAttribute('disabled');
  }
  document.querySelector('#submit').removeAttribute('disabled');
  input=elem.querySelectorAll('input');
  input.forEach(function(e){
    if(e.closest('.input')) e.closest('.input').style.filter='unset';
    e.removeAttribute('readonly');
    e.removeAttribute('disabled');
//    e.select();
    e.classList.remove('ok');
//    e.style.border='';
  });
  select=elem.querySelectorAll('select');
  select.forEach(function(e){
   e.removeAttribute('readonly');
   e.removeAttribute('disabled');
  });
  ed=elem.querySelectorAll('iframe');
  ed.forEach(function(e){
    if(e.tagName=='TEXTAREA'){
      e.removeAttribute('readonly');
      e.removeAttribute('disabled');
    }
    if(typeof editor!=='undefined' && (typeof editor.element==='undefined' || !elem.contains(editor.element.$))){
      editor.destroy();
      delete editor;
      document.querySelectorAll('.overlay').forEach(function(ov){ 
        ov.style.display=''; 
      });
    }
//    console.log('start showEditor on'+e)
    showEditor(e);
  });
}
function selectCar(elem){
  elem.parentNode.querySelectorAll('.ok').forEach(function(t){t.classList.remove('ok')})
  elem.classList.add('ok');
  document.querySelector('input[name="CAR"]').value=elem.dataset.id;
  elem.parentNode.querySelectorAll('.car_model').forEach(function(t){
  if(t!=elem) t.parentNode.removeChild(t) });
}
function showTicket(id){
  func=null;
  elemid=null;
  if(id==''){ 
    func=showEditor;
    elemid='BODY';
  }
  if(typeof editor!=='undefined') editor.destroy();
  get('ticket.php?id='+id,modal,func,elemid);
}

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
</script>
</head>
<body onLoad='getList()' onmousemove="getPosition(event)">
<script type="text/javascript">
  document.body.onkeyup = function (e) { if (e.keyCode == 27) { closeModal(); }}
</script>
<div id=notifbox></div>
<div id=hint></div>
<div id=loading style='display:none;'></div>
<div class=modal id=modal></div>
<?php
include_once('../auth2.php');
include_once("can.php");
if(!empty($_GET['action']) && $_GET['action']=="create"){
  $args="";
  if(!empty($_GET['id'])){
    $args.="action=copy";
    $args.="&id=".$_GET['id'];
  }else{
    $args.="action=create";
  }
  if(!empty($_GET['channelid'])){
    $args.="&channelid=".$_GET['channelid'];
  }
  if(!empty($_GET['client'])){
    $args.="&client=".$_GET['client'];
  }else if(!empty($_GET['cliid'])){
    $args.="&cliid=".$_GET['cliid'];
  }
  echo "<script type=text/javascript>get('ticket.php?".$args."',modal);</script>";
}

echo "<body><table id=ticketlist><thead><tr>
  <td><input type=text class='listfilter' name=iss_id placeholder='Номер' onkeyup='loadlist(event,this)' data-minfstr=1></td>
  <td><input type=text class='listfilter' name=subject placeholder='Тема' onkeyup='loadlist(event,this)' data-minfstr=3></td>
  <td><input type=date class='listfilter' name=iss_date placeholder='Создано' onkeyup='loadlist(event,this)' data-minfstr=8></td>
  <td><input type=text class='listfilter' name=creator placeholder='Автор'></td>
  <td><input type=text class='listfilter' name=phone_num placeholder='Телефон' onkeyup='loadlist(event,this)' data-minfstr=4></td>
  <td><input type=text class='listfilter' name=name placeholder='Имя' onkeyup='loadlist(event,this)' data-minfstr=3></td>
  <td><input type=text class='listfilter' name=type placeholder='Тип обращения' onkeyup='loadlist(event,this)' data-minfstr=3></td>
  <td><input type=text class='listfilter' name=model placeholder='Оборудование' onkeyup='loadlist(event,this)' data-minfstr=2></td>
  <td><input type=text class='listfilter' name=reason placeholder='Причина обращения' onkeyup='loadlist(event,this)' data-minfstr=3></td>
  <td><input type=text class='listfilter' name=car placeholder='Автомобиль'></td>
  <td style='background-color:rgb(150,255,185); text-align:center;' onclick=showTicket('')><span style='margin-left:1em;margin-right:1em;cursor:default;' >Создать</span></td>
  </tr></thead><tbody>";
echo "</tbody></table></body>";
