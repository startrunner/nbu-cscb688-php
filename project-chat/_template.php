<?php

require_once('./_users.php');

function render_page(PageOptions $options, $renderContent)
{
    $currentUser = UserOperations::instance()->get_current_user();

?>

<!doctype html>
<html>

<head>
    <title><?php echo ($options->pageTitle . ' - PHP Chat') ?></title>
    <link rel="stylesheet" href="css/w3css/4/w3.css" />
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="css/template.css" />

    <?php
    foreach ($options->cssSheets as $sheet) {
    ?>
    <link rel="stylesheet" href="<?php echo ($sheet); ?>" />
    <?php
    }
    ?>
</head>

<body>
    <nav class="w3-bar w3-blue">

        <?php
    if (!$currentUser->isOk) {
        ?>
        <a class="w3-bar-item w3-button" href="/login.php">Login</a>
        <a class="w3-bar-item w3-button" href="/register.php">Register</a>
        <?php
    } else {
        $fullName = $currentUser->firstName . " " . $currentUser->lastName;

        ?>
        <span class="w3-bar-item w3-text w3-right">Welcome, <?php echo ($fullName) ?> <a class="w3-link" href="/logout.php">(Logout)</a></span>
        <?php

    }
        ?>
    </nav>

    <main>
        <?php $renderContent(); ?>
    </main>

    <!-- <footer>This is footer</footer> -->
</body>

<?php
foreach($options->jsScripts as $script){
?><script src = "<?php echo($script) ?>"></script><?php
}
?>

</html>

<?php
}

class PageOptions
{
    public $cssSheets = [];
    public $jsScripts = [];
    public $pageTitle = '?';
}


?>