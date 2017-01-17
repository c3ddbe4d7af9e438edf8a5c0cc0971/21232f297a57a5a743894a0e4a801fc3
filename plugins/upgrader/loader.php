<?php
load_functions("social::social");
register_hook('system.started', function($app) {
    if ($app->themeType == 'frontend' or $app->themeType == 'mobile') {
        //register assets
        register_asset("social::css/social.css");

    }
    register_asset("upgrader::js/upgrader.js");
});

register_pager('admincp/upgrade', array('as' =>  'admincp-upgrade', 'use' => 'upgrader::admincp@upgrade_pager', 'filter' => 'admin-auth'));
register_pager('admincp/upgrade/move/lite', array('as' =>  'admincp-upgrade-move', 'use' => 'upgrader::admincp@upgrade_move_pager', 'filter' => 'admin-auth'));
register_pager('admincp/upgrade/upload', array('as' =>  'admincp-upgrade-upload', 'use' => 'upgrader::admincp@upgrade_upload_pager', 'filter' => 'admin-auth'));
register_pager('admincp/upgrade/now', array('as' =>  'admincp-upgrade-now', 'use' => 'upgrader::admincp@upgrade_now_pager', 'filter' => 'admin-auth'));

register_hook("admin-started", function() {
    add_menu("admin-menu", array("id" => "site-upgrade", "title" => lang("upgrader::system-upgrader"), "link" => "#", "icon" => "ion-android-cloud-circle"));
    get_menu("admin-menu", "tools")->addMenu(lang("upgrader::system-upgrader"), '#', 'site-upgrade');
    get_menu("admin-menu", "tools")->findMenu('site-upgrade')->addMenu(lang("upgrader::upgrade-system"), url('admincp/upgrade'));
    get_menu("admin-menu", "tools")->findMenu('site-upgrade')->addMenu(lang("upgrader::move-lite-data"), url('admincp/upgrade/move/lite'));

    get_menu("admin-menu", "tools")->findMenu('site-upgrade')->addMenu(lang("settings"), url('admincp/plugin/settings/upgrader'), "settings");
});
