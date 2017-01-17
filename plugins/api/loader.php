<?php
//load_functions("announcement::announcement");

register_pager("api/login", array('use' => "api::api@login_pager"));
register_pager("api/signup", array('use' => "api::api@signup_user"));
register_pager("api/feeds", array('use' => "api::api@get_feeds_pager"));

