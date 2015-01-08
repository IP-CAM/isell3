<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('html_errors','off');

$uri_explode=explode('/',$_SERVER['REQUEST_URI']);
include "../$uri_explode[1]/config.php";

//echo "../$uri_explode[1]/config.php";

//require_once '../../_NILUA.php';
include 'application/controllers/iSellBase.php';
$session = new iSellBase();
$logged=0;
if( isset($_POST['login']) ){
    $login=$_POST['login'];
    $pass=$_POST['pass'];
    $session->login($login,$pass);
    $logged=1;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="js/dijit/themes/claro/claro.css" />
<script type="text/javascript" src="js/dojo/dojo.js" data-dojo-config="parseOnLoad: true"></script>
<link href="css/main.css" rel="stylesheet" type="text/css" />
<title>Страница Авторизации</title>
<script type="text/javascript">
var logged=<?php echo $logged ?>;
var ref="<?php echo str_replace('--', '&', $_GET['ref']) ?>";
function checkLogState(){
    if( logged ){
        if( parent && parent.onlogin )
            setTimeout(function(){parent.onlogin();},50);
        else if( ref )
            location.href=ref;
    }
    else
        document.getElementById('submit_button').focus();
}
function cancel(){
    if( parent && parent.onlogincancel )
        parent.onlogincancel();
}
</script>
</head>

<body style="background:none"  class=" claro " onload="checkLogState()">
    <div style="text-align:center">
        
    </div>
    
<div class="rounded" style="width:260px;margin-left:auto;margin-right:auto;background: #abc">
    <div style="color:#fff"><?php echo $_GET['msg'] ?></div>
    <div class="rounded" style="margin-left:auto;margin-right:auto;background: #def;">
<form action="#" method="post">
<table width="200" border="0" align="center" cellspacing="1">
  <tr>
    <td colspan="2" align="center">
        <big>Авторизация</big>
    </td>
  </tr>
  <tr>
    <td><input type="text" name="login" style="width:100%;"  autofocus="autofocus" placeholder="Логин" /></td>
  </tr>
  <tr>
    <td><input type="password" name="pass" style="width:100%;" placeholder="Пароль" /></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
    <button type="submit" data-dojo-type="dijit/form/Button" value="Submit" id="submit_button"><img src="img/apply24.png"/> Вход</button>
    <button type="button" data-dojo-type="dijit/form/Button" onclick="cancel()" <?php if($session->svar('user_level')<1)echo 'disabled="disabled"' ?> ><img src="img/close24.png"/> Закрыть</button>
    </td>
  </tr>
</table>
</form>
</div></div>
</body>
</html>