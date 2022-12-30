<?php

require_once(__DIR__ . '/../../_recents.php');

$result = MessagesOperations::instance()->fetch_recents();
$resultJson = json_encode($result);
echo $resultJson;


?>