<?php
function chat_upgrade_database() {
    register_site_page("messages", array('title' => 'chat::messages', 'column_type' => ONE_COLUMN_LAYOUT ), function() {
        Widget::add(2277784330005,'messages','content', 'middle');
    });
}