<?php
function ajax_pager($app){
    $action = input('action') ? input('action') : null;
    switch($action){
        case 'set_list_type':
            $type = input('type') ? input('type') : null;
            if($type){
                $_SESSION['people_list_type'] = $type;
            }
        break;

        default:
        break;
    }
}