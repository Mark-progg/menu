<html>
<meta charset="utf-8">
<head>
<?php include('auth2.php'); ?>
<LINK href="style.css" rel="stylesheet" type="text/css">
<script src="./script.js" type="text/javascript"></script>
<script type="text/javascript">
setTimeout("onLoad()",2000);
</script>
</head>
<body  onclick="cleanMenus()">
<?php
include('header.php');
?>
<table id=middle>
  <tr>
    <td valign="top">
      <div id=members class=inline></div>
    </td>
    <td valign=top>
      <div id=callerslist></div>
      <div id=blacklist></div>
    </td>
    <td valign="top" align=right>
      <div id=missed></div>
    </td>
  </tr>
</table>
</body>
</html>
