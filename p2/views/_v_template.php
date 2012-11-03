<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!-->
<html class="no-js" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html"> <!--<![endif]-->
<head>
    <base href="<?=@BASE_URL ?>" >
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?=@$title; ?></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">

    <!-- JS -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>

    <!-- Controller Specific JS/CSS -->
    <?php echo @$client_files; ?>

    <script type="text/javascript">
        $(document).ready(function() {

            // when a user mouses over the username or password inputs, blank them out so that the user can
            // enter their values; if they mouse out and have nulled out the values, go back to the defaults
            var default_username = $('#username').val();
            var default_password = $('#password').val();

            $('#login-form #username').focus(function() {
                if ($(this).val() == default_username) $(this).val("");
            }).blur(function(){
                if ($(this).val().length == 0) $(this).val(default_username);
             });

            $('#login-form #password').focus(function() {
                if ($(this).val() == default_password) $(this).val("");
            }).blur(function(){
                if ($(this).val().length == 0) $(this).val(default_password);
            });

        });
    </script>

</head>
<body>

<!--[if lt IE 7]>
<p class="chromeframe">You are using an outdated browser. <a href="http://browsehappy.com/">Upgrade your browser today</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to better experience this site.</p>
<![endif]-->

<header>
    <div id="header-container">
        <h1>neokobe.com</h1>
<?php
if (isset($_SESSION['user']['username']) && isset($_COOKIE['token'])) {
?>
        <div id="login">
            <form id="logout-form" action="/users/logout" method="post">
                <span>You are logged in [<a href="/users/profile/<?=htmlspecialchars($_SESSION['user']['username'])?>"><?=$_SESSION['user']['username']?></a>]</span>
                <input type="submit" class="btn-submit" name="Logout" value="Logout" alt="Logout" />
            </form>
        </div>
<?php
} else {
?>
        <div id="login">
            <form id="login-form" action="/users/p_login" method="post">
                <input type="text" class="input-username input-text" name="username" id="username" value="username" />
                <input type="password" class="input-password input-text" name="password" id="password" value="password" />
                <input type="submit" class="btn-submit" name="Login" value="Login" alt="Login" />
            </form>
        </div>
<?php
}
?>
    </div>
</header>

<div id="application-title">
    <a href="/"><h2>P2 - Twitter Clone</h2></a>
</div>

<?php

// build out the navigation programmatically so that we can show it at the top and
// bottom of the page without re-evaluating the logic that determines what links to show
$navigation = "";
$navigation .= "<nav>\n";
$navigation .= "\t<ul class=\"navigation\">\n";

// for logged-in users: show links to a list of all user accounts, and also to their individual profile
// for non-logged in users: show a link to create a new account, and to the generic profile page (redirects)
if (isset($_SESSION['user']['username']) && isset($_COOKIE['token'])) {
    $navigation .= "\t\t<a href=\"/users/all\"><li>All Users</li></a>\n";
    $navigation .= "\t\t<a href=\"/users/profile/".$_SESSION['user']['username']."\"><li>Profile</li></a>\n";
} else {
    $navigation .= "\t\t<a href=\"/users/signup\"><li>New Account</li></a>\n";
    $navigation .= "\t\t<a href=\"/users/profile\"><li>Profile</li></a>\n";
}

$navigation .= "\t\t<a href=\"/posts/all\"><li>All Posts</li></a>\n";
$navigation .= "\t\t<a href=\"/posts/friends\"><li>Friend Posts</li></a>\n";
$navigation .= "\t\t<a href=\"/posts/mine\"><li>My Posts</li></a>\n";
$navigation .= "\t\t<a href=\"/posts/add\"><li>New Post</li></a>\n";
$navigation .= "\t</ul>\n";
$navigation .= "</nav>\n";

?>

<?=$navigation ?>

<article>
    <div id="main-content">
        <?=$content;?>
    </div>
</article>

<?=$navigation ?>

<footer>
    <h3>&copy; 2012 David Killeffer.  All Rights Reserved.</h3>
</footer>

</body>
</html>