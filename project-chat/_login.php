<?php
require_once('./_utils.php');

class LoginOperations
{
    private static ?LoginOperations $_instance = null;
    private const LOGIN_REDRECT_COOKIE_NAME = "lr";

    public static function instance(): LoginOperations
    {
        if (static::$_instance == null)
            static::$_instance = new LoginOperations();

        return static::$_instance;
    }

    function redirect_to_login()
    {
        $url = get_current_url();
        setcookie(static::LOGIN_REDRECT_COOKIE_NAME, $url);
        header("Location: /login.php");
        die();
    }

    function redirect_after_login()
    {
        $url = "/";
        if (array_key_exists(static::LOGIN_REDRECT_COOKIE_NAME, $_COOKIE)) {
            $url = $_COOKIE[static::LOGIN_REDRECT_COOKIE_NAME];
        }

        header("Location: " . $url);
        die();
    }
}


?>