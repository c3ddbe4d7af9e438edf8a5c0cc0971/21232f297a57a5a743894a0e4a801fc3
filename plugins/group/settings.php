<?php
return array(
    'title' => 'Group Plugin',
    'description' => lang("group::group-setting-description"),
    'settings' => array(
        'enable-group-posts-in-timeline' => array(
            'title' => lang('group::enable-group-posts-in-timeline'),
            'description' => lang('group::enable-group-posts-in-timeline-desc'),
            'type' => 'boolean',
            'value' => 1
        ),


    )
);
 