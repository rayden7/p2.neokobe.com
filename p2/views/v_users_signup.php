<script type="text/javascript">
    $(document).ready(function() {

        // when a user mouses over any of the text input signup fields, blank them out so that the user can
        // enter their values; if they mouse out and have nulled out the values, go back to the defaults

        // // DEBUGGING
        //alert('there are this many inputs: [' + $('#frmSignup input.input-signup').length + ']');
        var formIdArr = new Array($('#frmSignup input.input-signup').length);

        $('#frmSignup input').each(function() {

            // for all form inputs except the submit button, save the values so
            // that we know whether or not the user submitted the default value
            if ($(this).attr('name') !== 'signup') {
                // // DEBUGGING
                // alert("child element\n\nvalue: [" + $(this).val() + "]\nid: ["+ $(this).attr('id')+"]" );
                formIdArr[$(this).attr('id')] = $(this).val();

                $(this).focus(function() {
                    if ($(this).val() == formIdArr[$(this).attr('id')]) $(this).val("");
                }).blur(function(){
                    if ($(this).val().length == 0) $(this).val(  formIdArr[$(this).attr('id')]  );
                });
            }
        });
    });

</script>

<h1>Join Us!</h1>

<form id="frmSignup" method="post" action="/users/p_signup">
    <p>It's fast, easy, and <i>free</i> to join!  Just enter your information below and you'll be read to start
        posting in minutes.  You will just need to verify your account by responding to an email we will send to
        you, and then your account will be active.</p>

<?php

    // show any errors that prevented the user signup from going through
    if (isset($_SESSION['signup']['errors'])) {

        $_SESSION['signup']['errors'] = array_unique($_SESSION['signup']['errors']);

        echo "<div class=\"form-errors\">\n";
        foreach ($_SESSION['signup']['errors'] as $key => $value) {
            echo "<p>".$value."</p>\n";
        }
        echo "\n</div>\n";
    }

?>

    <div class="signup-text">
        <span>First Name: </span>
        <input type="text"
               class="input-text input-signup"
               name="first_name"
               id="signup_first_name"
               value="<?php
               if (isset($_SESSION['signup']['postvals']['first_name'])) {
                   echo htmlspecialchars($_SESSION['signup']['postvals']['first_name']);
               } else echo "first name";
               ?>" />
    </div>
    <div class="signup-text">
        <span>Last Name: </span>
        <input type="text"
               class="input-text input-signup"
               name="last_name"
               id="signup_last_name"
               value="<?php
               if (isset($_SESSION['signup']['postvals']['last_name'])) {
                   echo htmlspecialchars($_SESSION['signup']['postvals']['last_name']);
               } else echo "last name";
               ?>" />
    </div>
    <div class="signup-text">
        <span>Desired Username: </span>
        <input type="text"
               class="input-text input-signup"
               name="username"
               id="signup_username"
               value="<?php
               if (isset($_SESSION['signup']['postvals']['username'])) {
                   echo htmlspecialchars($_SESSION['signup']['postvals']['username']);
               } else echo "username";
               ?>" />
    </div>
    <div class="signup-text">
        <span>Email: </span>
        <input type="text"
               class="input-text input-signup"
               name="email"
               id="signup_email"
               value="<?php
               if (isset($_SESSION['signup']['postvals']['email'])) {
                   echo htmlspecialchars($_SESSION['signup']['postvals']['email']);
               } else echo "email";
               ?>" />
    </div>
    <div class="signup-text">
        <span>Re-Enter Email: </span>
        <input type="text"
               class="input-text input-signup"
               name="email_verify"
               id="signup_email_verify"
               value="<?php
               if (isset($_SESSION['signup']['postvals']['email_verify'])) {
                   echo htmlspecialchars($_SESSION['signup']['postvals']['email_verify']);
               } else echo "verify email";
               ?>" />
    </div>
    <div class="signup-text">
        <span>Password: </span>
        <input type="password"
               class="input-text input-signup"
               name="password"
               id="signup_password"
               value="password" />
    </div>
    <div class="signup-text">
        <span>Re-Enter Password: </span>
        <input type="password"
               class="input-text input-signup"
               name="password_verify"
               id="signup_password_verify"
               value="password" />
    </div>
    <div class="signup-text">
        <span>&nbsp;</span>
        <input type="submit"
               class="btn-submit hue-green"
               name="signup"
               id="signup"
               value="Sign Up" />
    </div>

<?php
    unset($_SESSION['signup']);
?>

</form>
