<?php

class users_controller extends base_controller {

    public function __construct() {
        parent::__construct();
    }

    /*-------------------------------------------------------------------------------------------------
    Access via http://yourapp.com/users/index/
    -------------------------------------------------------------------------------------------------*/
    public function index() {

        # Any method that loads a view will commonly start with this
        # First, set the content of the template with a view file
        $this->template->content = View::instance('v_users_index');

        # Now set the <title> tag
        $this->template->title = "Hello World Users";

        # If this view needs any JS or CSS files, add their paths to this array so they will get loaded in the head
        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );

        $this->template->client_files = Utils::load_client_files($client_files);

        // just in case the session is still there, kill it off, and the cookie as well
        setcookie("token", "", strtotime('-1 year'), '/');

        # Render the view
        echo $this->template;

    }


    public function error($title_error=null) {

        //echo "in the error handler!    \$title_error: [$title_error]";

        $this->template->content = View::instance('v_users_error');

        if (is_null($title_error) || $title_error == '') {
            $title_error = "Error Occurred";
        }
        $this->template->title = $title_error;
        $this->template->content->title_error = $title_error;

        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );
        $this->template->client_files = Utils::load_client_files($client_files);

        echo $this->template;
    }


    public function signup() {

        # Any method that loads a view will commonly start with this
        # First, set the content of the template with a view file
        $this->template->content = View::instance('v_users_signup');

        # Now set the <title> tag
        $this->template->title = "Sign Up For A New Account";

        # If this view needs any JS or CSS files, add their paths to this array so they will get loaded in the head
        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );
        $this->template->client_files = Utils::load_client_files($client_files);

        # Render the view
        echo $this->template;
    }


    public function p_signup() {

        // clear any previous submission errors
        unset($_SESSION);
        session_destroy();

        // just in case there are any issues with the user signup,
        // save the posted values so we can show them again on the form
        $_SESSION['signup']['postvals']['first_name'] = $_POST['first_name'];
        $_SESSION['signup']['postvals']['last_name'] = $_POST['last_name'];
        $_SESSION['signup']['postvals']['username'] = $_POST['username'];
        $_SESSION['signup']['postvals']['email'] = $_POST['email'];
        $_SESSION['signup']['postvals']['email_verify'] = $_POST['email_verify'];

        // if accessed without a post array or any values are missing, redirect to the signup page
        if (
            !isset($_POST) ||
            (
                !isset($_POST['first_name']) ||
                !isset($_POST['last_name'])  ||
                !isset($_POST['username'])   ||
                !isset($_POST['email'])      || ($_POST['email'] != $_POST['email_verify']) ||
                !isset($_POST['password'])   || ($_POST['password'] != $_POST['password_verify'])
            )
        ) {
            $_SESSION['signup']['errors'][] = 'Please complete the entire form before submitting.';
            Router::redirect("/users/signup");
        }

        // make sure nobody else is using the same username or email that were submitted
        $q = "SELECT (
                         SELECT COUNT(*)
                         FROM users u
                         WHERE u.username = '".DB::instance(DB_NAME)->sanitize($_POST['username'])."'
                     ) AS dupUsernameCount,
                     (
                         SELECT COUNT(*)
                         FROM users u
                         WHERE u.email = '".DB::instance(DB_NAME)->sanitize($_POST['email'])."'
                     ) AS dupEmailCount";

        $dups = DB::instance(DB_NAME)->select_row($q);
        if (isset($dups) && ($dups['dupUsernameCount'] >= 1 || $dups['dupEmailCount'] >= 1)) {
            if ($dups['dupUsernameCount'] > 0) {
                $_SESSION['signup']['errors'][] = 'Username is taken; please choose a different username.';
            }
            if ($dups['dupEmailCount'] > 0) {
                $_SESSION['signup']['errors'][] = 'Email already in use; please use a different username.';
            }
            Router::redirect("/users/signup");
        }

        # error out if the user didn't change the default password from the view
        if ($_POST['password'] == 'password' || $_POST['password_verify'] == 'password' ) {
            $_SESSION['signup']['errors'][] = 'Please enter a secure account password.';
            Router::redirect("/users/signup");
        }

        # modify the form submission values before we insert the user into the database, including removing
        # form fields that are not in the table but are used by the form (like the verification fields)
        $_POST['password'] = sha1(PASSWORD_SALT.$_POST['password']);
        $_POST['created'] = Time::now();
        $_POST['modified'] = Time::now();
        $_POST['token'] = sha1(TOKEN_SALT . $_POST['email'] . Utils::generate_random_string());
        $_POST['verification_code'] = Utils::generate_random_string(20);
        unset($_POST['email_verify']);
        unset($_POST['password_verify']);
        unset($_POST['signup']);

        # Insert this user into the database
        $user_inserted = DB::instance(DB_NAME)->insert("users", $_POST);

        $firstname = $_POST['first_name'];
        $lastname = $_POST['last_name'];

        # if the account signup was successful, then show the signup successful page
        if ($user_inserted) {

            // send the user an email including the verification code
            $email_subject = "Welcome to ".APP_NAME." - Activate Your Account";
            $email_body  = "Welcome to " . APP_NAME . ", ". $firstname . " ".$lastname. "!\n\n";
            $email_body .= "You must first activate your account before you may log in; please do so by clicking this link:\n\n";
            $email_body .= "\t" . BASE_URL . "/users/activate/" . $_POST['verification_code'];
            $email_body .= "\n\nIf the link is not clickable, please copy and paste it into your browser.\n\n";
            $email_body .= "We look forward to seeing you soon!";
            $email_bcc = SYSTEM_EMAIL;

            //$activation_email_sent = Email::send($_POST['email'], APP_EMAIL, $email_subject, $email_body, true, null, $email_bcc);
            $email_headers = "From: ". APP_EMAIL . "\r\n" .
                             "Bcc: ". $email_bcc . "\r\n" .
                             "Reply-To: ". APP_EMAIL . "\r\n" .
                             "X-Mailer: PHP/" . phpversion();
            $activation_email_sent = mail($_POST['email'], $email_subject, $email_body, $email_headers);

            // problem sending the account activation email - show an error
            if (!$activation_email_sent) {
                $this->template->content = View::instance('v_users_error');
                $this->template->title = "Problem Sending Activation Email";
                $this->template->content->title_error= $this->template->title;
                $client_files = Array(
                    "/views/css/main.css",
                    "/views/js/modernizr-2.6.1.min.js",
                );
                $this->template->client_files = Utils::load_client_files($client_files);
                $this->template->content->firstname = $firstname;
                $this->template->content->lastname = $lastname;
                $_SESSION['users']['errors'] = "There was a problem sending an account activation email - please
                                                email ".SYSTEM_EMAIL." directly to ask about your account.";
                echo $this->template;
            }
            // user account created, and activation email sent - redirect to thank you page
            else {
                $this->template->content = View::instance('v_users_signedup');
                $this->template->title = "Welcome, ".$firstname . " ".$lastname . "!";
                $client_files = Array(
                    "/views/css/main.css",
                    "/views/js/modernizr-2.6.1.min.js",
                );
                $this->template->client_files = Utils::load_client_files($client_files);
                $this->template->content->firstname = $firstname;
                $this->template->content->lastname = $lastname;
                echo $this->template;
            }
        }
        // there was a problem signing the user up - try to be informative
        else {
            $this->template->content = View::instance('v_users_error');
            $this->template->title = "Problem Creating Account";
            $this->template->content->title_error= $this->template->title;
            $client_files = Array(
                "/views/css/main.css",
                "/views/js/modernizr-2.6.1.min.js",
            );
            $_SESSION['users']['errors'] = "There was a problem creating your account!";
            $this->template->client_files = Utils::load_client_files($client_files);
            $this->template->content->firstname = $firstname;
            $this->template->content->lastname = $lastname;
            echo $this->template;
        }
    }

    public function activate($verification_code) {

        // just in case the session is still there, kill it off, and the cookie as well
        unset($_SESSION);
        setcookie("token", "", strtotime('-1 year'), '/');

        // first, see if we have a user account to begin with that has the verification code supplied
        $q = "SELECT u.first_name,
                     u.last_name,
                     u.username
              FROM   users u
              WHERE  u.verification_code = '".DB::instance(DB_NAME)->sanitize($verification_code)."'";

        $user_info = DB::instance(DB_NAME)->select_row($q);

        if (isset($user_info)) {
            $firstname = $user_info['first_name'];
            $lastname  = $user_info['last_name'];
            $username  = $user_info['username'];

            $account_activated = DB::instance(DB_NAME)->update('users',
                                                               array('verification_code'=>NULL),
                                                               "WHERE verification_code = '".DB::instance(DB_NAME)->sanitize($verification_code)."'"
                                                              );
            if ($account_activated == 1) {

                $this->template->content = View::instance('v_users_activated');

                # Now set the <title> tag
                $this->template->title = $username . "'s Account Activated";

                # If this view needs any JS or CSS files, add their paths to this array so they will get loaded in the head
                $client_files = Array(
                    "/views/css/main.css",
                    "/views/js/modernizr-2.6.1.min.js",
                );
                $this->template->client_files = Utils::load_client_files($client_files);

                $this->template->content->firstname = $firstname;
                $this->template->content->lastname  = $lastname;
                $this->template->content->username  = $username;

                # Render the view
                echo $this->template;
            }
            // there was a problem activating the account
            else {
                echo "There was a problem activating your account; please send an email to <a href=\"mailto:".APP_EMAIL."?subject=problem activating account for code [".$verification_code."]\">".APP_EMAIL."</a>.";
            }
        }
        else {
            echo "Verification code is not active!  Please send an email to <a href=\"mailto:".APP_EMAIL."?subject=verification code not active for code [".$verification_code."]\">".APP_EMAIL."</a>.";
        }
    }

    public function login() {

        # Any method that loads a view will commonly start with this
        # First, set the content of the template with a view file
        $this->template->content = View::instance('v_users_login');

        # Now set the <title> tag
        $this->template->title = "Hello World LOGIN";

        # If this view needs any JS or CSS files, add their paths to this array so they will get loaded in the head
        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );
        $this->template->client_files = Utils::load_client_files($client_files);

        # Render the view
        echo $this->template;
    }


    /**
     * Execute the actual login logic and validate the user credentials passed against the database.
     *
     */
    public function p_login() {

        unset($_SESSION);
        session_destroy();
        session_start();

        # Hash submitted password so we can compare it against one in the db
        $_POST['password'] = sha1(PASSWORD_SALT.$_POST['password']);

        # try to update the last_login time for this user, matching the username and password against the database
        $login_time_updated = DB::instance(DB_NAME)->update_row('users',
            array('last_login'=>Time::now()),
            "WHERE username = '".DB::instance(DB_NAME)->sanitize($_POST['username'])."' AND password = '".DB::instance(DB_NAME)->sanitize($_POST['password'])."' AND verification_code IS NULL"
        );

        # if we updated a record, then the user's last login time was updated, and we can now look up all the values we need
        if ($login_time_updated == 1) {
            $q = "SELECT u.created, u.modified, u.token, u.email, u.first_name, u.last_name, u.verification_code, u.username
                  FROM   users u
                  WHERE  u.username = '".DB::instance(DB_NAME)->sanitize($_POST['username'])."'
                         AND u.password = '".DB::instance(DB_NAME)->sanitize($_POST['password'])."'";

            $result = DB::instance(DB_NAME)->select_row($q);

            if ($result != null) {
                # Store this token in a cookie
                @setcookie("token", $result['token'], strtotime('+1 year'), '/');

                # Send them to the user's profile page
                Router::redirect("/users/profile/".$result['username']);
            }
            // could not find the user in the database with specified username and password and a NULL
            // validation_code (meaning that the user has already activated their account)
            else {
                setcookie("token", "", strtotime('-1 year'), '/');
                $_SESSION['users']['errors'][] = "Incorrect username or password. Please try again.";
                Router::redirect("/users/error");
            }
        }
        // could not update last login time because could not find user with specified username,
        // password, and NULL validation_code (which must be null or the account isn't considered active)
        else {

            // check if the credentials supplied are correct but the user just didn't
            // activate their account yet - if so, display a different error message
            $q = "SELECT u.created, u.modified, u.token, u.email, u.first_name, u.last_name, u.verification_code
                  FROM   users u
                  WHERE  u.email = '".DB::instance(DB_NAME)->sanitize($_POST['username'])."'
                         AND u.password = '".DB::instance(DB_NAME)->sanitize($_POST['password'])."'";
            $result = DB::instance(DB_NAME)->select_row($q);
            if ($result != null) {
                $_SESSION['users']['errors'][] = "You have not yet activated your account - please follow the instructions in the email that was sent, and email <a href=\"mailto:".APP_EMAIL."\">".APP_EMAIL."</a> if you are not able to activate your account.";
                $redirect_url = "/users/error/account-not-activated";
            }
            else {
                //$_SESSION['users']['errors'][] = "Unable to update last login time; please email <a href=\"mailto:".APP_EMAIL."\">".APP_EMAIL."</a> and inform them of the error.";
                $_SESSION['users']['errors'][] = "Incorrect username or password. Please try again.";
                $redirect_url = "/users/error";
            }

            setcookie("token", "", strtotime('-1 year'), '/');
            Router::redirect($redirect_url);
        }
    }

    public function logout() {

        # Generate and save a new token for next login
        $new_token = sha1(TOKEN_SALT.$this->user->email.Utils::generate_random_string());

        # Create the data array we'll use with the update method
        # In this case, we're only updating one field, so our array only has one entry
        $data = Array("token" => $new_token);

        # Do the update
        DB::instance(DB_NAME)->update("users", $data, "WHERE token = '".$this->user->token."'");

        # Delete their token cookie - effectively logging them out
        setcookie("token", "", strtotime('-1 year'), '/');
        setcookie("token", "", strtotime('-1 year'), BASE_URL);

        //unset($_SESSION);
        session_destroy();

        # Send them back to the main landing page
        Router::redirect("/");
    }

    public function profile($username=null) {

        // only show the profile page for logged-in users
        if (!$this->user) {
            $this->template->content = View::instance('v_users_login');
            $this->template->title = "Members Only";
            $client_files = Array(
                "/views/css/main.css",
                "/views/js/modernizr-2.6.1.min.js",
            );
            $this->template->client_files = Utils::load_client_files($client_files);
            $this->template->content->error_title = "Members Only";
            $_SESSION['users']['errors'][] = "Please use the login form at the top of the page.";
            echo $this->template;
            die();
        }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // and only allow users to see the profile pages of users they are following
        if ($this->user->username != $username) {
            Router::redirect("/users/profile/".htmlspecialchars($this->user->username));
        }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        unset($_SESSION['signup']['errors']);
        unset($_SESSION['users']['errors']);
        unset($_SESSION['users']['loggedin']);

        // just in case the page was accessed without a supplied username, try to default to using the logged-in user
        if (!isset($username) && isset($_SESSION['user']['username'])) {
            $username = $_SESSION['user']['username'];
        }

        # Search the db for this username, and get some info on their number of posts and most recent posting time
        $q = "
              SELECT     u.user_id,
                         u.created,
                         u.modified,
                         u.last_login,
                         u.token,
                         u.email,
                         u.first_name,
                         u.last_name,
                         u.username,
                         (
                             SELECT     MAX(p.created)
                             FROM       posts p
                             WHERE      p.user_id = u.user_id
                         ) AS most_recent_post_time,
                         (
                             SELECT     COUNT(*)
                             FROM       posts p
                             WHERE      p.user_id = u.user_id
                         ) AS total_posts
              FROM       users u
              WHERE      u.username = '".DB::instance(DB_NAME)->sanitize($username)."'
                         AND u.verification_code IS NULL
             ";
        $result = DB::instance(DB_NAME)->select_row($q);

        if ($result != null) {
            $_SESSION['user']['user_id']               = $result['user_id'];
            $_SESSION['user']['created']               = $result['created'];
            $_SESSION['user']['modified']              = $result['modified'];
            $_SESSION['user']['last_login']            = $result['last_login'];
            $_SESSION['user']['token']                 = $result['token'];
            $_SESSION['user']['email']                 = $result['email'];
            $_SESSION['user']['first_name']            = $result['first_name'];
            $_SESSION['user']['last_name']             = $result['last_name'];
            $_SESSION['user']['username']              = $result['username'];
            $_SESSION['user']['most_recent_post_time'] = $result['most_recent_post_time'];
            $_SESSION['user']['total_posts']           = $result['total_posts'];

            // so that we can show the correct FOLLOW / UNFOLLOW buttons on all posts, we need to know who this user
            // is currently following; look it up in the DB and then save an array in the session
            $following_query = "
                                   SELECT    DISTINCT
                                             f.user_id_followed,
                                             u.username AS username_followed
                                   FROM      users_users f
                                             JOIN users u ON u.user_id = f.user_id_followed
                                   WHERE     f.user_id = ".DB::instance(DB_NAME)->sanitize($result['user_id']);

            //echo $following_query;

            $following_result = DB::instance(DB_NAME)->select_rows($following_query);

            if (!is_null($following_result)) {

                //var_dump($following_result);

                foreach ($following_result as $key=>$val) {
                    $user_id_followed = $following_result[$key]['user_id_followed'];
                    $username_followed = $following_result[$key]['username_followed'];
                    $_SESSION['user']['following_users'][$user_id_followed] = $username_followed;
                }

//
//                # set a message letting them know the name of the user they are following
//                $q = "
//              SELECT     u.first_name,
//                         u.last_name,
//                         u.username
//              FROM       users u
//              WHERE      u.user_id = $user_id_followed
//             ";
//                $result = DB::instance(DB_NAME)->select_row($q);
//                if (!is_null($result)) {
//
//                    $_SESSION['following_notice'] = "You are now following <b>"
//                        .htmlspecialchars($result['first_name'])." "
//                        .htmlspecialchars($result['last_name'])." (<a href=\"/users/profile/"
//                        .htmlspecialchars($result['username'])."\">".htmlspecialchars($result['username'])."</a>)</b>.";
//
//                    # add the current user to the list of users being followed by the current user
//                    $_SESSION['user']['following_users'][$user_id_followed] = $result['username'];
//
//                }


            }

            // prepare the view for display
            $this->template->content = View::instance('v_users_profile');
            $this->template->title = "Welcome, ".$result['first_name'] . " ".$result['last_name'] . "!";
            $client_files = Array(
                "../views/css/main.css",
                "../views/js/modernizr-2.6.1.min.js",
            );
            $this->template->client_files = Utils::load_client_files($client_files);

            $this->template->content->created               = $result['created'];
            $this->template->content->modified              = $result['modified'];
            $this->template->content->last_login            = $result['last_login'];
            $this->template->content->token                 = $result['token'];
            $this->template->content->email                 = $result['email'];
            $this->template->content->first_name            = $result['first_name'];
            $this->template->content->last_name             = $result['last_name'];
            $this->template->content->username              = $result['username'];
            $this->template->content->most_recent_post_time = $result['most_recent_post_time'];
            $this->template->content->total_posts           = $result['total_posts'];

            echo $this->template;
        }
        // could not find the user in the database - show an error
        else {
            $this->template->content = View::instance('v_users_error');
            $this->template->title = "No such user found";
            $this->template->content->title_error= $this->template->title;
            $client_files = Array(
                "/views/css/main.css",
                "/views/js/modernizr-2.6.1.min.js",
            );
            $_SESSION['users']['errors'][] = "Sorry, there is no profile for user \"".$username."\".";
            $this->template->client_files = Utils::load_client_files($client_files);
            echo $this->template;
        }
    }

} // end class
