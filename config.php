<?php
// данные ДБ
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'demo');
 
// подключение к БД MySql
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Проверка соединения
if($mysqli === false)
{
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}
?>