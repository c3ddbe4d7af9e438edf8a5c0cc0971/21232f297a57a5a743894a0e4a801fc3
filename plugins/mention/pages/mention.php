<?php
load_functions("mention::mention");
function find_user_pager($app) {
    $text = input('text');
    echo view('mention::list', array('users' => find_mentions($text)));
}