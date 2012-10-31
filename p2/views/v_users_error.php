<h1 class="errortext"><?=$title_error ?></h1>

<?php

// show any errors specified in the user array
if (isset($_SESSION['users']['errors'])) {

    if (is_array($_SESSION['users']['errors'])) {
        $_SESSION['users']['errors'] = array_unique($_SESSION['users']['errors']);

        echo "<div class=\"errors\">\n";
        foreach (array_unique($_SESSION['users']['errors']) as $key => $value) {
            echo "<p>".$value."</p>\n";
        }
        echo "\n</div>\n";
    }
    else {
        echo "<div class=\"errors\">\n<p>".$_SESSION['users']['errors']."\n</p>\n</div>\n";
    }
}

?>
