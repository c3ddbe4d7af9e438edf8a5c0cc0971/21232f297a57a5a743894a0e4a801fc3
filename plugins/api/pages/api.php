<?php
function get_feeds_pager($app) {
    $userid = input('userid');
    $user = find_user($userid);
    $result = array("feeds" => array());
    header("Cache-Control: max-age=86400");
    $offset = input("offset", 0);
    if ($user) {
        App::getInstance()->user = $user;
        App::getInstance()->userid = App::getInstance()->user['id'];
        $feeds = get_feeds("feed", null, null, $offset);
        foreach($feeds as $feed) {
            $result['feeds'][] = array(
                'id' => $feed['feed_id'],
                'name' => $feed['publisher']['name'],
                'avatar' => $feed['publisher']['avatar'],
                'message' => $feed['feed_content']
            );
        }
    }

    return json_encode($result);
}
function login_pager($app) {
    $result = array(
        'status' => "0",
        "userid" => '',
        'message' => '',
        'firstname' => '',
        'lastname' => '',
        'email' => '',
        'gender' => ''
    );

    $username = input('username');
    $password = input('password');
    $result['message'] = $username.$password;
    if ($username and $password) {
        $login = login_user($username, $password);
        if ($login) {
            $user = find_user($username);
            $result['userid'] = $user['id'];
            $result['firstname'] = $user['first_name'];
            $result['lastname'] = $user['last_name'];
            $result['email'] = $user['email_address'];
            $result['gender']  = $user['gender'];
            $result['status'] = "1";
        }
    }

    return json_encode($result);
}
 function signup_user($app) {
     $result = array(
         'status' => "0",
         "userid" => '10000',
         'message' => ''
     );
     $val = array(
         'first_name' => input('firstname'),
         'last_name' => input('lastname'),
         'username'  => input('username'),
         'email_address' => input('email_address'),
         'password'  => input('password'),
     );

     $rules = array(
         'first_name' => 'required|predefined',
         'last_name' => 'required|predefined',
         'username'  => 'required|predefined|alphanum|min:3|username',
         'email_address' => 'required|email|unique:users',
         'password'  => 'required|min:6',

     );

     $validator = validator($val, $rules);
     if (validation_passes()) {
         $added = add_user($val);
         send_user_welcome_email($val['username']);
         $user = find_user($added);
         $result['status'] = "1";
         $result['userid'] = $added;
         if(config('user-activation', false)) {
            db()->query("UPDATE users SET active='1',activated='1' WHERE id='{$added}'");
         }
     } else {
         $result['message'] = validation_first();
     }

     return json_encode($result);
 }