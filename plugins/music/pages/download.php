<?php
function download_pager($app) {
    $id = segment(2);
    $music = get_music($id);
    if($music) {
        $file = url($music['file_path']);
        header("Content-type: octet/stream");
        header("Content-disposition: attachment; filename=".$music['title'].";");
        try {
            $head = array_change_key_case(get_headers($file, TRUE));
            $filesize = $head['content-length'];
            header("Content-Length: ".$filesize);
        } catch (Exception $e) {

        }
        readfile($file);
        exit;
    } else {
        return MyError::error404();
    }
}
