<?php
function video_upgrade_database() {
    register_site_page("videos", array('title' => 'video::videos-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(227778433660004555,'videos','content', 'middle');
        Widget::add(null,'videos','plugin::video|menu', 'right');
        Widget::add(null,'videos','plugin::video|featured', 'right');
        Widget::add(null,'videos','plugin::video|top', 'right');
        Widget::add(null,'videos','plugin::video|latest', 'right');

        Widget::add(null,'profile','plugin::video|profile-recent', 'right');
        Widget::add(null,'feed','plugin::video|latest', 'right');
        Menu::saveMenu('main-menu', 'video::videos', 'videos', 'manual', true, 'ion-ios-videocam-outline');
    });

    register_site_page("video-create", array('title' => 'video::videos-create-page', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(22777843368675565,'video-create','content', 'middle');
    });


    register_site_page("video-page", array('title' => 'video::view-video-page', 'column_type' => TWO_COLUMN_RIGHT_LAYOUT ), function() {
        Widget::add(2277788732204555,'video-page','content', 'middle');
        Widget::add(null,'video-page','plugin::video|related', 'right');
        Widget::add(null,'video-page','plugin::video|latest', 'right');
    });

    db()->query("ALTER TABLE  `videos` ADD  `auto_posted` INT NOT NULL DEFAULT  '0' AFTER  `privacy` ;");
}