<?php
function login_pager($app) {
    $redirectTo = input("redirect_to", '');
    $redirectTo = (empty($redirectTo)) ? url_to_pager("feed") : $redirectTo;
    if (is_loggedIn()) {
        return go_to_user_home();
    }
    $val = input("val");
    $message = null;
    $app->setTitle(lang('login'));
    if ($val) {
        /**
         * @var $username
         * @var $password
         */
        extract($val);
       if ($username and $password) {
           $login = login_user($username, $password, input("val.remember"));

           if ($login) {

               return go_to_user_home($redirectTo, find_user($username));
           }
       }
        $message = lang('login-error');
    }
    return $app->render(view('login/content', array('message' => $message)));
}

function forgot_password_pager($app) {
    $app->setTitle(lang('reset-password'));
    //$app->setLayout("layouts/blank");

    $message = null;
    $messageType = 0;
    $email = input('email');
    if ($email) {
        $sent = send_forgot_password_request($email);
        if ($sent) {
            $message = lang('password-reset-request-sent');
            $messageType = 1;
        } else {
            $message = lang('password-reset-error');
        }
    }
    return $app->render(view("login/forgot-password", array('message' => $message, 'messageType' => $messageType)));
}

function reset_password_pager($app) {
    $app->setTitle(lang('reset-password'));
    //$app->setLayout("layouts/blank");

    $message = null;
    $hash = input('code');
    $verifyHash = mail_hash_valid($hash);
    if (!$verifyHash) {
        return $app->render(view("login/reset-password-invalid"));
    }
    $val = input('val');
    if ($val) {
        /**
         * @var $password
         * @var $confirm_password
         */
        extract($val);
        if (!$password or !$confirm_password or ($password != $confirm_password)){
            $message = lang('password-match-error');
        } else {
            $user = find_user($verifyHash);
            $newPassword = hash_make($password);
            update_user(array('password' => $newPassword), $user['id']);
            delete_mail_hash($hash);
            $login = login_user($user['username'], $password, 0);
            if ($login) return go_to_user_home();
        }
    }

    return $app->render(view("login/reset-password", array('message' => $message)));
}
 