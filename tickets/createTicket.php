<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<head>
<script type="text/javascript" src="./script.js"></script>
  <link href="https://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css" rel="stylesheet">
  <link rel="stylesheet" href="./medium/dist/css/medium-editor.css">
  <link rel="stylesheet" href="./medium/dist/css/themes/default.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-core.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-classapplier.min.js"></script>
             
<link rel="stylesheet" href="style.css">
<script type="text/javascript">
  function search(e,elem,flag=null){
    console.log(e.keyCode);
    if(elem.value.length<1) return;
    elem.classList.remove('ok');
    sel=elem.parentNode.querySelector("select");
    sel.selectedIndex=-1;
    sel.value='';
    opts=sel.options;
    a=0;
    if(e.keyCode!=39 && e.keyCode!=37){
      for(i=0;i<opts.length;i++){
        if(opts[i].innerText.indexOf(elem.value)==-1){ 
          opts[i].disabled=true;
        }else{
          a++;
          opts[i].disabled=false;
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
  function choose(sel,inp){
    inp.value=sel.options[sel.selectedIndex].innerHTML;
    inp.classList.add('ok');
    hide(sel);
    if(typeof sel.dataset.next!=='undefined') load_ref(sel.value,sel.dataset.next);
    inp.setAttribute("readonly","true");
  }
  function selKey(e,elem){
    opts=elem.parentNode.querySelectorAll("select > option:not(:disabled)");
    input=elem.parentNode.querySelector('input');
    if(e.keyCode==13){
      input.value=elem.options[elem.selectedIndex].innerHTML;
      choose(elem,input);
    }else if(e.keyCode==38 & sel.selectedIndex==opts[0].index){
      sel.selectedIndex=-1;
      sel.value="";
      input.focus();
      input.classList.remove('ok');
    }
  }  
  
  function load_ref(id,target){
    tgt=document.querySelector('select[name="DETALE['+target+']"]');
    get('./ref.php?id='+id,tgt);
  }
  
  function loadbrand(id){
    get("./car_model.php?brand_id="+id,document.querySelector("#car_model"),null);
    document.querySelector("#allbrands").classList.add("hide");
  }
  
  function clickInput(elem){
    input=elem.querySelector('input');
    input.removeAttribute("readonly");
    input.select();
    input.classList.remove('ok');
  }
  
  function selectCar(elem){
    elem.parentNode.querySelectorAll('.ok').forEach(function(t){t.classList.remove('ok')})
    elem.classList.add('ok');
    document.querySelector('input[name="CAR"]').value=elem.dataset.id;
    elem.parentNode.querySelectorAll('.car_model').forEach(function(t){ if(t!=elem) t.parentNode.removeChild(t) });
  }
</script>
<?php
include('../auth2.php');
if($_SERVER['REQUEST_METHOD']=="POST"){
  var_dump($_POST);
  $stmt=oci_parse($conn,"insert into pbx.issues(iss_id, client_id, iss_date, creator) values(pbx.ISSUES_SEQ.NEXTVAL, :client_id, sysdate, :username) return iss_id into :issue_id");
  oci_bind_by_name($stmt,":client_id",$_POST['CLIENT_ID']);
  oci_bind_by_name($stmt,":username",$_SESSION['uid']);
  oci_bind_by_name($stmt,":issue_id",$iss_id,-1,SQLT_INT);
  oci_execute($stmt);
  echo "iss=".$iss_id;
  if($iss_id){
    $stmt1=oci_parse($conn,"insert into pbx.issue_detale(iss_id,detale_id) values(:iss_id,:detale_id)");
    oci_bind_by_name($stmt1,":iss_id",$iss_id);
    foreach($_POST['DETALE'] as $detale_id){
      oci_bind_by_name($stmt1,":detale_id",$detale_id);
      oci_execute($stmt1);
    }
    $stmt2=oci_parse($conn,"select type_id, name from pbx.types where class_id=60");
    oci_execute($stmt2);
    $stmt3=oci_parse($conn,"insert into pbx.iss_prop values(:issue_id,:type_id,:value,sysdate)");
    oci_bind_by_name($stmt3,":issue_id",$iss_id);
    while($row=oci_fetch_array($stmt2,OCI_ASSOC+OCI_RETURN_NULLS)){
//      echo $row['NAME']." ".$_POST[$row['NAME']];
      oci_bind_by_name($stmt3,":type_id",$row['TYPE_ID']);
      oci_bind_by_name($stmt3,":value",$_POST[$row['NAME']]);
      oci_execute($stmt3);
    }
  }
  
  exit;
}
$_GET['client']='9817133264';
echo "</head>";
echo "<body>";
echo "<div id=notifbox></div>";
echo "<div id=loading style='display:none'></div>";
echo "<form action='createTicket.php' method=post>";
echo "<div id=ticket>";
echo "<div>Создание заявки</div>";
echo "<div class=section><span class=label>Клиент</span>";
echo "<table id=ticketHeader>";
$sql="select c.client_id, c.name, cp.phone_num from PBX.CLI_PHONES cp, PBX.CLIENTS c, pbx.types t where cp.CLIENT_ID=c.CLIENT_ID and t.type_id=c.TYPE_ID and cp.phone_num=:phone";
$stmt=oci_parse($conn,$sql);
oci_bind_by_name($stmt,':phone',$_GET['client']);
oci_execute($stmt);
$row=oci_fetch_array($stmt,OCI_ASSOC);
echo "<tr><td>Имя</td><td><input type=hidden name=CLIENT_ID value=".$row['CLIENT_ID']."><input type=text name=NAME disabled=true value='".$row['NAME']."'></td></tr>
<tr><td>Тип клиента</td><td><input type=text name=cliTYPE disabled=true value='".$row['CITY']."'></td></tr>
<tr><td>Номер телефона</td><td>".$row['PHONE_NUM']."</td></tr>
<tr><td>Город</td><td><input type=text name=s_CITY onkeyup='search(event,this,true)' onclick='this.removeAttribute(\'readonly\');this.style.border=\'\';this.select();'><br>
<select class=searchlist size=5 name=CITY onkeyup='selKey(event,this)' onclick='this.style.visibility=\'hidden\'' onfocusout='choose(this);hide(this);'>";

echo "</select></td></tr></table></div>";
echo "<div class=section><div id=cars>";
echo "<span class=label>Автомобиль</span>";
include("can.php");
$stmt1=$can->prepare("select id,name,logo_link,is_popular from brand order by is_popular desc, name asc");
$stmt1->execute();
$stmt1->bind_result($brand_id,$brand_name,$brand_logo,$is_popular);
$allbrands="";
echo "<div>Популярные бренды</div>";
while($stmt1->fetch()){
  if($brand_logo != NULL && $is_popular!=0){
    echo "<img data-id=".$brand_id." class=brand_logo src=https://can.starline.ru/data/logos/".$brand_logo." onclick=loadbrand(this.dataset.id)>";
  }else{
    $allbrands.="<div data-id=".$brand_id." class=brand_name onclick=loadbrand(this.dataset.id)>".$brand_name."</div>";
  }
}
echo "<div onclick=this.nextElementSibling.classList.toggle('hide')>Все бренды</div>";
echo "<div id=allbrands class=hide>".$allbrands."</div>";
echo "</div><div id=car_model></div>
<input type=hidden name=CAR>";
echo "<table><tr><td>Тип КПП</td><td><input type=radio name=TRANSMISSION value=None>Нет <input type=radio name=TRANSMISSION value=MT>Механическая <input type=radio name=TRANSMISSION value=AT>Автоматическая";
echo "</td></tr></table>";
echo "</div><div class=section><span class=label>Заявка</span>";
$stmt2=oci_parse($conn,"SELECT detale_id,name FROM pbx.detale WHERE TYPE_id=22 order by name asc");
oci_execute($stmt2);
$stmt3=oci_parse($conn,"select detale_id,name from pbx.detale where type_id=43 order by detale_id asc");
oci_execute($stmt3);
echo '<table id=ticketDetails>
<tr><td>Тема</td><td><input type=text name=SUBJECT size=96></td></tr>
<tr><td>Тип обращения</td><td><div class=input onclick=clickInput(this)><input type=text name=s_TYPE size=32 onkeyup="search(event,this,true)" onclick="this.removeAttribute(\'readonly\');this.style.border=\'\';this.select();"><br>
<select class=searchlist size=5 name="DETALE[TYPE]" onkeyup="selKey(event,this)" onchange="" onclick="this.style.visibility=\'hidden\'" onfocusout="choose(this);hide(this);">';
while($row=oci_fetch_array($stmt3,OCI_ASSOC+OCI_RETURN_NULLS)){
  echo "<option value=".$row['DETALE_ID'].">".$row['NAME']."</option>";
  }
echo '</select></div></td></tr>
<tr><td>Модель оборудования</td><td><div class=input onclick=clickInput(this)><input type=text name=s_MODEL size=32 onkeyup="search(event,this,true)" onclick="this.removeAttribute(\'readonly\');this.style.border=\'\';this.select();" autocomplete=off><br>
<select class=searchlist size=5 name=DETALE[MODEL] data-next=REASON onkeyup="selKey(event,this)" onchange="" onclick="this.style.visibility=\'hidden\'" onfocusout="choose(this);hide(this);">';
while($row=oci_fetch_array($stmt2,OCI_ASSOC+OCI_RETURN_NULLS)){
  echo "<option value=".$row['DETALE_ID'].">".$row['NAME']."</option>";
}
echo '</select></div></td></tr>
<tr><td>Причина обращения</td><td><div class=input onclick=clickInput(this)><input type=text name=s_REASON size=32 onkeyup="search(event,this,true)" onclick="this.removeAttribute(\'readonly\');this.style.border=\'\';this.select();"><br>
<select class=searchlist size=5 name=DETALE[REASON] onkeyup="selKey(event,this)" onchange="" onclick="this.style.visibility=\'hidden\'" onfocusout="choose(this);hide(this);">';
echo '</select></div></td>
</td></tr>
<tr><td>Логин</td><td><input type=text name=LOGIN></td></tr>
<tr><td></td><td><textarea class=editable style="width:60em;height:20em" name=BODY></textarea></td></tr>
</table>';
echo "</div></div>";
echo "<script src='./medium/dist/js/medium-editor.js'></script>";
echo "<script>
rangy.init();

        var HighlighterButton = MediumEditor.extensions.button.extend({
            name: 'highlighter',
            tagNames: ['mark'],
            contentDefault: '<b>H</b>',
            contentFA: '<i class=\"fa fa-paint-brush\"></i>',
            aria: 'Highlight',
            action: 'highlight',

            init: function () {
                MediumEditor.extensions.button.prototype.init.call(this);

                this.classApplier = rangy.createClassApplier('highlight', {
                    elementTagName: 'mark',
                    normalize: true
                });
            },

            handleClick: function (event) {
                this.classApplier.toggleSelection();

                // Ensure the editor knows about an html change so watchers
                // are notified
                // ie: <textarea> elements depend on the editableInput event
                // to stay synchronized
                this.base.checkContentChanged();
            }
        });


  var editor = new MediumEditor('.editable', {
    toolbar: {
      buttons: ['bold', 'italic', 'underline', 'highlighter']
    },
    buttonLabels: 'fontawesome',
    extensions: {
      'highlighter': new HighlighterButton()
    }
  });
                                                                                                                                            

/*var editor = new MediumEditor('.editable', {
  toolbar: {
    buttons: ['h2', 'h3', 'bold', 'italic', 'underline', 'strikethrough', 'highlighter', 'quote', 'image', 'orderedlist', 'unorderedlist', 'pre', 'outdent', 'indent' ],
    buttonLabels: 'fontawesome',
    extensions: {
       'highlighter': new HighlighterButton()
     }
  }
});*/
</script>";
echo "<div class=section><button type=button onclick='submitForm(this.form)'>Создать</button></div></form>";
echo "</body>";
?>
</html>