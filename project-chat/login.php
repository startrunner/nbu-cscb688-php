<?php

require_once('./_template.php');
require_once('./_users.php');
require_once('./_login.php');

const INPUT_LOGIN = 'input-login';
const INPUT_PASS = 'input-pass';

$failMessage = null;


if ($_POST) {
    $login = $_POST[INPUT_LOGIN];
    $pass = $_POST[INPUT_PASS];
    $result = UserOperations::instance()->login_user($login, $pass);
    if ($result->isOk) {
        LoginOperations::instance()->redirect_after_login();
        die();
    } else {
        $failMessage = $result->failMessage;
    }
}


$options = new PageOptions();
$options->pageTitle = 'Login';
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


    <label for="<?php echo (INPUT_LOGIN) ?>">Login</label>
    <input required type="text" id="<?php echo (INPUT_LOGIN) ?>" name="<?php echo (INPUT_LOGIN) ?>" class="w3-input" />

    <label for="<?php echo (INPUT_PASS) ?>">Password</label>
    <input required type="password" id="<?php echo (INPUT_PASS) ?>" name="<?php echo (INPUT_PASS) ?>"
        class="w3-input" />

    <button type="submit" class="w3-button"> Log In! </button>
</form>
<?php
});

?>