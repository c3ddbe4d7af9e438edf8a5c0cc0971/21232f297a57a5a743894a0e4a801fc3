<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(!class_exists('finfo')) {exit('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body onload="top.alert(\'fileinfo is not installed\')"></body></html>');}
    function get_base($l) {$p = '';for($i = 0; $i < $l; $i++) {$p .=  '..'.DIRECTORY_SEPARATOR;}return realpath($p);}
    function get_url($base_dir) {$protocol = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 'https' : strtolower(explode('/', $_SERVER["SERVER_PROTOCOL"])[0]);$port = $_SERVER["SERVER_PORT"] == "80" ? '' : ':'.$_SERVER["SERVER_PORT"];$rel_base_dir = str_replace('\\', '/', preg_replace('/^'.preg_quote(str_replace('/', '\\', $_SERVER['DOCUMENT_ROOT'])).'/i', '', $base_dir));$url = $protocol.'://'.$_SERVER["SERVER_NAME"].$port.$rel_base_dir;return $url;}
    $config = include('config.php');
    $base_dir = get_base($config['base_diff']);
    $upload_path = $base_dir.DIRECTORY_SEPARATOR.$config['upload_path'];
    $path_info = pathinfo($_FILES['img']['name']);
    $dir = $path_info['dirname'];
    $file_name = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', trim($path_info['filename'])));
    $ext = isset($path_info['extension']) ? $path_info['extension'] : 'jpg';
    $i = 0;
    while(file_exists($dir.DIRECTORY_SEPARATOR.$file_name.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'.'.$ext)) {$i++;}
    $path = $upload_path.DIRECTORY_SEPARATOR.$file_name.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'.'.$ext;
    $url = get_url($base_dir).'/'.str_replace('\\', '/', $config['upload_path']).'/'.$file_name.'-'.str_pad($i, 4, '0', STR_PAD_LEFT).'.'.$ext;
    $allowedtypes = explode(',', $config['allowed_types']);
    if(!is_uploaded_file($_FILES['img']['tmp_name'])) {
        $errors[] = 'No image was uploaded';
    }
    if(is_uploaded_file($_FILES['img']['tmp_name'])) {
        if (class_exists('finfo')) {
            if((!in_array(ltrim(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['img']['tmp_name']), 'image/'), $allowedtypes))) {
                $errors[] = 'Invalid Format';
            }
        } else {
            echo 'fileinfo did not installed';
        }
    }
    if($_FILES['img']['size'] > $config['max_size']) {
        if ($config['max_size'] < 1024) {
            $max_size = $config['max_size'].' Bytes';
        } elseif ($config['max_size'] < 1048576) {
            $max_size = round($config['max_size'] / 1024).' KB';
        } else {
            $max_size = round($config['max_size'] / 1048576, 1).' MB';
        }
        $errors[] = 'Maximum Size Allowed '.$max_size;
    }
    if(empty($errors)) {
        move_uploaded_file($_FILES['img']['tmp_name'], $path);
        echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body onload="top.tinymce.activeEditor.insertContent(\'<img src='.$url.' />\'); top.tinymce.activeEditor.windowManager.close();"></body></html>';
    } else {
        echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body onload="top.alert(\''.implode(' &bull; ', $errors).'\')"></body></html>';
    }
}
