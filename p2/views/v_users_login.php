
<?php

// show any errors
if (isset($_SESSION['users']['errors'])) {

    if (isset($error_title)) {
        echo "<h1 class=\"errortext\">".$error_title."</h1>\n";
    }

    $_SESSION['users']['errors'] = array_unique($_SESSION['users']['errors']);

    echo "<div class=\"errors\">\n";
    foreach ($_SESSION['users']['errors'] as $key => $value) {
        echo "<p>".$value."</p>\n";
    }
    echo "\n</div>\n";
}
else {
    echo "<h1>Login</h1>\n";
    echo "<p>Please use the login form at the top of the page.</p>\n";
}
?>

<!--<p>Please use the login form at the top of the page.</p>-->

<?php
//unset($_SESSION['signup']);
//unset($_SESSION['users']);
?>