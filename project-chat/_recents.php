<?php

require_once(__DIR__ . '/_users.php');

class MessagesOperations
{
    private static ?MessagesOperations $_instance = null;

    public static function instance(): MessagesOperations
    {
        if (MessagesOperations::$_instance == null)
            MessagesOperations::$_instance = new MessagesOperations();

        return MessagesOperations::$_instance;
    }

    public function fetch_recents()
    {

        $currentUser = UserOperations::instance()->get_current_user();

        if (!$currentUser->isOk) {
            die();
        }

        $currentUserId = $currentUser->userId;

        $db = open_db_connection();


        $qRecents = $db->prepare("
        select 
            `other_user`.`login`,
            `other_user`.`first_name`,
            `other_user`.`last_name`,
            `latest_personal_message`.`time`,
            `latest_personal_message`.`text`,
            `latest_personal_message`.`ref_other`
        from 
            (
                select 
                    if(`ref_sender` = ?,`ref_recipient`, `ref_sender`) as `ref_other`,
                    `time`, `text`,
                    row_number() over (partition by `ref_other` order by `time` desc) as `rn`
                from 
                    `personal_message`
                where 
                    (`ref_sender` = ? or `ref_recipient` = ?)
            ) as `latest_personal_message`
        inner join
            `user` as `other_user`
        on
            `other_user`.`id` = `latest_personal_message`.`ref_other`
            and `rn` = 1
        ");



        $qRecents->execute([$currentUserId, $currentUserId, $currentUserId]);

        $qRecents = $qRecents->get_result();

        $result = [];
        for (; ; ) {
            $recent = $qRecents->fetch_assoc();
            if ($recent == null)
                break;

            $otherUserFirstName = $recent["first_name"];
            $otherUserLastName = $recent["last_name"];
            $otherUserFullName = $otherUserFirstName . " " . $otherUserLastName;
            $otherUserBadgeText = UserOperations::instance()->badge_text($otherUserFirstName, $otherUserLastName);

            //var_dump($recent);
            $model = array();
            $model["otherUserLogin"] = $recent["login"];
            //$model["otherUserFirstName"] = $otherUserFirstName;
            //$model["otherUserLastName"] = $otherUserLastName;
            $model["otherUserFullName"] = $otherUserFullName;
            $model["otherUserBadgeText"] = $otherUserBadgeText;
            $model["messageTime"] = $recent["time"];
            $model["messageText"] = $recent["text"];
            $model["otherUserId"] = $recent["ref_other"];

            array_push($result, $model);

        }

        return $result;
    }

}

?>