<?php

require_once('./_template.php');
require_once('./_users.php');
require_once('./_login.php');


const INPUT_LOGIN = 'input-login';
const INPUT_PASS = 'input-pass';
const INPUT_FIRST_NAME = 'input-first-name';
const INPUT_LAST_NAME = 'input-last-name';
const INPUT_CONFIRM_PASS = 'input-confirm-pass';

$failMessage = null;

if ($_POST) {
    $login = htmlspecialchars($_POST[INPUT_LOGIN]);
    $pass = htmlspecialchars($_POST[INPUT_PASS]);
    $confirmedPass = htmlspecialchars($_POST[INPUT_CONFIRM_PASS]);
    $firstName = htmlspecialchars($_POST[INPUT_FIRST_NAME]);
    $lastName = htmlspecialchars($_POST[INPUT_LAST_NAME]);

    $result = UserOperations::instance()->register_user($login, $pass, $confirmedPass, $firstName, $lastName);
    if ($result->isOk) {
        LoginOperations::instance()->redirect_after_login();
        die();
    } else {
        $failMessage = $result->failMessage;
    }
}


$options = new PageOptions();
$options->pageTitle = 'Register';
array_push($options->cssSheets, 'css/page/login+register.css');

render_page($options, function () use ($failMessage) {
    ?>
    <form method="post" class="w3-container">
        <?php
        if ($failMessage != null) {
            ?>
            <div class="w3-panel w3-red">
                <?php echo ($failMessage); ?>
            </div>
            <?php
        }
        ?>


        <label for="<?php echo (INPUT_FIRST_NAME) ?>">First Name</label>
        <input required type="text" id="<?php echo (INPUT_FIRST_NAME) ?>" name="<?php echo (INPUT_FIRST_NAME) ?>"
            class="w3-input" />

        <label for="<?php echo (INPUT_LAST_NAME) ?>">Last Name</label>
        <input required type="text" id="<?php echo (INPUT_LAST_NAME) ?>" name="<?php echo (INPUT_LAST_NAME) ?>"
            class="w3-input" />

        <label for="<?php echo (INPUT_LOGIN) ?>">Login</label>
        <input required type="text" id="<?php echo (INPUT_LOGIN) ?>" name="<?php echo (INPUT_LOGIN) ?>" class="w3-input" />

        <label for="<?php echo (INPUT_PASS) ?>">Password</label>
        <input required type="password" id="<?php echo (INPUT_PASS) ?>" name="<?php echo (INPUT_PASS) ?>"
            class="w3-input" />


        <label for="<?php echo (INPUT_CONFIRM_PASS) ?>">Confirm Password</label>
        <input required type="password" id="<?php echo (INPUT_CONFIRM_PASS) ?>" name="<?php echo (INPUT_CONFIRM_PASS) ?>"
            class="w3-input" />

        <button type="submit" class="w3-button"> Register! </button>
    </form>
    <?php
});

?>