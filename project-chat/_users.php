<?php
require_once(__DIR__ . '/_database.php');
require_once(__DIR__ . '/_utils.php');


class UserOperations
{
    private static ?UserOperations $_instance = null;
    public const SESSION_COOKIE_NAME = "ssk";

    public static function instance(): UserOperations
    {
        if (UserOperations::$_instance == null)
            UserOperations::$_instance = new UserOperations();

        return UserOperations::$_instance;
    }

    public function register_user($login, $pass, $confirmedPass, $firstName, $lastName): OperationResult
    {
        $result = new OperationResult();

        if ($pass != $confirmedPass) {
            $result->failMessage = "Passwords do not match";
            return $result;
        }

        $db = open_db_connection();

        $existingUsers = $db->prepare('select count(`id`) as `count` from `user` where `login` = ?');
        $existingUsers->execute([$login]);
        $existingUsers = $existingUsers->get_result()->fetch_assoc()['count'];

        if ($existingUsers > 0) {
            $result->failMessage = "User '" . $login . "' already exists!";
            return $result;
        }


        $passHash = password_hash($pass, PASSWORD_DEFAULT, array('cost' => 9));

        // var_dump($login, $pass, $passHash);

        //$firstName = 'Unknown';
        //$lastName = 'User';

        $userId = guidv4();

        $db->prepare('
            insert into `user`(`id`,        `login`,    `pass_hash`,   `first_name`,    `last_name`     )
            values            (?,           ?,          ?,              ?,              ?               )
        ')->execute([$userId, $login, $passHash, $firstName, $lastName]);


        self::_login_user($db, $userId);

        $db->close();
        $result->isOk = true;
        return $result;
    }

    public function login_user($login, $pass): OperationResult
    {
        $result = new OperationResult();
        $db = open_db_connection();

        $qUserInfo = $db->prepare("select `id`, `pass_hash` from `user` where `login` = ?");
        $qUserInfo->execute([$login]);
        $userInfo = $qUserInfo->get_result()->fetch_assoc();

        $isLoginOk = false;
        if ($userInfo != null) {
            $passHash = $userInfo['pass_hash'];
            $isLoginOk = password_verify($pass, $passHash);
        }


        if (!$isLoginOk) {
            $result->failMessage = "Username or password is incorrect";
            return $result;
        }

        $userId = $userInfo['id'];
        self::_login_user($db, $userId);
        $result->isOk = true;
        return $result;
    }

    private function _login_user($db, $userId)
    {
        $secretSessionKey = static::_generate_session_key();
        //var_dump($userId, $secretSessionKey);

        $qInsertSession = $db->prepare("
            insert into session(`id`,   `ref_user`,     `expiry_time`,                                  `cookie_value`  )
            values(             uuid(), ?,              date_add(current_timestamp, INTERVAL 1 DAY),    ?               )
        ");
        $qInsertSession->execute([$userId, $secretSessionKey]);

        setcookie(static::SESSION_COOKIE_NAME, $secretSessionKey);
    }

    private static function _generate_session_key(): string
    {
        $length = 150;
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        return static::_generate_random_string($alphabet, $length);
    }

    //https://stackoverflow.com/questions/4356289/php-random-string-generator
    //https://stackoverflow.com/questions/4356289/php-random-string-generator
    private static function _generate_random_string($characters, $length = 10)
    {
        //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function get_current_user(): CurrentUserResult
    {
        $db = open_db_connection();
        $result = new CurrentUserResult();
        $result->firstName = '123';
        $result->lastName = '123';

        if (!array_key_exists(static::SESSION_COOKIE_NAME, $_COOKIE)) {
            $result->failMessage = "No session cookie";
            return $result;
        }

        $secretSessionKey = $_COOKIE[static::SESSION_COOKIE_NAME];

        $qSessionInfo = $db->prepare("
        select `ref_user` from `session` where `cookie_value` = ? and `expiry_time` > current_timestamp limit 1
        ");
        $qSessionInfo->execute([$secretSessionKey]);
        $sessionInfo = $qSessionInfo->get_result()->fetch_assoc();

        if ($sessionInfo == null) {
            $result->failMessage = "Session info is null";
            return $result;
        }

        $userId = $sessionInfo['ref_user'];

        //echo ("UID: " . $userId . "<br/>");

        $qUserInfo = $db->prepare("
            select `first_name`, `last_name` from `user` where `id` = ? limit 1
        ");
        $qUserInfo->execute([$userId]);
        $userInfo = $qUserInfo->get_result()->fetch_assoc();
        if ($userInfo == null) {
            $result->failMessage = "User not found";
            return $result;
        }

        $firstName = $userInfo['first_name'];
        $lastName = $userInfo['last_name'];

        $result->isOk = true;
        $result->firstName = $firstName;
        $result->lastName = $lastName;
        $result->userId = $userId;
        return $result;
    }

    public function logout_current_user()
    {
        $cookieName = static::SESSION_COOKIE_NAME;
        setcookie($cookieName, null);
    }

    public function badge_text(string $firstName, string $lastName): string
    {
        $badgeText = "";
        if (strlen($firstName) > 0)
            $badgeText = $badgeText . $firstName[0];
        if (strlen($lastName) > 0)
            $badgeText = $badgeText . $lastName[0];
        if (strlen($badgeText) == 0)
            $badgeText = "?";

        return $badgeText;
    }
}

class CurrentUserResult extends OperationResult
{
    public string $firstName;
    public string $lastName;
    public string $userId;
}

?>