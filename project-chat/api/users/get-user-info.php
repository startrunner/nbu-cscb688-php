<?php

require_once('../../_database.php');

$db = open_db_connection();


$login = $_GET["u"];
$result = null;

$qUserInfo = $db->prepare("
select `first_name`, `last_name`, `login` from `user` where `login` = ?
");
$qUserInfo->execute([$login]);
$userInfo = $qUserInfo->get_result()->fetch_assoc();

if ($userInfo != null) {
    $result = [];
    $result["firstName"] = $userInfo["first_name"];
    $result["lastName"] = $userInfo["last_name"];
    $result["login"] = $userInfo["login"];
    $resultJson = json_encode($result);
    echo $resultJson;
} else {
    http_response_code(404);
}

$db->close();
?>