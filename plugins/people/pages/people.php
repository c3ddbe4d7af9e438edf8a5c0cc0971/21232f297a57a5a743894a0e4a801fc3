<?php
function people_pager($app){
    $app->setTitle(lang("people::people"));
    $filter = input('val') ? input('val') : $_GET;
    $page = input('page') ? input('page') : null;
    $limit = config('max-page-result', 20);
    $appends = $_GET;
    $_SESSION['people_list_type'] = isset($_SESSION['people_list_type']) ? $_SESSION['people_list_type'] : config('default-people-list-type', 'list');
    $list_type = $_SESSION['people_list_type'];
    unset($appends['page']);
    $people = people_get_users($filter, $limit)->append($appends);
    $message = null;
    return $app->render(view('people::people', array('people' => $people, 'filter' => $filter, 'list_type' => $list_type, 'message' => $message)));
}