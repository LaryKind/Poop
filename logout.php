<?php
session_start();
 
// удаление значений всех переменных сессии
$_SESSION = array();

session_destroy();
 
// редирект на страницу 'login'
header("location: index.php");
exit;
?>