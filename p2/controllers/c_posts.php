<?php

class posts_controller extends base_controller {

    public function __construct() {

        parent::__construct();

        # authenticate the user - if we don't have a user object, do not allow access to any of these pages
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

    }

    ###
    # This is the main index page of the posts section - it can be accessed via:
    #
    #     - http://p2.neokobe.com/posts
    #     - http://p2.neokobe.com/posts/
    #     - http://p2.neokobe.com/posts/index
    #
    # This URL re-routes to the "all posts" view at /posts/all
    ##
    public function index() {
        Router::redirect("/posts/all");
    }

    # Allow users to add new posts here; shows the form for a user to create a new post
    public function add() {
        $this->template->content = View::instance('v_posts_add');

        $this->template->title = "Create A New Post";
        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );
        $this->template->client_files = Utils::load_client_files($client_files);
        echo $this->template;
    }

    # process a new post form submission; alter the $_POST array slightly and then inserts the record into the database
    public function p_add() {

        $_POST['created'] = Time::now();
        $_POST['modified'] = Time::now();
        $_POST['user_id'] = $this->user->user_id;

        # Insert
        # Note we didn't have to sanitize any of the $_POST data because we're using the insert method which does it for us
        DB::instance(DB_NAME)->insert('posts', $_POST);

        Router::redirect("/posts/mine");
    }

    # Show the user just their own posts
    public function mine() {

        $this->template->content = View::instance('v_posts_all');
        $this->template->title = "My Posts";

        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );
        $this->template->client_files = Utils::load_client_files($client_files);

        # look up all the posts from the database posted by the currently logged in user in descending order by post date
        $q = "SELECT   p.post_id,
                       p.created AS post_created,
                       p.modified AS post_modified,
                       p.content AS post_content,
                       u.user_id,
                       u.username,
                       u.first_name,
                       u.last_name,
                       u.email
              FROM     posts AS p
                       JOIN users AS u USING(user_id)
              WHERE    p.user_id = ".$this->user->user_id."
              ORDER BY p.created DESC";

        $posts = DB::instance(DB_NAME)->select_rows($q);

        $this->template->content->all_posts = $posts;
        $this->template->content->title = $this->template->title;

        # Render the view
        echo $this->template;
    }

    # Show all posts for all users
    public function all() {

        $this->template->content = View::instance('v_posts_all');

        # Now set the <title> tag
        $this->template->title = "All Users' Posts";

        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );
        $this->template->client_files = Utils::load_client_files($client_files);

        # look up ALL the posts from the database, including user information
        # so that we can show the follow / unfollow links
        $q = "SELECT   p.post_id,
                       p.created AS post_created,
                       p.modified AS post_modified,
                       p.content AS post_content,
                       u.user_id,
                       u.first_name,
                       u.last_name,
                       u.email,
                       u.username
              FROM     posts AS p
                       JOIN users AS u USING(user_id)
              ORDER BY p.created DESC";

        $all_posts = DB::instance(DB_NAME)->select_rows($q);

        # pass the posts variable to the template, by creating a "posts" variable "on-the-fly"
        $this->template->content->all_posts = $all_posts;
        $this->template->content->title = $this->template->title;

        # Render the view
        echo $this->template;

        # remove any old "following" notices
        unset($_SESSION['following_notice']);
    }


    # Show all posts for people the current user is following
    public function friends() {

        # remove any old "following" notices
        unset($_SESSION['following_notice']);

        $this->template->content = View::instance('v_posts_all');

        # Now set the <title> tag
        $this->template->title = "My Friend's Posts";
        $this->template->content->title = $this->template->title;

        $client_files = Array(
            "/views/css/main.css",
            "/views/js/modernizr-2.6.1.min.js",
        );
        $this->template->client_files = Utils::load_client_files($client_files);

        # look up ALL the posts from the database, including user information
        # so that we can show the follow / unfollow links
        $q = "
              SELECT   p.post_id,
                       p.created AS post_created,
                       p.modified AS post_modified,
                       p.content AS post_content,
                       u.user_id,
                       u.first_name,
                       u.last_name,
                       u.email,
                       u.username
              FROM     posts AS p
                       JOIN users AS u USING(user_id)
                       JOIN users_users AS uu ON uu.user_id_followed = u.user_id
              WHERE    uu.user_id = ".$this->user->user_id."
              ORDER BY p.created DESC
             ";

        $friend_posts = DB::instance(DB_NAME)->select_rows($q);

        # pass the posts variable to the template, by creating a "posts" variable "on-the-fly"
        $this->template->content->all_posts = $friend_posts;

        echo $this->template;
    }

    # process a form submission to "follow" another user
    public function p_follow($user_id_followed=null) {

        # remove any old "following" notices
        unset($_SESSION['following_notice']);

        # if the form was submitted without specifying a userID to follow, then redirect back to all the posts
        if (is_null($user_id_followed))
            Router::redirect('/posts/all');

        # Prepare our data array to be inserted
        $data = Array(
            "created" => Time::now(),
            "user_id" => $this->user->user_id,
            "user_id_followed" => $user_id_followed
        );

        # save a record of the user that they wanted to follow
        DB::instance(DB_NAME)->insert('users_users', $data);

        # set a session variable that will be displayed on other pages
        # informing the user that the "follow" submission was processed
        $q = "
              SELECT     u.first_name,
                         u.last_name,
                         u.username
              FROM       users u
              WHERE      u.user_id = $user_id_followed
             ";
        $result = DB::instance(DB_NAME)->select_row($q);
        if (!is_null($result)) {
            $_SESSION['following_notice'] = "You are now following <b>"
                .htmlspecialchars($result['first_name'])." "
                .htmlspecialchars($result['last_name'])." (".htmlspecialchars($result['username']).")</b>.";

            # add the current user to the list of users being followed by the current user
            $_SESSION['user']['following_users'][$user_id_followed] = $result['username'];
        }

        # redirect back to all of the user postings for the site
        Router::redirect("/posts/all");
    }

    # process a form submission to "unfollow" a specified user
    public function p_unfollow($user_id_followed=null) {

        # remove any old "following" notices
        unset($_SESSION['following_notice']);

        # if the form was submitted without specifying a userID to unfollow, then redirect back to all the posts
        if (is_null($user_id_followed))
            Router::redirect('/posts/all');

        # Delete this connection
        $where_condition = 'WHERE user_id = '.$this->user->user_id.' AND user_id_followed = '.$user_id_followed;
        DB::instance(DB_NAME)->delete('users_users', $where_condition);

        # remove the user id being unfollowed from the list of users being followed by the current user
        if (isset($_SESSION['user']['following_users'][$user_id_followed])) {
            unset($_SESSION['user']['following_users'][$user_id_followed]);
        }

        # set a session variable that will be displayed on other pages
        # informing the user that the "unfollow" submission was processed
        $q = "
              SELECT     u.first_name,
                         u.last_name,
                         u.username
              FROM       users u
              WHERE      u.user_id = $user_id_followed
             ";
        $result = DB::instance(DB_NAME)->select_row($q);
        if (!is_null($result)) {
            $_SESSION['following_notice'] = "You are no longer following <b>"
                .htmlspecialchars($result['first_name'])." "
                .htmlspecialchars($result['last_name'])." (".htmlspecialchars($result['username']).")</b>.";
        }

        # redirect back to all of the user postings for the site
        Router::redirect("/posts/all");
    }

}
