<?php

require_once('../../_users.php');

$request = json_decode(file_get_contents('php://input'), true);
$currentUser = UserOperations::instance()->get_current_user();

if (!$currentUser->isOk) {
    die();
}

$db = open_db_connection();

//var_dump($request);

$senderId = $currentUser->userId;
$recipientLogin = $request["to"];
$messageText = $request["text"];

$qRecipientId = $db->prepare("select `id` from `user` where `login` = ?");
$qRecipientId->execute([$recipientLogin]);
$recipientId = $qRecipientId->get_result()->fetch_assoc()["id"];


$qInsert = $db->prepare("
insert into `personal_message`  (`id`,   `ref_sender`,  `ref_recipient`,    `time`,             `text`  )
values                          (uuid(), ?,             ?,                  current_timestamp,  ?       )
")->execute([$senderId, $recipientId, $messageText]);


var_dump($senderId, $recipientId, $messageText);

$db->close();

?>