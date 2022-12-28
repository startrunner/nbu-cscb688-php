<?php
use const php_chat_configuration\db_host;
use const php_chat_configuration\db_user;
use const php_chat_configuration\db_pass;
use const php_chat_configuration\db_name;
use const php_chat_configuration\db_port;

require_once(__DIR__.'/_config.php');

function open_db_connection()
{
    $connection = mysqli_connect(db_host, db_user, db_pass, db_name, db_port, null);
    return $connection;
}


?>