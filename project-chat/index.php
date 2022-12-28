<?php

require_once('./_template.php');
require_once('./_users.php');
require_once('./_login.php');
require_once('./_database.php');

$db = open_db_connection();

$currentUser = UserOperations::instance()->get_current_user();
if (!$currentUser->isOk) {
    LoginOperations::instance()->redirect_to_login();
    die();
}

$otherUser = null;
if (array_key_exists("pm", $_GET)) {
    $otherLogin = $_GET["pm"];
    $qOtherInfo = $db->prepare("
    select `first_name`, `last_name`, `login` from `user`
    where `login` = ?
    ");
    $qOtherInfo->execute([$otherLogin]);
    $otherUser = $qOtherInfo->get_result()->fetch_assoc();
}



$options = new PageOptions();
$options->pageTitle = 'Home';
array_push($options->cssSheets, 'css/page/index.css');
array_push($options->jsScripts, 'js/page/index.js');


render_page($options, function () use ($otherUser) {
    ?>

    <nav id="leftnav">

        <nav id="actions" class="w3-bar">
            <button id="btn-new-chat" class="w3-button"><i class="material-icons">edit_square</i></button>
        </nav>


        <nav id="recents" class="w3-container">
        </nav>

    </nav>

    <div id="chat">
        <?php
        if ($otherUser != null) {
            $firstName = $otherUser["first_name"];
            $lastName = $otherUser["last_name"];
            $login = $otherUser["login"];
            $fullName = $firstName . " " . $lastName;
            $otherUserBadgeText = UserOperations::instance()->badge_text($firstName, $lastName);
            ?>

            <h1 id="chat-header">
                <span class="w3-badge"><?php echo $otherUserBadgeText ?></span>
                <?php echo $fullName; ?>
            </h1>

            <div id="chat-messages">
            </div>

            <form id="chat-sender" method="dialog" style="display: flex;">
                <input type="text" id="input-message-text" class="w3-input" style="width: unset; display: inline; flex: 1;" />
                <button class="w3-button"><i class="material-icons">send</i></button>
            </form>

            <?php
        }
        ?>


    </div>
<?php
});


$db->close();
?>