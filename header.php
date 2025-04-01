<?php
//var_dump($_SESSION);
if(@$_SESSION['admin']==1) echo "<script type='text/javascript'> function adminPauseMember(elem){ if(confirm('Поставить/Снять с паузы '+elem.dataset.number)) loadExternalContent('./dnd2.php?exten='+elem.dataset.number,''); } </script>";
echo "<script type='text/javascript'>var self_phone='";
echo isset($_SESSION['phone_number']) ? $_SESSION['phone_number'] : "";
echo "';</script>";
?>
  <table id=header>
    <tr>
      <td><? echo $_SESSION['name']." - ".$_SESSION['phone_number'] ?>
        <div class="button inline" onclick="loadContent('anslist');" title="Входящие">
          <img src="./images/in.png" class=" pic" >
          <div id=anscalls class=inline></div>
          <div id=anslist class=menu></div>
        </div>
        <div class="button inline" onclick="loadContent('calledlist');" title="Исходящие">
          <img src="./images/out.png" class=" pic" >
          <div id=called class=inline></div>
          <div id=calledlist class=menu></div>
        </div>
        <div class="button inline" onclick="loadContent('lostlist');" title="Пропущеные">
          <img src="./images/lost.png" class=" pic" >
          <div id=lost class=inline></div>
          <div id=lostlist class=menu></div>
        </div>
        <div class="button inline" title="В очереди">
          <img src="./images/queue.png" class=" pic">
          <div id=callers class=inline></div>
        </div>
      </td>
      <td colspan=3 align=right>
        
        <button type=button onclick="document.location.href='./index.php'" <? if(basename($_SERVER['PHP_SELF'])=="index.php") echo "class=active" ?> >Операторы</button>
        <button type=button onclick="document.location.href='./records.php'" <? if(basename($_SERVER['PHP_SELF'])=="records.php") echo "class=active" ?>>Записи разговоров</button>
        <button type=button onclick="document.location.href='./marks.php'" <? if(basename($_SERVER['PHP_SELF'])=="marks.php") echo "class=active" ?>>Оценки</button>
        <button type=button onclick="window.open('./tickets/index.php')">Заявки</button>
<?php if(@$_SESSION['admin']==1){
        echo "<button type=button onclick='document.location.href=\"./listadmin.php\"'";
        if(basename($_SERVER['PHP_SELF'])=="listadmin.php") echo " class=active";
        echo ">Списки доступа</button>";
        echo "<button type=button onclick='document.location.href=\"log.php\"'";
        if(basename($_SERVER['PHP_SELF'])=="log.php") echo " class=active";
        echo ">Логи</button>";
        echo "<button type=button onclick='document.location.href=\"jirareasons.php\"'";
        if(basename($_SERVER['PHP_SELF'])=="jirareasons.php") echo " class=active";
        echo ">Причины JIRA</button>";
      }
?>
        <button type=button onclick="document.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?action=logout'">Выход</button>
        <div id=notifybox></div>
      </td>
    </tr>
    <tr>
      <td colspan=2><div class=inline id=dnd></div><div class=inline id=calcpause></div></td>
      <td><form id=call method=get action="javascript:void(0)" onsubmit="javascript:if(confirm('Позвонить?')){postform('./call.php',this,'')}"><input id=phonenumber name=phonenumber><button type=button onclick="if(confirm('Позвонить')){postform('./call.php',this.form,'')}">Позвонить</button></form></td>
      <td align=right><div id=clock></div></td>
    </tr>
  </table>
<div id=notifbox></div>
<div id=modal></div>  
