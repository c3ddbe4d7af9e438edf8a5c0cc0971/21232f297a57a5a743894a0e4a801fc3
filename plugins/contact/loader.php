<?php
load_functions('contact::contact');


register_asset("contact::css/backend/style.css");
register_asset("contact::css/contact.css");

register_pager("contact", array(
    'as' => 'contact-page',
    'use' => 'contact::contact@contact_pager'
));
add_available_menu('contact::contact-us', 'contact', 'ion-android-mail');



register_pager("admincp/contact/list", array(
    'filter' => 'admin-auth',
    'as' => 'admin-contact-list',
    'use' => 'contact::admincp@list_pager'
));


register_pager("admincp/contact/view", array(
    'filter' => 'admin-auth',
    'as' => 'admin-contact-view',
    'use' => 'contact::admincp@view_pager'
));

register_pager("admincp/contact/reply", array(
    'filter' => 'admin-auth',
    'as' => 'admin-message-reply',
    'use' => 'contact::admincp@reply_pager'
));

register_pager("admincp/contact/forward", array(
    'filter' => 'admin-auth',
    'as' => 'admin-message-forward',
    'use' => 'contact::admincp@forward_pager'
));

register_pager("admincp/contact/delete", array(
    'filter' => 'admin-auth',
    'as' => 'admin-message-delete',
    'use' => 'contact::admincp@delete_pager'
));


register_hook("admin-started", function() {
    add_menu("admin-menu", array(
        "id" => "contact-manager",
        "title" => lang("contact::contact-manager"),
        "link" => url_to_pager('admin-contact-list'),
        "icon" => "ion-android-mail"));
   });




register_hook('admin.statistics', function($stats) {
    $stats['contact'] = array(
        'count' => count_total_contact(),
        'title' => lang('contact::Contacts'),
        'icon' => 'ion-android-mail',
        'link' => url_to_pager('admin-contact-list'),
    );
    return $stats;
});

if(config('contact-dashboard-menu-link', false)){add_menu("dashboard-main-menu", array("icon" => "<i class='ion-android-mail'></i>", "id" => "contact", "title" => lang("contact::contact-us"), "link" => url("contact")));}
if(config('contact-explorer-menu-link', true)){add_menu("dashboard-menu", array("icon" => "<i class='ion-android-mail'></i>", "id" => "contact", "title" => lang("contact::contact-us"), "link" => url("contact")));}
if(config('contact-footer-menu-link', false)){add_menu("footer-menu", array("id" => "contact", "title" => lang('contact::contact-us'), "link" => url("contact")));}
