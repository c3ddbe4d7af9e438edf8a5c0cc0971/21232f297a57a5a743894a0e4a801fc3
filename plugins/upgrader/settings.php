<?php
return array(
    'title' => 'Upgrader Settings',
    'description' => lang("upgrader::upgrade-settings-description"),
    'settings' => array(
        'ftp-type' => array(
            'type' => 'selection',
            'title' => 'FTP Connection Type',
            'description' => 'Set ftp connection type',
            'value' => 'ftp',
            'data' => array(
                'ftp' => 'FTP',
                'ftp/ftps' => 'FTP / FTPS'
            )
        ),

        'ftp-host' => array(
            'type' => 'text',
            'title' => 'FTP Host',
            'description' => '',
            'value' => '127.0.0.1'
        ),

        'ftp-port' => array(
            'type' => 'text',
            'title' => 'FTP Port',
            'description' => '',
            'value' => '21'
        ),

        'ftp-username' => array(
            'type' => 'text',
            'title' => 'FTP Username',
            'description' => '',
            'value' => ''
        ),

        'ftp-password' => array(
            'type' => 'text',
            'title' => 'FTP Password',
            'description' => '',
            'value' => ''
        ),

        'ftp-path' => array(
            'type' => 'text',
            'title' => 'FTP Path',
            'description' => 'Set to path where crea8socialPRO is installed',
            'value' => '/public_html/'
        ),

        'enable-sftp' => array(
            'type' => 'boolean',
            'title' => 'Enable SFTP',
            'description' => '',
            'value' => 0
        )
    )
);
 