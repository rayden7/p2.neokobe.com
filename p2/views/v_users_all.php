
<h1><?=$title ?></h1>

<?php

foreach ($all_users as $key => $user) :

    $userinfo  = "<b>". htmlspecialchars($user['first_name']) . " ";
    $userinfo .= htmlspecialchars($user['last_name']);
    $userinfo .= "</b> (total posts: " . $user['total_posts'] . ") ";

    // adjust form action and submission button details based on whether the user is currently being followed or not
    if ((int)$user['currentlyFollowing'] == 1 )  {
        $formaction = "/posts/p_unfollow/".$user['user_id'];
        $submit_button_val = "Unfollow";
        $submit_button_css = "btn-submit hue-red";
    } else {
        $formaction = "/posts/p_follow/".$user['user_id'];
        $submit_button_val = "Follow";
        $submit_button_css = "btn-submit hue-green";
    }

?>
    <form action="<?=$formaction ?>" method="post">
        <p class="userlist">
            <?=$userinfo ?>
            <input type="submit"
                   class="<?=$submit_button_css ?>"
                   name="<?=$submit_button_val ?>"
                   value="<?=$submit_button_val ?>"
                   alt="<?=$submit_button_val ?>" />
        </p>
    </form>
<?php

endforeach;

?>
