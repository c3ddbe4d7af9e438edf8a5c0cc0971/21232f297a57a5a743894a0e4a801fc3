<?php
load_functions('sharer::sharer');

register_asset("sharer::css/sharer.css");

register_hook("footer", function() {
    echo view("sharer::share_site");
});

register_hook("after-render-css", function($html) {
    $dimension = config('button-size', 'medium') == 'medium' ? 40 : 80;
    $size = config('button-size', 'medium') == 'medium' ? 24 : 48;
    $html .= "
	<style>
        .share-site .share-button {
            min-width: ".$dimension."px;
            min-height: ".$dimension."px;
        	font-size: ".$size."px;
		}
    </style>\n";
    return $html;
    }
);

register_pager("sharer", array(
    'as' => 'sharer',
    'use' => 'sharer::sharer@sharer_pager')
);