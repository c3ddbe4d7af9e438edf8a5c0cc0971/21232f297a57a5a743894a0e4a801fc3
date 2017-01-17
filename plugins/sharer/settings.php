<?php
return array(
    'title' => 'Sharer Plugin',
    'description' => lang("sharer::sharer-setting-description"),
    'settings' => array(
        'button-size' => array(
            'type' => 'selection',
            'title' => lang('sharer::button-size'),
            'description' => lang('sharer::button-size-desc'),
            'value' => 'medium',
            'data' => array(
                'medium' => lang('sharer::medium'),
                'large' => lang('sharer::large'),
            )
        ),
        'facebook-button' => array(
            'type' => 'boolean',
            'title' => lang('sharer::activate-facebook-button'),
            'description' => lang('sharer::activate-facebook-button-desc'),
            'value' => true
        ),
        'twitter-button' => array(
            'type' => 'boolean',
            'title' => lang('sharer::activate-twitter-button'),
            'description' => lang('sharer::activate-twitter-button-desc'),
            'value' => true
        ),
        'googleplus-button' => array(
            'type' => 'boolean',
            'title' => lang('sharer::activate-googleplus-button'),
            'description' => lang('sharer::activate-googleplus-button-desc'),
            'value' => true
        ),
        'linkedin-button' => array(
            'type' => 'boolean',
            'title' => lang('sharer::activate-linkedin-button'),
            'description' => lang('sharer::activate-linkedin-button-desc'),
            'value' => true
        ),
        'email-button' => array(
            'type' => 'boolean',
            'title' => lang('sharer::activate-email-button'),
            'description' => lang('sharer::activate-email-button-desc'),
            'value' => true
        ),
        'feed-button' => array(
            'type' => 'boolean',
            'title' => lang('sharer::activate-feed-button'),
            'description' => lang('sharer::activate-feed-button-desc'),
            'value' => true
        ),
    )
);