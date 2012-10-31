
<h1>Welcome, <?=$username ?>!</h1>

<p>Why not <a href="/posts/add">tell everyone what you're up to</a>, or browse what <a href="/posts/following">your friends are up to</a>?</p>

<hr />

<h2>Your Profile</h2>

<div class="signup-text">
    <span>First Name: </span>
    <input type="text"
           class="input-text input-signup"
           name="first_name"
           id="signup_first_name"
           disabled="true"
           value="<?=htmlspecialchars($first_name) ?>" />
</div>
<div class="signup-text">
    <span>Last Name: </span>
    <input type="text"
           class="input-text input-signup"
           name="last_name"
           disabled="true"
           value="<?=htmlspecialchars($last_name) ?>" />
</div>
<div class="signup-text">
    <span>Username: </span>
    <input type="text"
           class="input-text input-signup"
           name="username"
           disabled="true"
           value="<?=htmlspecialchars($username) ?>" />
</div>
<div class="signup-text">
    <span>Email: </span>
    <input type="text"
           class="input-text input-signup"
           name="email"
           id="signup_email"
           disabled="true"
           value="<?=htmlspecialchars($email) ?>" />
</div>
<div class="signup-text">
    <span>Last Login: </span>
    <input type="text"
           class="input-text input-signup"
           name="last_login"
           id="last_login"
           disabled="true"
           value="<?=date(TIME_FORMAT, $last_login) ?>" />
</div>
<div class="signup-text">
    <span>Joined: </span>
    <input type="text"
           class="input-text input-signup"
           name="joined"
           disabled="true"
           value="<?=date(TIME_FORMAT, $created) ?>" />
</div>

<?php if (isset($total_posts)) : ?>
<div class="signup-text">
    <span>Total Posts: </span>
    <input type="text"
           class="input-text input-signup"
           name="total_posts"
           disabled="true"
           value="<?=$total_posts ?>" />
</div>
<?php endif; ?>

<?php if (isset($most_recent_post_time)) : ?>
<div class="signup-text">
    <span>Most Recent Post At: </span>
    <input type="text"
           class="input-text input-signup"
           name="most_recent_post"
           disabled="true"
           value="<?=date(TIME_FORMAT, $most_recent_post_time) ?>" />
</div>
<?php endif; ?>
