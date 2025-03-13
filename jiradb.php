<?php
$db_srv='10.1.13.2';
$db_name='jiradb';
$db_user='jirauser';
$db_pass='mypassword';
$j_conn=mysql_connect($db_srv,$db_user,$db_pass) or die(mysql_error());
mysql_set_charset('utf8',$j_conn);
mysql_select_db($db_name,$j_conn) or die(mysql_error());
mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
?>