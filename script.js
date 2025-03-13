var clock;
var clockflag=false;
var members;
var anscalls;
var called;
var lost;
var callers;
var missed;
var blacklist;
var calcpause;
var callers;
var ws;
var tOutIds=new Array();
var tOutIds1=new Array();
var Queues=new Map([['sl-group-1','Приложение'],['sl-group-2','Пользователь'],['sl-group-4','Пользователь'],['sl-group-3','Установщик'],['sl-group-5','Установщик'],['sl-group-all','Всем']]);
var Statuses=new Map([["0","Idle"],["1","InUse"],["2","Busy"],["4","Unavailable"],["8","Ringing"],["16","OnHold"]]);
var WS=false;
NotifGranted=false;
var debug=false;

function clearTouts(arr){
  arr = arr || tOutIds;
//  console.log(arr);
  for(i in arr){
    clearTimeout(arr[i]);
//    console.log(arr[i]);
  }
}

//function dnd(reason,tOutId){
//  tOutId=tOutId || tOutIds['calcpause'];
//  clearTimeout(tOutId);
//  loadExternalContent("./dnd2.php?reason="+reason,document.getElementById('dnd'));
//}

function notification(text){
//  clearInterval(calcpause);
//  var calcpause = setInterval("loadContent('calcpause')",5000);
//  clearTimeout(tOutIds['calcpause']);
  document.getElementById('dnd').innerHTML=text;
  setTimeout("document.getElementById('dnd').innerHTML=''",5000);
}
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
function send(url,data,target){
  terminateFunc=false;
  var req=getreq();   
  req.onreadystatechange=function(){ loadExternalContentDone(req,target) };
  req.open("POST", url, true);
  if(target=="_blank"){
    req.responseType = 'blob';
  }
  req.send(data);
  delete data;   
}
function get(url,target){
  terminateFunc=false;
  var req=getreq();   
  req.onreadystatechange=function(){ loadExternalContentDone(req,target) };
  req.open("GET", url, true);
  if(target=="_blank"||target=="file"){
    req.responseType = 'blob';
  }
  req.send();
}
                                                          
function search(e,elem,flag=null){
  if(debug) console.log(e.keyCode);
  if(elem.value.length<3) return;
  elem.classList.remove('ok');
  sel=elem.parentNode.querySelector("select");
  sel.selectedIndex=-1;
  sel.value='';
  opts=sel.options;
  a=0;
  if(e.keyCode!=39 && e.keyCode!=37){
    if(typeof elem.dataset.dir !== 'undefined'){
      get(elem.dataset.dir+'.php?str='+elem.value,sel,null);
    }else{
//    if(opts.length>0){
      for(i=0;i<opts.length;i++){
        if(opts[i].innerText.indexOf(elem.value)==-1){
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
        choose(sel,elem);
      }
    }else if(a==opts.length){
      hide(sel);
    }else{
      sel.style.visibility='visible';
      if(e.keyCode==40){
        sel.focus();
        sel.selectedIndex=dopts[0].index;
      }else if(e.keyCode==38 & sel.selectedIndex==0){
        sel.selectedIndex=-1;
        sel.value="";
      }
    }
  }
}
function choose(sel,inp=sel.parentNode.firstChild){
  if(sel.selectedIndex!=-1){
    inp.value=sel.options[sel.selectedIndex].innerHTML;
    inp.classList.add('ok');
    hide(sel);
    if(typeof sel.dataset.next!=='undefined') load_ref(sel.value,sel.dataset.next);
    inp.setAttribute("readonly","true");
  }
}
                                                                                                                                                                                                                                              
function loadContent(id,restartDelay){
  restartDelay = restartDelay || 0;
  restartDelay=0;
  url='./'+id+'.php';
  target=document.getElementById(id);
  loadExternalContent(url,target,restartDelay);
}

function parseJson(json){
  if(json['action']=="time"){
    o_time=new Date(json['time']);
    c_time=new Date();
    console.log(c_time-o_time+'ms');
    if(o_time-c_time>3000 || c_time-o_time>5000){
      alert('Не правильное время на компьютере. Синхронизируйте. При повторении ошибки обратитесь к администратору ресурса. ('+(c_time-o_time)+'ms)');
    }
  }else if(json['action']=="mark"){
//    console.log(json);
    if(json['data']!==false){
      console.log('AGENT: '+json['data']['AGENT']+', MARK: '+json['data']['MARK']);
      i=document.createElement('img');
      i.classList='star';
//      i.src='./images/star.png';
      member=document.querySelector('#member'+json['data']['AGENT']);
      div=member.querySelector('.stars');
      div.appendChild(i);
      setTimeout(Scale,100,i,1);
    }
  }
}
function loadExternalContentDone(req,target,restartDelay=0) {
  if (req.readyState == 4) {
    if (req.status == 200) {   
      if(req.responseText=='redirecting'){
//        alert('session timeout');
        clearTouts(tOutIds);
        setTimeout(function(){window.location = window.location.href},2000);
      }
      content=req.getResponseHeader('content-type');
      if(req.response != "" && req.response.type=="application/json" && req.responseType=="blob"){
        req.response.text().then( function (data){
          try{
            json=JSON.parse(data);
            parseJson(json,target);
          }catch(e){
            json="";
          }
        });
      }else if(content && content.indexOf("application/json")!== -1 && req.response != ""){
        json=JSON.parse(req.response);
        parseJson(json,target);
      }else{
        json="";
      }
      if(json==""){
        if(target!=''){
          target.innerHTML=req.responseText;
        }else{
          notification(req.responseText);
        }
        if(restartDelay!=0){
          if(target.id=='blacklist' || target.id=='missed'){
            tOutIds1[target.id]=setTimeout("loadContent('"+target.id+"','"+restartDelay+"')",restartDelay)
          }else{
           tOutIds[target.id]=setTimeout("loadContent('"+target.id+"','"+restartDelay+"')",restartDelay);
//         console.log(target.id+" "+id);
//         tOutIds[target.id]=id;
          }
        }
      }
      req=null;
    }else{
      if(target==''){
        target.innerHTML='<img style="width:1em;height:1em;" src="./loading.gif">';
      }
    }
  }else{
    if(target==''){
      target.innerHTML='<img style="width:1em;height:1em;" src="./loading.gif">';
    }
  }
}

function loadExternalContent(url,target,restartDelay) {
  var req=null;
  restartDelay = restartDelay || 0;
  if (window.XMLHttpRequest) {
    req = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    req = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (req !== undefined) {
    req.onreadystatechange = function() {loadExternalContentDone(req,target,restartDelay);};
    req.open("GET", url, true);
    req.send("");
  }
}

function postformDone(req,target) {
  if (req.readyState == 4) {
    if (req.status == 200) {
      if(target!=''){
        target.innerHTML=req.responseText;
      }else{
        new Notification(req.responseText);
//        notification(req.responseText);
      }
      req=null;
    }
  }  
}    

function postform(url,form,target) {
  var req;
  inputs=form.elements;
  var data = new FormData();
  for (i=0;i<inputs.length;i++){   
    if (inputs[i].type=='file'){   
      inputdata=inputs[i].files[0];
    }else if(inputs[i].type=='checkbox'){
      inputdata=inputs[i].checked;
    }else{
      inputdata=inputs[i].value;
    }
    data.append(inputs[i].name,inputdata);
  } 
  if (window.XMLHttpRequest) { 
    req = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    req = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (req !== undefined) {
    req.onreadystatechange = function() {postformDone(req,target);};
    req.open("POST", url, true);
    req.send(data);
  }
}

function loadReasonDone(req,setStatus=true){
  if (req.readyState == 4) {
    if (req.status == 200) {
      if(req.responseText=='redirecting'){
        clearTouts(tOutIds);
        setTimeout(function(){window.location = window.location.href},2000);
      }
      content=req.getResponseHeader('content-type');
      if(content && content.indexOf("application/json")!== -1 && req.response != ""){
        json=JSON.parse(req.response);
//        parseJsonLog(json,target);
//        console.log("Loaded reason "+json['REASON']+" for "+json['PHONE_NUMBER']+" "+json['PAUSE']);
        if(document.querySelector("#member"+json['PHONE_NUMBER'])!==null){
          document.querySelector("#member"+json['PHONE_NUMBER']+'> .statusimg').src='./images/'+json['REASON']+'.png';
          if(document.querySelector("#member"+json['PHONE_NUMBER'])!==null){ 
            document.querySelector("#member"+json['PHONE_NUMBER']).classList.remove('workout','postcall','busy','dinner','break','paused','recall');
            if(document.querySelector("#member"+json['PHONE_NUMBER']+"~.active_per")!==null) document.querySelector("#member"+json['PHONE_NUMBER']+"~.active_per").classList.remove('active_per');
          }
          if(json['PAUSE']==1){
            if(document.querySelector("#member"+json['PHONE_NUMBER'])!==null) document.querySelector("#member"+json['PHONE_NUMBER']).classList.add('paused',json['REASON']);
            if(document.querySelector("#"+json['REASON']+json['PHONE_NUMBER'])!==null) document.querySelector("#"+json['REASON']+json['PHONE_NUMBER']).classList.add('active_per');
          }
          if(setStatus){
//            console.log("Setting status "+Statuses.get(json['STATUS'].toString())+"("+json['STATUS']+") for "+json['PHONE_NUMBER']);
            if(document.querySelector("#member"+json['PHONE_NUMBER'])!==null){
              document.querySelector("#member"+json['PHONE_NUMBER']).classList.remove('Up','InUse', 'inuse', 'Busy', 'busy', 'Ringing', 'Ring', 'ringing', 'ring', 'Idle', 'idle', 'Online', 'online','orange', 'undefined', 'OnHold', 'Unavailable', 'unavail');
              document.querySelector("#member"+json['PHONE_NUMBER']).classList.add(Statuses.get(json['STATUS'].toString()));
            }
          }
        }
      }
    }
  }
}

function loadReason(member, setStatus){
  if (window.XMLHttpRequest) {
    req = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    req = new ActiveXObject("Microsoft.XMLHTTP");
  }
  if (req !== undefined) {
    req.response.type=="application/json";
    req.onreadystatechange = function() {loadReasonDone(req,setStatus);};
    req.open("GET", './reason.php?num='+member, true);
    req.send("");
  }
}

function call(phonenum){
  document.getElementById('phonenumber').value=phonenum;
  if(confirm('Позвонить')){postform('./call.php',document.getElementById('call'),'')};
}

function add2bl(){
  num=document.getElementById('bl_phonenum');
  reason=document.getElementById('bl_reason');
  if(num.value==""){
    num.style.border="1px solid red";
    return;
  }
  if(reason.value==""){
    reason.style.border="1px solid red";
    return;
  }
//  loadExternalContent('http://10.1.10.235:8088/asterisk/rawman?action=login&username=www&secret=wwwgfhjkm','');
//  loadExternalContent('http://10.1.10.235:8088/asterisk/rawman?action=DBPut&family=blacklist&key='+num.value+'&val='+reason.value,'');
  loadExternalContent('./bl.php?action=put&phonenum='+num.value+'&reason='+reason.value,'');
  loadContent('blacklist');
}

function delFromBl(num){
  loadExternalContent('./bl.php?action=del&phonenum='+num,'');
  loadContent('blacklist');
}

function calcTimer(member){
  if(member.dataset.ts!==null){
    fullt = new Date();
    ts=new Date(member.dataset.ts);
    diff=fullt - ts;
    diff=(diff-(milliseconds=diff%1000))/1000;
    diff=(diff-(seconds=diff%60))/60;
    diff=(diff-(minutes=diff%60))/60;
    days=(diff-(hours=diff%24))/24;
    member.innerHTML=String(hours).padStart(2,'0')+":"+String(minutes).padStart(2,'0')+":"+String(seconds).padStart(2,'0');
  }
}

function updateClock() {
  if(clockflag) return;
  clockflag=true;
  var datedelim='/';
  var timedelim=':';
  var fulltime = new Date();
  var time=('00'+fulltime.getDate()).slice(-2)+datedelim+('00'+(parseInt(fulltime.getMonth())+1)).slice(-2)+datedelim+fulltime.getFullYear()+'  '+('00'+fulltime.getHours()).slice(-2)+timedelim+('00'+fulltime.getMinutes()).slice(-2)+timedelim+('00'+fulltime.getSeconds()).slice(-2);
  document.getElementById('clock').innerHTML=time;
  if(WS){
    document.querySelectorAll('.last_event').forEach(calcTimer);
    document.querySelectorAll('.active_per').forEach(iterateTime);
  }
  clockflag=false;
}

function DND(reason){
    if(confirm("Вы хотите включить/выключить паузу("+reason+")")){
      loadExternalContent("./dnd2.php?reason="+reason);
    }
}
   
function onLoad(){
  setInterval("updateClock()",1000);
  if(document.getElementById('members')){ loadContent('members',10000); }
  if(document.getElementById('anscalls')){ loadContent('anscalls',15000); }
  if(document.getElementById('called')){ loadContent('called',15000); }
  if(document.getElementById('lost')){ loadContent('lost',15000); }
  if(document.getElementById('missed')){ loadContent('missed',120000); }
  if(document.getElementById('blacklist')){ loadContent('blacklist',120000); }
  if(document.getElementById('calcpause')){ loadContent('calcpause',10000); }
  if(document.getElementById('callers')){ loadContent('callers',12000); }
  if(document.getElementById('callerslist')){ loadContent('callerslist',1000);  }

  if (!("Notification" in window)) {
    // Check if the browser supports notifications
    console.warn("This browser does not support desktop notification");
  }else if (Notification.permission !== "denied"){
    // We need to ask the user for permission
    Notification.requestPermission().then((permission) => {
    // If the user accepts, let's create a notification
      if (permission === "granted") { NotifGranted=true; }else{ NotifGranted=false; }
    });
    
  }
  startWS();
  window.onbeforeunload = function () {
    clearTouts();
  }
  lastnumber=getCookie("lastnumber");
  lastchannelid=getCookie("lastchannelid")
  if(lastnumber != '' && lastchannelid != '' && typeof lastnumber != 'undefined' && typeof lastchannelid != 'undefined' && confirm("Последний звонок с номера "+lastnumber+". Открыть карточку клиента?")){
    loadExternalContent("./ticketlist.php?client="+lastnumber+"&channelid="+lastchannelid,modal);
  } else {
    deleteCookie("lastnumber");
    deleteCookie("lastchannelid");
  }
  get('./time.php','',null);
  
}
function setCookie(name, value) {
  options = {
    path: '/',
  }
  if (options.expires instanceof Date) {
    options.expires = options.expires.toUTCString();
  }
  let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
  for (let optionKey in options) {
    updatedCookie += "; " + optionKey;
    let optionValue = options[optionKey];
    if (optionValue !== true) {
      updatedCookie += "=" + optionValue;
    }
  }
  document.cookie = updatedCookie;                         
}

function getCookie(name) {
  let matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
  return matches ? decodeURIComponent(matches[1]) : undefined;
}

function deleteCookie(name) {
  setCookie(name, "", {
    'max-age': -1
  })
}

function cleanMenus(){
  elems=document.getElementsByClassName('menu');
  for(i=0;i<elems.length;i++){
    elems[i].innerHTML='';
  } 
}

function cleanCommBox(){
  elems=document.getElementsByClassName('comment');
  for(i=0;i<elems.length;i++){
    elems[i].style.visibility='hidden';
  }
}

//setTimeout("onLoad()",2000);
function Remove(elem){
  elem.remove();
}

function Scale(elem,n){
  elem.style.scale=n;
}

function Append(parent,elem){
  parent.appendChild(elem);
  setTimeout(Scale,100,elem,1);
}
function InsertAfter(nextTo,elem){
  nextTo.parentNode.insertBefore(elem,nextTo);
  setTimeout(Scale,100,elem,1);
}

function parseWS(event){
  data=JSON.parse(event.data);
  if(data.type=="ChannelDestroyed"){
    if(debug) console.log(data.timestamp+" "+data.type+": "+data.channel.caller.number+" - "+data.channel.connected.number);
    if(data.channel.caller.number.length > 8 && document.querySelector('#cli'+data.channel.caller.number) !== null && data.channel.state!='Ringing'){
      document.querySelector('#cli'+data.channel.caller.number).style.scale=0;
      setTimeout(Remove,300,document.querySelector('#cli'+data.channel.caller.number));
      if(data.channel.connected.number.length==4 && data.channel.state=='Up') get('./mark.php?channelid='+data.channel.id);
    }
    if(data.channel.caller.number!==null &&  document.querySelector('#member'+data.channel.caller.number)!==null && data.channel.caller.number!=='s'){
      changeMemberStatus(member=data.channel.caller.number,status=null,needReason=true);
      document.querySelector('#event'+data.channel.caller.number).dataset.ts=data.timestamp;
    }
    if(data.channel.connected.number!==null &&  document.querySelector('#member'+data.channel.connected.number)!==null && data.channel.connected.number!=='s'){
      changeMemberStatus(member=data.channel.connected.number,status=null,needReason=true);
      document.querySelector('#event'+data.channel.connected.number).dataset.ts=data.timestamp;
      if(document.querySelector('#event'+data.channel.caller.number)!==null) document.querySelector('#event'+data.channel.caller.number).dataset.ts=data.timestamp;
    }else{
      if(data.channel.caller.number!==null && document.querySelector('#cli'+data.channel.caller.number)){
        document.querySelector('#cli'+data.channel.caller.number).style.scale=0;
//        setTimeout(InsertAfter,300,document.querySelector('#missed>div:first-child').nextElementSibling,document.querySelector('#cli'+data.channel.caller.number));
//      setTimeout(Remove,300,document.querySelector('#cli'+data.channel.caller.number));
      }
    }
  }else if(data.type=="ChannelConnectedLine"){
    if(debug) console.log(data.timestamp+" "+data.type+": "+data.channel.caller.number+" - "+data.channel.connected.number);  
    if(document.querySelector('#cli'+data.channel.connected.number) !== null && data.channel.state!='Ringing'){
      document.querySelector('#cli'+data.channel.connected.number).style.scale=0;
      setTimeout(Remove,300,document.querySelector('#cli'+data.channel.connected.number));
    }
  }else if(data.type=="PeerStatusChange"){
    if(debug) console.log(data.timestamp+" "+data.type+": "+data.endpoint.resource+" - "+data.endpoint.state);
    if(document.querySelector('#member'+data.endpoint.resource) !== null ){
      div=document.querySelector('#member'+data.endpoint.resource);
      document.querySelector('#event'+data.endpoint.resource).dataset.ts=data.timestamp;
      if(data.endpoint.state=='Registered'){
        div.classList.remove('unavail');
      }
      if( typeof data.channel !== 'undefined' && typeof data.channel.caller !== 'undefied') document.querySelector('#event'+data.channel.caller.number).lastElementChild.dataset.ts=data.timestamp;
    }
  }else if(data.type=="ChannelStateChange"){
    if(debug) console.log(data.timestamp+" "+data.type+": "+data.channel.state+" - "+data.channel.caller.number);
    if(data.channel.caller.number!=='s' && document.querySelector('#member'+data.channel.caller.number)!==null){
      changeMemberStatus(data.channel.caller.number,data.channel.state,false);
      if(NotifGranted==true && data.channel.caller.number==self_phone){
        if(data.channel.state=="Ringing"){
          if(typeof n!=="undefined") n.close();
          n=new Notification("Входящий звонок с номера "+data.channel.connected.number);
        }else if(data.channel.state=="Up"){
          if(typeof n!=="undefined") n.close();
        }
      }
      document.querySelector('#event'+data.channel.caller.number).dataset.ts=data.timestamp;
    }
  }else if(data.type=='ChannelDialplan'){
    if(data.dialplan_app=='Queue'){
      queue=data.dialplan_app_data.split(',')[0];
      if(document.querySelector('#cli'+data.channel.caller.number)==null){
        if(debug) console.log(data.timestamp+' '+queue+': append '+data.channel.caller.number);
        cli=document.createElement('div');
        cli.classList.add('caller');
        cli.setAttribute('id','cli'+data.channel.caller.number);
        cli.style.scale=0;
        cli.innerHTML='<span class=queue>'+Queues.get(queue)+'</span><span>'+data.channel.caller.number+'</span><span id="event'+data.channel.caller.number+'" class=last_event data-ts="'+data.timestamp+'"></span></div>';
        document.querySelector('#callerslist').appendChild(cli);
        setTimeout(Scale,100,cli,1);
      }else{
        document.querySelector('#cli'+data.channel.caller.number).firstElementChild.innerHTML=Queues.get(queue);
      }
    }
  }else if(data.type=='DeviceStateChanged'){
    state=data.device_state.name.split('_');
    if(state.length==3 && state[0]=='Queue:sl-group-all' && state[1]=='pause'){
      mem=state[2].split('/')[1];
      if(document.querySelector('#member'+mem)){
         changeMemberStatus(mem,null,true);
      }
    }else if(state.length==1){
      mem0=state[0].split('/')[1];
      if(typeof mem0 !== 'undefined'){
        mem=mem0.split('@')[0];
        if(document.querySelector('#member'+mem)){
          document.querySelector('#member'+mem).classList.remove('paused');
          document.querySelector('#member'+mem).classList.remove('orange');
          if(typeof mem !== undefined) loadReason(mem);
        }
      }
    }
  }else if(data.type=='ChannelLeftBridge'){
    if(document.querySelector('#member'+data.channel.connected.number) !== null){
      if(data.channel.connected.number.length==4){
        document.querySelector('#event'+data.channel.connected.number).dataset.ts=data.timestamp;
        changeMemberStatus(data.channel.caller.number,null,true);
      }
    } 
  }else if(data.type=='ChannelEnteredBridge'){
    if(debug) console.log(data.timestamp+" "+data.type+": "+data.channel.state+" - "+data.channel.caller.number+" - "+data.channel.connected.number);
    if(document.querySelector('#cli'+data.channel.caller.number) !== null){
      if(data.channel.caller.number.length>8){
        cli=document.querySelector('#cli'+data.channel.caller.number);
        cli.style.scale=0;
        document.querySelector('#event'+data.channel.caller.number).dataset.ts=data.timestamp;
        setTimeout(Append,300,document.querySelector('#member'+data.channel.connected.number),cli);
      }
    }
    if(data.channel.caller.number.length > data.channel.connected.number.length && data.channel.connected.number==self_phone){
      if(typeof n!=="undefined") n.close();
      if(modal.innerHTML=="" || confirm("Вы действительно хотите открыть новый список? Введенные данные не будут сохранены.")){
        loadExternalContent("./ticketlist.php?client="+data.channel.caller.number+"&channelid="+data.channel.id,modal);
        document.cookie='lastnumber='+data.channel.caller.number;
        document.cookie='lastchannelid='+data.channel.id;
      }
    }
  }else if(data.type=='ApplicationReplaced'){
    console.warn(data.timestamp+' '+data.type+'. Websocket connection closed. Restarting.');
    window.location.reload();
  }
}
function changeMemberStatus(member,status=null,needReason=true){
  if(document.querySelector('#member'+member)){
    div=document.querySelector('#member'+member);
    div.classList.remove('Up','InUse', 'inuse', 'Busy', 'busy', 'Ringing', 'Ring', 'ringing', 'ring', 'Idle', 'idle', 'Online', 'online','orange', 'undefined', 'OnHold', 'Unavailable', 'unavail');
    if(status == null || status == ''){
      setStatus=true;
    }else{
      setStatus=false;
//      console.log("Setting status of member "+member+" to status "+status+". needReason is "+needReason);
      div.classList.add(status);
    }
    if(needReason) loadReason(member,setStatus)
  }
}
function startWS(){
  if(WS==false){
    WS=true;
    clearTouts();
    ws=new WebSocket("wss://fsprec.starline.ru:9088");
//    ws=new WebSocket("wss://fsprec.starline.ru:8089/ari/events?app=hello-world&subscribeAll=true&api_key=jira:jirasecret");/* fsp:gfhjkm");*/
    ws.onopen=(event)=>{console.log("Websocket connected");}
    ws.onclose=(event)=>{
      console.warn("Websocket connection closed "+event.code+" "+event.reason);
      setTimeout(startWS,4000);
    }
    ws.onerror=(event)=>{console.error("Websocket connection error "+event.code+" "+event.reason);}
    ws.onmessage=parseWS;
//    console.log("Switching to realtime mode");
//    document.querySelector('#beta>button').classList.add('active');
  }
}

function iterateTime(elem){
  time=elem.lastElementChild.innerHTML.split(':')
  d = new Date();
  d.setTime(d.setHours(time[0],time[1],time[2])+1000);
  elem.lastElementChild.innerHTML=d.toLocaleTimeString();
}

function clientList(){
  return;
}
function createTicket(id=null,channelid=null){
  args='action=create';
  if(id!=null) args+='&id='+id;
  if(channelid!=null) args+='&channelid='+channelid;
  if(document.querySelector("input[name=CLIphone]") && document.querySelector("input[name=CLIphone]").value!=''){ 
    args+='&client='+document.querySelector("input[name=CLIphone]").value;
  }else if(document.querySelector("input[name=CLIid]") && document.querySelector("input[name=CLIid]").value!=''){
      args+="&cliid="+document.querySelector("input[name=CLIid]").value;
  }
  window.open('tickets/index.php?'+args);
}
function checkTime(){
  get('./time.php');
}
function star(mark,agent){
  
}