<?php
load_functions("notification::notification");

function notification_load_pager($app) {
    pusher()->reset('notification');
    delete_old_notifications();
    return view("notification::display", array("notifications" => get_notifications(10)->results()));
}

function notification_mark_pager() {
    mark_notification_read(input('id'), input('type'));
}

function notification_delete_pager() {
    return delete_notification(input('id'));
}

function notifications_pager($app) {
    pusher()->reset('notification');
    $app->setTitle(lang('notification::notifications'));
    delete_old_notifications();
    return $app->render(view("notification::lists", array('notifications' => get_notifications())));
}

function preload_pager($app) {
    $ids = input('ids');
    return view("notification::display", array("notifications" => preload_notifications($ids)));
}
 