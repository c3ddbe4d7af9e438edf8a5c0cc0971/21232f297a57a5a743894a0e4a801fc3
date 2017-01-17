<?php
return array(
    'title' => 'Contact Plugin',
    'description' => lang("contact::contact-setting-description"),
    'settings' => array(

        'contact-dashboard-menu-link' => array(
            'type' => 'boolean',
            'title' => lang('contact::show-contact-dashboard-link'),
            'description' => lang('contact::show-contact-dashboard-link-desc'),
            'value' => false
        ),
        'contact-explorer-menu-link' => array(
            'type' => 'boolean',
            'title' => lang('contact::show-contact-explorer-link'),
            'description' => lang('contact::show-contact-explorer-link-desc'),
            'value' => true
        ),
        'contact-footer-menu-link' => array(
            'type' => 'boolean',
            'title' => lang('contact::show-contact-footer-link'),
            'description' => lang('contact::show-contact-footer-link-desc'),
            'value' => false
        ),
    )
);