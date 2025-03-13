var sortdir=new Array();
var terminateFunc=false;
var json="";
var mh="15em";
var coord;
// JSON -> Logline (put given json to log line on page)
function iterateLOG(json,cls){
  i=0;
  for(var e in json){
    if(terminateFunc) break;
    notif=document.getElementById("notifbox");
    div=document.createElement("div");
    div.classList.add(cls);
    div.innerHTML=time()+json[e];
    notif.insertBefore(div,notif.firstElementChild);
    i++;
  }
  return i;
}
//parse json answer for notifbox
function parseJsonLog(json,target){
        n=0;
      if(json["action"]=="login"){
          if(json["S"].length==0){
            if(json["output"].length>0){
              //Форма авторизации при неудаче
              document.getElementById("itemtext").innerHTML=json["output"][0];
              document.getElementById("itembox").style.visibility="visible";
            }
            n+=iterateLOG(json["E"],"red");
            n+=iterateLOG(json["W"],"yellow");
            n+=iterateLOG(json["S"],"green");
            n+=iterateLOG(json["I"],"green");
            setTimeout(function(){loading.style.display="none"},200);
            req=null;
            target=null;
            return;
          }else{
            document.getElementById('userid').innerHTML=json["user"];
            
          }
      }else if(json["action"]=="status"){
          st=document.querySelector('#status'); 
          if(json['name'] && json['id']){
            st.classList='inline s'+json['id'];
            st.innerHTML=json['name'];
          }
        }else if(json["action"]=="ticketlist"){
          tbody=document.querySelector('#ticketlist > tbody');
          tbody.innerHTML='';
          cols=document.querySelectorAll('#ticketlist .listfilter');
          for(var row in json['data']){
            tr=document.createElement('tr');
            tr.setAttribute('onclick','showTicket(this.id)');
            tr.setAttribute('id',json['data'][row]['ISS_ID']);
            tr.classList='ticket';
            for(i in cols){
              if(typeof cols[i]=="object"){
                name=cols[i].name;
                td=document.createElement('td');
                if(i==0) td.classList.add('s'+json['data'][row]['STATUS_ID']);
                td.innerHTML=json['data'][row][name.toUpperCase()];
                tr.appendChild(td)
              }
            }
            tbody.appendChild(tr);
          }
        }else if(json["action"]=="client"){
          clientOptions(json['data'],target);
        }else if(json["action"]=="records"){
          recordOptions(json['data'],target);
        }else if(json["action"]=="user"){
          userOptions(json['data'],target);
        }else if(json["action"]=="assign"){
          if(json['data']){
            setChoosenOption(target);
//            choose(target);
            hide(target);
          }
        }
        
        if(json["hint"]){
          showHint(json["hint"]);
        }
        //Логи
          n+=iterateLOG(json["E"],"red");
          n+=iterateLOG(json["W"],"yellow");
          n+=iterateLOG(json["S"],"green");
          n+=iterateLOG(json["I"],"green");
          if(target==null){
            n+=iterateLOG(json["output"],"green");
          }else if(target=="file"){
            if(json["output"].length>0) getFileJSON(atob(json["output"]),json["output-type"],json["output-name"]);
          }else{
            for(var o in json["output"]){
              target.innerHTML+=json["output"][o];
            }
        }
        delete div;
        setTimeout(function(){loading.style.display="none"},200);
        notif.scrollTo(0,0);
        if(n>0) notif.style.maxHeight=1.25*n+"em";
        setTimeout(function(){notif.style.maxHeight=".15em"},6000);
}
//update clients options in select box after xhr
function clientOptions(data,target){
    target.length=0;
    for(t in data){
      opt=document.createElement('option');
      opt.value=data[t]['CLIENT_ID'];
      opt.dataset.type=data[t]['TYPE_ID'];
      opt.dataset.phone=data[t]['PHONE_NUM'];
      opt.dataset.name=data[t]['C_NAME'];
      opt.innerHTML=data[t]['C_NAME']+' '+data[t]['PHONE_NUM'];
      target.appendChild(opt);
    }
    target.style.visibility='visible';
}
//update recordlist in select box after xhr
function recordOptions(data,target){
  target.length=0;
  opt=document.createElement('option');
  opt.innerHTML='-';
  opt.value='';
  opt.selected=true;
  target.appendChild(opt);
  for(t in data){
    opt=document.createElement('option');
    opt.value=data[t]['CALL_ID'];
    opt.dataset.file=data[t]['FILENAME'];
    opt.innerHTML=data[t]['CALL_DATE']+' '+data[t]['AGENT']+' '+data[t]['PHONENUM'];
    target.appendChild(opt);
  }
  if(data.length>0){
    target.style.visibility='visible';
  }else{
    hide(target);
  }
}
//update user options in select box after xhr
function userOptions(data,target){
  target.length=0;
  if(data.length==0) showHint("Не найдено");
  for(t in data){
    opt=document.createElement('option');
    opt.value=data[t]['UNAME'];
    opt.innerHTML=data[t]['UNAME']+' - '+data[t]['NAME'];
    target.appendChild(opt);
  }
}
// clock not used here
function time(){
  var d = new Date();
  return ("0"+d.getHours()).slice(-2)+":"+("0"+d.getMinutes()).slice(-2)+":"+("0"+d.getSeconds()).slice(-2)+" - ";
}

//init xhr request
function getreq(){
  if (window.XMLHttpRequest) {
    req = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    req = new ActiveXObject("Microsoft.XMLHTTP");
  }
  loading=document.getElementById('loading');
  req.onabort==function(){ loading.style.display="none";};
  return req;  
}
//xhr post request
function send(url,data,target,postfunction=hide,param=document.getElementById("itembox"),param2=null,param3=null){
  terminateFunc=false;
  var req=getreq();  
  req.onreadystatechange=function(){ updatereqtarget(req,target,postfunction,param,param2,param3) };
  req.open("POST", url, true);
  if(target=="_blank"){
    req.responseType = 'blob';
  }
  req.send(data);
  delete data;
}
//xhr get request
function get(url,target,postfunction=hide,param=document.getElementById("itembox"),param2=null){
  terminateFunc=false;
  var req=getreq();  
  req.onreadystatechange=function(){ updatereqtarget(req,target,postfunction,param,param2) };
  req.open("GET", url, true);
  if(target=="_blank"||target=="file"){
    req.responseType = 'blob';
  }
  req.send();
}
//update target after xhr request
function updatereqtarget(req,target=null,postfunction=null,param=null,param2=null,param3=null){
  loading=document.getElementById('loading');
  if(terminateFunc){
    loading.style.display="none";
    req.abort();
    return false;
  }
  if (req.readyState == 4) {
    if (req.status == 200) {
//      if(target!=null) target.innerHTML="";
      if (((postfunction==null)||(postfunction==hide))&& loading!=null) setTimeout(function(){loading.style.display="none"},200);
      notif=document.getElementById("notifbox");
      content=req.getResponseHeader('content-type');
      if(req.response != "" && req.response.type=="application/json" && req.responseType=="blob"){
         req.response.text().then( function (data){
             try{
               json=JSON.parse(data);
               parseJsonLog(json,target);
             }catch(e){
               json="";
             }
          });
      }else if(content && content.indexOf("application/json")!== -1 && req.response != ""){
        json=JSON.parse(req.response);
        parseJsonLog(json,target);
      }else{
        json="";
      }
      if(json==""){
        if(req.response.type != "application/json"){
        //для загрузки файлов
          if(req.response!=""){
            if(target=="_blank"){
              if(window.navigator.msSaveOrOpenBlob) {
                window.navigator.msSaveBlob(req.response, fileName);
              }else{
              }
            }else if(target=="prompt"){
              loading.style.display="none";
              text=prompt(req.responseText);
              if(text!==null){
                if(postfunction!=null){
                  postfunction(param,param2,param3,text);
                }
              }
              return;
          //для загрузки справочников
            }else if(target!=null){
              target.innerHTML=req.responseText
              if(target.id=='page') document.forms.main.elements[0].focus();
            }else{
              showItem(req.responseText); 
            }
          } 
        }
      }
    }     
    req=null
    if(postfunction!=null){
      setTimeout(postfunction,500,param,param2,param3);
    }else{
      setTimeout(function(){ if(loading!=null) loading.style.display="none"},200);
    }
  //Ошибки http
  }else if(req.status=="404"){
    setTimeout(function(){loading.style.display="none"},200);
    if(target!=null){
      target.innerHTML="<option disabled>Не найден "+req.status+"</option>";
    }else{
      iterateLOG(["Предвиденная ошибка "+req.status],"red");
    }
  }else if(req.status=="500" || req.status=="502" || req.status=="504"){
    setTimeout(function(){loading.style.display="none"},200);
    if(target!=null){
      target.innerHTML="Непредвиденная ошибка "+req.status;
    }else{
      iterateLOG(["Непредвиденная ошибка "+req.status],"red");
    }
  }else if(req.status=="413"){
    setTimeout(function(){loading.style.display="none"},200);
  }else{
    if(loading!=null)loading.style.display="block";
  }
}
//not used
function load(elem){
  id=elem.id;
  b=document.querySelector("#buttons");
  b.querySelectorAll('button').forEach(function(a){ if(a!=elem){ a.removeAttribute('disabled') }else{ a.setAttribute('disabled','true')} });
  var req=getreq();
  target=document.getElementById('page');
  req.onreadystatechange=function(){ updatereqtarget(req,target)};
  req.onabort=function(){loading.style.display="none"};
  req.open("GET","/"+id+".php",true);
  req.send();
  document.title=elem.innerHTML+" - SAP.starline.ru";
  b.style.height="4em";
  elem.scrollIntoView();
}
//its for log i guess in parseJSON function
function scroll_to(parent,el){
  if(el===undefined){
    el=document.querySelector('#buttons').querySelector('button[disabled=true]');
  }
  if(el==null){
    parent.scrollTo(0,0);
  }else{
   el.scrollIntoView();
  }
}
//not used
function showMenu(el){
  e = window.event;
  posX = e.clientX;
  posY = e.clientY;
  elem=document.elementFromPoint(posX,posY);
  el.style.height=el.scrollHeight;
}
//not used
function showItem(text){
  notifbox=document.getElementById("notifbox");
  notifbox.innerHTML=text;
  notifbox.style.visibility="visible";
}
//not used
function loadform(elem,dats){
  data=new FormData();
  mid=document.getElementById("mid");
  maxsection=document.getElementById("maxsection");
  dats.nextform.split("").forEach(function(nextform){
    data.append("level",nextform);
    data.append(mid.name,mid.value);
    data.append(maxsection.name,maxsection.value);
    if(!dats.paramname) dats.paramname=elem.name;
    data.append(dats.paramname,elem.value);
    i=nextform;
    do{
      document.getElementById("form"+i).innerHTML="";
      i++;
    }while(document.getElementById("form"+i))
    send("form.php",data,document.getElementById("form"+nextform));
  });
}
//....
function hide(elem){
  if(elem!=null) elem.style.visibility="hidden";
}
//rewritten in index.php
/*function choose(elem,flag=true,paste=false, recursiveflag=false){
  if(elem.name.substring(elem.name.length-1)=="]"){
    idx=elem.name.split('[').pop().split(']')[0];
    suf="["+idx+"]";
  }else{
    suf="";
  }
  if(elem.previousElementSibling!=null){  
    if(target=elem.parentNode.getElementsByTagName('INPUT')[0]){
      if(elem.selectedIndex!=-1){
        target.value=elem.options[elem.selectedIndex].text;
        target.style.border="2px green solid";
        target.setAttribute("readonly","true");
      }else{
        target.style.border="2px red solid";
      }
      //для копирования значения в другое поле
      if((flag==true)&&(target.dataset.eq!== undefined)){
        eq=elem.parentNode.parentNode.getElementsByTagName('select').namedItem(target.dataset.eq+suf);
        if((eq!==undefined)&&(eq!==null)){
          if((eq.selectedOptions.length>0)&&(eq.selectedOptions[0].value=="")){
            eq.innerHTML="<option value="+elem.value+" selected>"+target.value+"</option>";
            choose(eq,false);
          }
        }
      }
      if((elem.dataset.batchinput) && (!paste)) {
        add(elem);
      }
    }
  }else{
    target=elem;
  }
      //для подстановки доп значения. например: еи материала
  if(elem.selectedOptions.length==1){
    for(d in elem.selectedOptions[0].dataset){
      if(elem.name.substring(elem.name.length-1)=="]"){
        idx=elem.name.split('[').pop().split(']')[0];
        suf="["+idx+"]";
        tt=document.getElementsByName(d.toUpperCase().split('[')[0]+suf);
        if(tt.length==0){
          suf="["+getElemNo(elem.parentNode)+"]";
          tt=document.getElementsByName(d.toUpperCase().split('[')[0]+suf);
        }
      }else{
        suf="";
        tt=document.getElementsByName(d.toUpperCase().split('[')[0]+suf);
      }
      dd=document.getElementsByName(elem.name)
      for(i=0;i<tt.length;i++){
        if(dd[i]==elem){
          if(tt[i].tagName=="SELECT"){
            tt[i].innerHTML="<option value="+elem.selectedOptions[0].dataset[d]+" selected>"+elem.selectedOptions[0].dataset["name_"+d]+"</option>";
            choose(tt[i],flag,paste,recursiveflag);
          }else if(tt[i].tagName=="INPUT"){
            tt[i].value=elem.selectedOptions[0].dataset[d];
          }
        }
      }
    }
  }
  if(typeof recursiveflag === "undefined"){
    recursiveflag=false;
  }
  if((recursiveflag==false)&&(elem.dataset.dep!==undefined)){
    arr=elem.dataset.dep.split(',').forEach(function(tt){
      target=elem.parentNode.parentNode.getElementsByTagName('select').namedItem(tt+suf)||elem.parentNode.parentNode.getElementsByTagName("input").namedItem(tt+suf);
      if(target.tagName=="SELECT"){
        target.value="";
        recursiveflag=true;
        runSearch(target,false);
        if(elem.selectedOptions[0].dataset[('X'+target.className).slice(0,5).toLowerCase()]=="X"){
          target.setAttribute('required',"true");
        }else{
          target.removeAttribute('required');
        }
      }
    });
  }
  recursiveflag=false;
  if(elem.dataset.cleantype!=null) document.querySelectorAll('[type='+elem.dataset.cleantype+']').forEach(function (el){el.value=''})
  if(elem.dataset.nextform!=null) loadform(elem,elem.dataset);
}*/
/*function selKey(e,elem){
  target=elem.previousElementSibling.previousElementSibling;
  if(e.keyCode==13){ 
    choose(elem);
    hide(elem);
    return false;
  }
  if((elem.selectedIndex==0)&&(e.keyCode==38)){
    target.removeAttribute('readonly');
    target.focus();
  }
  if(e.keyCode==8){
    target.removeAttribute('readonly');
    target.focus();
    hide(elem);
  }

}*/
//submit form with formElements checking by required attr
function submitForm(form,target=null,hideitem=false,postfunction=hide,param=document.getElementById("itembox"),param2=null,param3=null){
  url=form.action;
  ok=true;
  for(i=0;i<form.elements.length;i++){
    a=getFieldNoInForm(form.elements[i],form);
    if((form.elements[i].required)
      &&((form.elements[i].value.trim()=="") || (form.elements[i].type=="radio" && form.querySelectorAll('[name='+form.elements[i].name+']:checked').length==0))
      &&(!form.elements[i].disabled)
      &&((typeof form.elements[i].dataset.req=="undefined")||(
        (form.querySelectorAll('[name='+form.elements[i].dataset.req+']')[a].value!=0) && (form.querySelectorAll('[name='+form.elements[i].dataset.req+']')[a].value!=""))
      )
    ){ 
      ok=false;
      form.elements[i].parentNode.style.filter='drop-shadow(0px 0px 3px red)';
    }
  }
  if(!ok) return false;
  if((target!=null) && (document.getElementById(target)!=null)) target=document.getElementById(target);
  var data=new FormData(form);   
  if(typeof editor!=='undefined' && typeof editor.element!=='undefined' && form.contains(editor.element.$)){
    data.append(editor.name,editor.getData());
    editor.destroy();
    delete editor;
  }
  if(document.getElementById("submit")) document.getElementById('submit').setAttribute("disabled",true);
  send(url,data,target,postfunction,param,param2,param3);
  if(hideitem) document.getElementById('modal').innerHTML='';
}
//Hmmmm.... Doubt
function showLoading(){
  document.getElementById("loading").style.display="block";
}
//not used
function sort(asc,idx){
    if(typeof(sortdir[idx])=="undefined"){
      sortdir[idx]=asc;
    }else{
      sortdir[idx]=!sortdir[idx];
    }
    tbl=document.getElementById('tbl').getElementsByTagName('tbody')[0];
    t=tbl.children;
    unsort=true;
    num=false;
    while(unsort){
      if(terminateFunc) break;
      unsort=false;
      for(i=0;i<t.length-1;i++){
        val=t[i].children[idx].innerHTML;
        if(t[i+1].className=='sum') continue;
        nextval=t[i+1].children[idx].innerHTML;
        if(val==" ") val="0";
        if(nextval==" " || nextval===undefined) nextval="0";
        v=val.split(".");
        n=nextval.split(".");
        if(v.length==3){
          val=new Date(v[2],v[1],v[0]);
        }
        if(n.length==3){
          nextval=new Date(n[2],n[1],n[0]);
        }
        if((v.length<3)&&(n.length<3)&&(!isNaN(val))){
          val = parseFloat(val);
          nextval = parseFloat(nextval);
          num=true;
        }
        if(sortdir[idx] ? val>nextval:val<nextval){
          tbl.insertBefore(t[i+1],t[i]);
          unsort=true;
        }
      }
    }
    t=tbl.children;
    for(i=0;i<t.length;i++){
      if(terminateFunc) break;
      if(t[i].classList.contains('sum') || (t[i+1] && t[i+1].classList.contains('sum'))) continue;
      if(i%2==1){
        t[i].classList.add('odd');
        t[i].classList.remove('even');
      }else{
        t[i].classList.add('even');
        t[i].classList.remove('odd');
      }
      for(ii=0;ii<t[i].children.length;ii++){
        if(ii!=idx) t[i].children[ii].style.opacity="unset";
      }
      if(!num && t[i+1]!==undefined && t[i].children[idx].innerHTML==t[i+1].children[idx].innerHTML){
        t[i+1].children[idx].style.opacity="0.4";
      }
    }
    document.getElementById('loading').style.display="none";
}
//not used
function startsort(asc,idx){
  terminateFunc=false;
  showLoading();
  setTimeout(sort,500,asc,idx);
}
//not used
function resetEvaluation(elem){
  form=elem.form;
  elem.parentNode.parentNode.lastChild.innerHTML="";
  document.getElementsByName(form.elements.item(form.elements.length-1).name).forEach((v)=>{
    if((v.value=="A")&&((elem.value>elem.dataset.max)||(elem.value<elem.dataset.min))){
      v.disabled=true;
    }else{
      v.disabled=false;
    }
    v.removeAttribute("checked")
  });
}
//not used
function dsblRow(chk,multi=true){
  if(multi==false){
    tbody=chk.parentNode.parentNode.parentNode;
    for(t=0;t<tbody.childElementCount;t++){
      if(tbody.children[t].firstChild.firstChild!=chk){
        tbody.children[t].firstChild.firstChild.checked=false;
        dsblRow(tbody.children[t].firstChild.firstChild);
      }
    }
  }
  inputs=chk.parentNode.parentNode.getElementsByTagName("input");
  select=chk.parentNode.parentNode.getElementsByTagName("select")
  if(chk.checked==true){
    for(i=0;i<inputs.length;i++){
      if(inputs[i].className!="selitem") inputs[i].removeAttribute("disabled");
    }
    for(i=0;i<select.length;i++){
      select[i].removeAttribute("disabled");
    }
  }else{
    for(i=0;i<inputs.length;i++){
      if(inputs[i].className!="selitem") inputs[i].setAttribute("disabled",true);
    }
    for(i=0;i<select.length;i++){
      select[i].setAttribute("disabled",true);
    }
  }
}
//not used
function checkAuth(param=""){
  var req=getreq();
  req.onreadystatechange=function(){ updatereqtarget(req,null)};
  req.onabort=function(){loading.style.display="none"};
  req.open("GET","/auth.php?"+param,true);
  req.send();
}
//must not be used... i doubt
function reload(){
  document.location.href=document.location.origin+document.location.pathname;
}
//not used
function showTree(elem,e){
  elem.parentNode.nextElementSibling.classList.toggle("hidden");
  elem.classList.toggle("caret-down");
  e.stopPropagation();
}
//not used
function enableAll(elem){
  inputs=elem.parentNode.parentNode.parentNode.parentNode.getElementsByTagName("input");
  select=elem.parentNode.parentNode.parentNode.parentNode.getElementsByTagName("select");
  for(i=1;i<inputs.length;i++){
    if(inputs[i].type=="checkbox"){
      if(inputs[i].className=="selitem") inputs[i].checked=elem.checked;
    }
   if(inputs[i].className!="selitem") inputs[i].disabled=!elem.checked;
  }
  for(i=0;i<select.length;i++){
    select[i].disabled=!elem.checked;
  }
}
//not used
function selectAll(elem){
  inputs=elem.parentNode.parentNode.parentNode.parentNode.querySelectorAll(".selitem");
  for(i=0;i<inputs.length;i++){
    inputs[i].checked=elem.checked;
  }
}
//not used
function formReset(form,exclArr,focusElm){
  if(document.getElementById("submit")) document.getElementById("submit").removeAttribute("disabled");
  els=form.elements;
  for(a=0;a<els.length;a++){
    if(els[a].value!=""){
      if(!matchInArr(els[a].name,exclArr)) els[a].value="";
    }
    if(els[a].id==focusElm) els[a].focus();
  }
}
//not used
function matchInArr(str,arr){
  var result=false;
  for(i=0;i<arr.length;i++){
    if(str.match(RegExp(arr[i],"i"))!=null){
      result=true;
    }
  }
  return result;
}
//not used
function disabl(id,elem){
  inpt=document.getElementsByTagName('input');
  o=0;
  for(i=0;i<inpt.length;i++){ 
    if(inpt[i].name.indexOf(id)!=-1){
      inpt[i].value="";
      inpt[i].setAttribute("readonly","true");
      if(inpt[i].required){
        inpt[i].removeAttribute("required");
        o=1;
      }
    }
  }
  for(i=0;i<inpt.length;i++){
    if(inpt[i].name.indexOf(elem.name.split("-")[0])!=-1){
      inpt[i].removeAttribute("readonly"); 
      if(o==1){
        inpt[i].setAttribute("required","true");
      }
    }
  }
}
//not used
function showlog(){
  if(!selDetect()){
    n=document.getElementById('notifbox');
    if(parseFloat(n.style.maxHeight)<"15"){
      mh=n.style.maxHeight;
      n.style.maxHeight="15em";
    }else{
      n.style.maxHeight=mh;
    }
  }
}
//not used
function showdebug(elem){
  event.stopPropagation();
  elem.nextElementSibling.classList.toggle('hidden');
  showlog();
}
//not used
function getSelected(classname,filter=' '){
  var selarr = new Array()
  document.querySelectorAll('.'+classname+':checked').forEach( function(elem){ 
    if(elem.dataset[filter] === undefined || elem.dataset[filter]==""){
      idx=' ';
    }else{
      idx=elem.dataset[filter];
    }
    if(selarr[idx]===undefined) selarr[idx]=Array();
    selarr[idx].push(elem.value);
  });
  return selarr;
}
//rewritten. not used
function showcontextmenu(elem,e){
    e.stopPropagation();
  switch (elem.parentNode.lastChild.style.visibility) {
    case 'visible':
      elem.parentNode.lastChild.style.visibility="hidden";
      break;
    case 'hidden':
      elem.parentNode.lastChild.style.visibility="visible";
      break;
    default:
//      console.log(elem.parentNode.lastChild.style.visibility);
      break;
  }
  return false;
}
//not used
function getNext(e,elem){
  if(e.keyCode==elem.dataset.nextkeycode){
    add(elem,1);
  }
}
//not used
function getElemNo(elem){
  for(i=0;elem.parentNode.children[i]!=elem;i++){}
  return i--;
}
//not used
function getFieldNoInForm(elem,form){
  e=form.querySelectorAll("[name='"+elem.name+"']");
  for(o=0;o<e.length;o++){
    if(e[o]==elem) return o;
  }
}
//not used
function togglePrevBox(elem){
  if(elem.previousElementSibling.getAttribute('value')=="X"){
    elem.previousElementSibling.setAttribute('value'," ");
    elem.previousElementSibling.value="";
    elem.classList.remove("green");
    elem.classList.add("red");
  }else{
    elem.previousElementSibling.setAttribute('value',"X");
    elem.previousElementSibling.value="X";
    elem.classList.remove("red");
    elem.classList.add("green");
  }
  nam=elem.previousElementSibling.name.split("[")
  elem.previousElementSibling.onclick();
}
function closeSearchList(){
  sel=document.getElementsByClassName("searchlist");
  for(i=0;i<sel.length;i++){
    sel[i].style.visibility="hidden";
  }
}
//not used
function selDetect(){
  if (window.getSelection) {
    return window.getSelection().toString();
  } else if (document.getSelection) {
    return document.getSelection().toString();
  } else {
    var selection = document.selection && document.selection.createRange();
    if (selection.text) {
      return selection.text.toString();
    }
    return false;
  }
  return false;
}
//not used
function getfile(form,type,charset='windows-1251'){
  frm=form.cloneNode(true)
  sel=frm.getElementsByTagName("select");
  for(i=0;i<sel.length;i++){
    if(sel[i].options.length==1){
      sel[i].options[0].setAttribute("selected","true");
    }
  }
  inpt=document.createElement("input");
  inpt.name="type";
  inpt.value=type;
  frm.appendChild(inpt);
  submitForm(frm,'file');
}
//not used
function copySelected(e){
  var a=Array();
  document.getElementById('list').querySelectorAll(".selitem:checked").forEach(function(elem){a.push(elem.value)});
  if(a.length>0){
    str=a.join("\n");
    navigator.clipboard.writeText(str);
    alert("Скопировано "+a.length+" позиции");
  }else{
    alert("Ничего не выбрано");
  }
  e.stopPropagation();
  e.preventDefault();
  return false;
}
//not used
function filter(elem){
  form=document.forms.main;
  form.reset();
  e=new KeyboardEvent('keyup');
  field=form.querySelector('input[name="s-'+elem.className+'"]');
    field.readOnly=false;
  if(field !== null){
    field.value=elem.dataset['value'];
    field.onkeyup(e,field);
  }else{
    field=form.querySelector('input[name="'+elem.className+'"]');
    field.value=elem.dataset['value'];
  }
  setTimeout(submitForm,500,document.forms.main,"list");

}
//not used
function toggleInCell(elem,e){
  e.stopPropagation();
  if(elem.tagName!='TD'){
    parent=elem.parentElement;
  }else{
    parent=elem;
  }
  if(parent.tagName=='TD'){
    el=parent.firstElementChild;
    if(el.disabled==true){
      el.disabled=false;
      parent.lastElementChild.classList.remove('hidden');
    }else{
      el.value=el.dataset.origin;
      el.disabled=true;
      parent.lastElementChild.classList.add('hidden');
    }
  }
}
//clean input and reset select choise... not used
function clearInput(elem){
  if(elem.tagName=='INPUT') elem.value='';
  if(s=elem.parentNode.getElementsByClassName('searchlist')[0]){
    s.selectedIndex=-1;
    s.innerHTML='';
  }
}
//some old staff. may be not needed
function convertWeekNum(wn,y){
  d=new Date(y+'-01-01');
  d.setDate(d.getDate()+wn*7-(d.getDay()-(7-d.getDay())));
  return d.getFullYear()+'-'+(d.getMonth()+1).toString().padStart(2,'0')+'-'+d.getDate().toString().padStart(2,'0');
}
//some old staff. may be not needed
function checkDateStr(elem){
  tr=elem.parentNode.parentNode;
  v=elem.value.split(/\.|\-|\,/);
  wd=tr.querySelector('[name="'+elem.dataset.warrantydate+'"]');
  if(v.length>1){
    if(v[0].length!=4 && v[v.length-1].length==4){ 
      v.reverse();
    }
    if(v.length==2){
      vv=convertWeekNum(v[1],v[0]);
    }
    if(v.length==3){
      vv=v.join('-');
    }
    elem.value=vv;
    if(wd.value==""){
      nn=new Date(vv);
      nnv=new Date(nn.setDate(nn.getDate()+parseInt(elem.dataset.warranty)));
      wd.value=nnv.getFullYear()+'-'+(nnv.getMonth()+1).toString().padStart(2,'0')+'-'+nnv.getDate().toString().padStart(2,'0');
    }
  }
}
//show context menu
function contextmenu(event,elem){
  cleanCommBox();
  event.preventDefault();
  event.stopPropagation();
  switch (elem.nextSibling.style.visibility) {
    case 'visible':
      elem.nextSibling.style.visibility="hidden";
      break;
    case 'hidden':
      elem.nextSibling.style.visibility="visible";
      break;
    default:
      elem.nextSibling.style.visibility="visible";
      break;
    }
  return false;
}
//can't remember what is this for.
function cleanCommBox(){
  elems=document.getElementsByClassName('comment');
  for(i=0;i<elems.length;i++){
    elems[i].style.visibility='hidden';
  }
}
//update issuestatus and close context menu
function setStatus(event,elem){
  statusBox=document.querySelector('#status');
  if(document.querySelector('#ISS_ID')){
    issueId=document.querySelector('#ISS_ID').value;
    get('./status.php?id='+issueId+'&status='+elem.id,null,get,'ticketlist.php');
  }else{
    if(document.querySelector('input[name=STATUS]')){
      inp=document.querySelector('input[name=STATUS]');
    }else{
      inp=document.createElement('input');
      inp.type='hidden';
      inp.name='STATUS';
      elem.parentNode.parentNode.appendChild(inp);
    }
    inp.value=elem.id;
    elem.parentNode.previousElementSibling.innerHTML=elem.innerHTML;
    elem.parentNode.previousElementSibling.classList.remove('s','s120','s121');
    elem.parentNode.previousElementSibling.classList.add('s'+elem.id);
  }
  contextmenu(event,elem.parentNode.previousElementSibling);
}
//show CKEditor
function showEditor(elem){
  if(typeof editor!=='undefined'){
    editor.destroy();
    delete editor;
  }
  editor=CKEDITOR.replace( elem, {
      toolbar:[['Font','FontSize'],['Bold','Italic','Underline'],['NumberedList','BulletedList','-','Blockquote','-','Link'],['Table']], 
    skin : 'kama'
  });
  setTimeout(function(){document.querySelector('#loading').style.display="none"},200);
  elem.parentNode.querySelector('.overlay').style.display='none';
}
//filter ticketlist
function loadlist(event=null,elem=null){
  argstr="?";
  if(typeof a!=="undefined") clearTimeout(a);
  f=true;
  if(event && elem && elem.value.length<elem.dataset.minfstr && elem.value!="") f=false;
  if(f){
    inp=document.querySelectorAll('.listfilter');
    inp.forEach(function(e){
      if(e.value.length>=elem.dataset.minfstr || elem.value!=""){
        argstr+=e.name+'='+e.value+'&';
      }
    });
    a=setTimeout(get,500,'ticketlist.php'+argstr);
  }
}
//close modal with confirmation
function closeModal(){
  ok=true;
  if((document.querySelector('#modal').querySelectorAll('input:not(:disabled):not([value=""]):not(.listfilter)').length>0 && document.querySelectorAll('.section:has(.cke_browser_webkit)').length > 0) || document.querySelector('#submit').disabled==false){
    ok=confirm("Введенные данные не сохранятся. Закрыть?");
  }
  if(ok){
    document.querySelector('#modal').innerHTML='';
  }
}
//add audio tag from recordlist
function chooseRecord(elem){
  if(elem.value && !document.querySelector('#record'+elem.value.split('.')[0])){
    div=document.createElement('div');
    div.id='record'+elem.value.split('.')[0];
    inp=document.createElement('input');
    inp.type='hidden';
    inp.name='CHANNELID[]';
    inp.value=elem.value;
    div.appendChild(inp);
    audio=document.createElement('audio');
    audio.controls=true;
    audio.src='https://fsprec.starline.ru:8000/'+elem.options[elem.options.selectedIndex].dataset.file+'.mp3';
    audio.type='audio/mpeg';
    source=document.createElement('source');
    source.type= 'audio/mpeg';
    source.src='https://fsprec.starline.ru:8000/'+elem.options[elem.options.selectedIndex].dataset.file+'.mp3';
    audio.appendChild(source);
    audio.load();
    div.appendChild(audio);
    img=document.createElement('img');
    img.src='/images/del.png';
    img.classList='pic';
    img.setAttribute('onclick',"document.querySelector('#recordlist').removeChild(this.parentNode)");
    div.appendChild(img);
    document.querySelector('#recordlist').appendChild(div);
    if(document.querySelector('#ISS_ID')){
      document.querySelector('#ISS_ID').removeAttribute('readonly');
      document.querySelector('#ISS_ID').removeAttribute('disabled');
    }
    document.querySelector('#submit').removeAttribute('disabled');
  }
  elem.selectedIndex=0;
}
//position for hint
function getPosition(e){
  var x = y = 0;
  if(!e){
    var e = window.event;
  }
  if (e.pageX || e.pageY){
    x = e.pageX;
    y = e.pageY;
  } else if (e.clientX || e.clientY){
    x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
    y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
  }
  coord={x: x, y: y}
}
function showHint(text,tout=1500){
  hint=document.querySelector('#hint');
  hint.style.top=coord.y+'px';
  hint.style.left=coord.x+'px';
  hint.innerHTML=text;
  hint.style.filter='opacity(1)';
  setTimeout(function(hint){ hint.style.filter='opacity(0)'; },tout,hint);
}
//update ticketlist on main page
function getList(){
  get("./ticketlist.php");
}
//assign issue to user
function setAssignee(elem,e){
//  console.log(e);
  if(e.keyCode==13 || e.type=='click'){
   if (elem.value!="" && document.querySelector('#ISS_ID') && document.querySelector('#ISS_ID').value!='') get("./assign.php?uname="+elem.value+"&iss_id="+document.querySelector('#ISS_ID').value,elem);
  }else{
    return false;
  }
}
//update input from select list
function setChoosenOption(sel,inp=sel.parentNode.firstChild){
  inp.value=sel.options[sel.selectedIndex].innerHTML;
  inp.classList.add('ok');
}