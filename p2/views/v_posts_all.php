
<h1><?=$title ?></h1>

<?php

// if the user just followed someone, show the notice here
if (
    @is_array($_SESSION['user']['following_users']) &&
    @count($_SESSION['user']['following_users']) > 0 &&
    !@is_null($_SESSION['following_notice']) &&
    @count($_SESSION['following_notice']) > 0
) :
?>
    <div class="flashnotices">
        <p><?=$_SESSION['following_notice'] ?></p>
    </div>
<?php

endif;

// if the user is on the "Friend Posts" page but there are no posts, show a message telling them to follow more people!
if ( @preg_match("/^\/posts\/friends\/?$/i", $_SERVER['PATH_INFO']) == 1 && (is_null($all_posts) || count($all_posts) == 0)) :
?>
    <div class="flashnotices">
        <p>Whoops!  Looks like there's no posts made by your friends!  <a href="/posts/all">Follow some more people!</a></p>
    </div>
<?php

endif;


$i = 0;
$side = "left";

// alternate left and right styles for the post bubbles
foreach ($all_posts as $key => $post):
    if ($i % 2) $side = "right";
    else $side = "left";
    $i++;

    // default posting time string and user avatar token image
    $timestring = " by <a href=\"/users/profile/". htmlspecialchars($post['username'])."\">". htmlspecialchars($post['username']) ."</a></p>\n";
    $tokenimage = "<img src=\"/views/images/token-02.png\" class=\"token-icon ".$side."\" alt=\"user: ".htmlspecialchars($post['username']) ."\" title=\"user: ".htmlspecialchars($post['username']) ."\" />\n";

    // if the post was made by the currently logged in user, show a different avatar and time string
    if (@$_SESSION['user']['user_id'] == @$post['user_id']) {
        $timestring = " by <a href=\"/users/profile/". htmlspecialchars($post['username'])."\">me</a></p>\n";
        $tokenimage = "<img src=\"/views/images/token-self.png\" class=\"token-icon ".$side."\" alt=\"user: me\" title=\"user: me\" />\n";
    }

?>
<div class="post-container">
    <div class="post-bubble post-bubble-<?=$side ?>">
        <p class="post-text"><?=$post['post_content'] ?></p>
        <p class="post-time">posted on <?=date('l, M. j, Y \a\t g:iA', $post['post_modified']) ?>
        <?=$timestring ?>
<?php

// only show follow/unfollow links for other users than the logged-in user
if (isset($_SESSION['user']['user_id']) &&  $_SESSION['user']['user_id'] != $post['user_id']  ) {

    // show the unfollow form for users currently being followed
    if ( @in_array($post['user_id'], @array_keys($_SESSION['user']['following_users'])) ) {
?>
            <form class="follow-form" action="/posts/p_unfollow/<?=$post['user_id']?>" method="post">
                <input type="submit" class="btn-submit hue-red" name="Unfollow" value="Unfollow" alt="Unfollow" />
            </form>
<?php
    }
    // and show the follow form for users not yet followed
    else {
?>
            <form class="follow-form" action="/posts/p_follow/<?=$post['user_id']?>" method="post">
                <input type="submit" class="btn-submit hue-green" name="Follow" value="Follow" alt="Follow" />
            </form>
<?php
    }
}
?>
    </div>
    <div class="post-spike post-spike-<?=$side ?>">
        <?=$tokenimage ?>
    </div>
</div>
<? endforeach; ?>
