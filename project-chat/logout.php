<?php
require_once('./_users.php');

UserOperations::instance()->logout_current_user();
header("Location: /");
die();
?>