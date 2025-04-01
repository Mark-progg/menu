<?php
ini_set('session.gc_maxlifetime', 80000);
ini_set('session.cookie_lifetime', 80000);
ini_set('session.save_path', $_SERVER['DOCUMENT_ROOT'] .'/sessions/');
ini_set("oci8.max_persistent",1);
ini_set("oci8.persistent_timeout",15);
#ini_set("oci8.old_oci_close_semantics",1);
$main_pages=Array('index.php','records.php','marks.php','listadmin.php','discuss.php','log.php','records2.php','jirareasons.php');
$db='MASTER';
$ast_ip="10.4.0.100";
$conn = null;

function connect($u,$p,$d='MASTER'){
  global $conn;
  $conn=oci_connect($u,$p,'//10.1.10.29:1521/MASTER'); // Используем полную строку соединения, собранную из дескриптора TNS
  if (!$conn) {
    $e = oci_error();  
    error_log(htmlentities($e['message'], ENT_QUOTES));
    echo htmlentities($e['message'], ENT_QUOTES)."<br>\n";
    echo "<span style='color:red;'>Ошибка соединения с базой данных. Попробуйте еще раз.</span>";
    return false;
  }else{
    return $conn;
  }
}

function login(){
    global $conn;
    if ($conn) { // <--- Добавляем проверку на null перед oci_close()
      oci_close($conn);
    }
  global $main_pages;
  if(in_array(basename($_SERVER['PHP_SELF']),$main_pages)){
    session_regenerate_id();
    echo '<link rel="stylesheet" type="text/css" href="login.css">';
    echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <form id=loginform action="'.$_SERVER['PHP_SELF']. (!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '').'" method=post> 
    <h2>Menu-New login</h2>
    <input type=hidden id=action name=action value="login">
    <input type=text id="login" name=username placeholder="Имя">
    <input type=password id="password" name=password placeholder="Пароль">
    <input type=submit value=Войти>
    </form>';
    exit;
  }else{
    session_destroy(); 
    echo "redirecting";
    error_log("session timeout");
  }     
}
session_start();
if((isset($_SESSION['username'])) && (isset($_SESSION['password']))){
  if((connect($_SESSION['username'],$_SESSION['password'],$db)==false)||(@$_SERVER['HTTP_CLIENT_IP']!=$_SESSION['CLIENT_IP'])){
    session_destroy(); 
    echo '<meta http-equiv="refresh" content="0">';
  }
  if (isset($_GET['action']) && ($_GET['action']=='logout')){
    global $conn;
    if ($conn) { // <--- Добавляем проверку на null перед oci_close()
      oci_close($conn);
    }
    session_destroy();
    echo '<script type="text/javascript"> document.location.href="'.$_SERVER['PHP_SELF'].'"</script>';
    exit;
  }
                                  
}else{
  if ((isset($_POST['action'])) && ($_POST['action']=="login")){
    if ((isset($_POST['username'])) && (isset($_POST['password']))) {
      $password=$_POST['password'];
      $username='wt'.str_pad($_POST['username'],6,'0',STR_PAD_LEFT);
      if(connect($username,$password,$db)==true){
        global $conn;
        $stmt=oci_parse($conn,"select s.uname,p.name,ph.phone_number
                        from roles r,
                            grants g,
                            staff s,
                            phones ph,
                            persons p
                        where r.ROLE_ID='565' 
                          and g.ROLE_ID=r.ROLE_ID
                          and s.uname=substr(user,3)
                          and ph.person_id=s.person_id
                          and ph.person_sid=s.person_sid
                          and p.person_id=s.person_id
                          and p.person_sid=s.person_sid
                          and g.uname=s.uname
                          and length(ph.phone_number)=4
                          and (ph.phone_number like '15%' or ph.phone_number in ('1410','2016','1005','2065','2007','1072'))");
        oci_execute($stmt);
        $cnt=oci_fetch_all($stmt,$arr,null,1,OCI_FETCHSTATEMENT_BY_ROW+OCI_ASSOC);
        if($cnt>0){
          $_SESSION['username']=$username;
          $_SESSION['uid']=$arr[0]['UNAME'];
          $_SESSION['password']=$password;
          $_SESSION['name']=$arr[0]['NAME'];
          $_SESSION['phone_number']=$arr[0]['PHONE_NUMBER'];
          $_SESSION['CLIENT_IP']=$_SERVER['HTTP_CLIENT_IP'];
          $_SESSION['FORWARD_IP']=$_SERVER['HTTP_X_FORWARDED_FOR'];
          $_SESSION['REMOTE_IP']=$_SERVER['REMOTE_ADDR'];
          $stmt=oci_parse($conn,"select check_parameter('FSP','FSP_GRANT'),check_parameter('FSP','FSP_BAN_ACCESS') from dual");
          oci_execute($stmt);
          oci_fetch_all($stmt,$row,null,null,OCI_FETCHSTATEMENT_BY_ROW+OCI_NUM);
          if($row[0][0]=="TRUE"){
            $_SESSION['admin']=1;
          }
          if($row[0][1]=="TRUE"){
            $_SESSION['ban_access']=1;
          }
        }
        oci_free_statement($stmt);
      }else{
        login();
      }
    }
  }else{
    login();
  }
}                                          
 

if (!$conn){
  session_destroy();
  exit;
} else { // <--- Добавляем блок else
  oci_close($conn); // <--- Переносим oci_close($conn) внутрь блока else
}

?>
