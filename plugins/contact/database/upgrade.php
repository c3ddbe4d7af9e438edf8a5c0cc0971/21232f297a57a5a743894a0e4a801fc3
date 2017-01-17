<?php
function contact_upgrade_database() {
        register_site_page("contact-page", array('title' => lang('contact::contact-page'), 'column_type' => TWO_COLUMN_RIGHT_LAYOUT), function() {
        Widget::add(null,'contact-page','content', 'middle');
        Menu::saveMenu('main-menu', 'contact::contact-us', 'contact', 'manual', true, 'ion-android-mail');
        Menu::saveMenu('footer', 'contact::contact-us', 'contact', 'manual', true, 'ion-android-mail');
    });
}


