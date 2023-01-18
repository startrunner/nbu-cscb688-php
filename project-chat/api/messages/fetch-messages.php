<?php

require_once('../../_users.php');

$currentUser = UserOperations::instance()->get_current_user();

if (!$currentUser->isOk) {
    die();
}

$currentUserId = $currentUser->userId;

$db = open_db_connection();

$otherUserLogin = htmlspecialchars($_GET["pm"]);
$qOtherUserId = $db->prepare("select `id` from `user` where `login` = ?");
$qOtherUserId->execute([$otherUserLogin]);
$otherUserId = $qOtherUserId->get_result()->fetch_assoc()["id"];


$qMessages = $db->prepare("
select `id`, `ref_sender`, `ref_recipient`, `text`, `time` from `personal_message`
where
    ((ref_sender = ? and ref_recipient = ?) or (ref_sender = ? and ref_recipient = ?))
order by
    `time` asc
");
$qMessages->execute([$currentUserId, $otherUserId, $otherUserId, $currentUserId]);
$messages = $qMessages->get_result();

$result = [];
for (; ; ) {
    $message = $messages->fetch_assoc();
    if ($message == null)
        break;

    $senderId = $message["ref_sender"];

    $model = array();
    $model["isSentByCurrentUser"] = $senderId == $currentUserId;
    $model["messageId"] = $message["id"];
    $model["senderId"] = $senderId;
    $model["recipientId"] = $message["ref_recipient"];
    $model["messageText"] = $message["text"];
    $model["time"] = $message["time"];

    array_push($result, $model);
}

$resultJson = json_encode($result);
echo $resultJson;


?>