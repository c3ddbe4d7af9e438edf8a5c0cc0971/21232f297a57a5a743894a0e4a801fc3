<?php
get_menu('admin-menu', 'site-upgrade')->setActive();

function upgrade_pager($app) {
    $app->setTitle(lang('upgrader::upgrade-system'));

    $file = input('file');
    if ($file) {
        $zip = new ZipArchive();
        $path = path($file);
        if ($zip->open($path) === TRUE and !file_exists(path('storage/uploads/'.input('path')).'upgrade.php')) {
            $zip->extractTo(path('storage/uploads/'.input('path')));
            $zip->close();
        }
    }

    return $app->render(view('upgrader::upgrade'));
}

function upgrade_move_pager($app) {
    $app->setTitle('Move Lite Version data');

    $val = input('val');
    $message = null;
    if ($val) {

        try {
            /**
             * @var $host
             * @var $username
             * @var $password
             * @var $name
             * @var $prefix
             * @var $data
             */
            extract($val);
            $db = mysqli_connect($host, $username, $password, $name) ;

            if ($db) {
                //delete feeds
                db()->query("DELETE FROM feeds");
                if (true) {
                    $users = $db->query("SELECT * FROM {$prefix}users WHERE active='1' and activated='1'");

                    while($fetch = $users->fetch_assoc()) {
                        $dUser = $fetch['username'];
                        if ($fetch['id']) {
                            $dId = $fetch['id'];
                            $dPassword = $fetch['password'];
                            $email = $fetch['email_address'];
                            $fullname = $fetch['fullname'];
                            $avatar = ($fetch['avatar']) ? 'storage/'.$fetch['avatar'] : '';
                            $verified = $fetch['verified'];
                            $cover = ($fetch['cover']) ? 'storage/'.$fetch['cover'] : '';
                            $fArray = explode(' ', $fullname);
                            $lastName = '';
                            if (count($fArray) > 1) {
                                list($firstName, $lastName) = $fArray;
                            } else {
                                $firstName = $fullname;
                            }

                            $firstName = mysqli_escape_string(db(), $firstName);
                            $lastName = mysqli_escape_string(db(), $lastName);
                            $gender = mysqli_escape_string(db(), $fetch['genre']);
                            $country = mysqli_escape_string(db(), $fetch['country']);
                            $city = mysqli_escape_string(db(), $fetch['city']);
                            $day = mysqli_escape_string(db(), $fetch['birth_day']);
                            $month = mysqli_escape_string(db(), $fetch['birth_month']);
                            $year = mysqli_escape_string(db(), $fetch['birth_year']);
                            $join_date = mysqli_escape_string(db(), $fetch['created_at']);
                            if ($avatar) {
                                db()->query("INSERT INTO medias (user_id,path,file_type,type,type_id,privacy) VALUES(
                                '{$dId}','{$avatar}','image','profile-avatar','{$dId}','1'
                                )");
                            }

                            if ($cover) {
                                db()->query("INSERT INTO medias (user_id,path,file_type,type,type_id,privacy) VALUES(
                                '{$dId}','{$cover}','image','profile-cover','{$dId}','1'
                                )");
                            }
                            $insert = "INSERT INTO users (id,avatar,verified,cover,resized_cover,username,password,email_address,first_name,last_name,gender,country,city,active,activated,birth_day,birth_month,birth_year,join_date)VALUES";
                            $insert .= "('{$dId}','{$avatar}','{$verified}','{$cover}','{$cover}','{$dUser}','{$dPassword}','{$email}','{$firstName}','{$lastName}','{$gender}','{$country}','{$city}','1', '1', '{$day}','{$month}','{$year}','{$join_date}')";
                            db()->query($insert);

                            //lets move this user posts if instructed
                            if (in_array('posts', $data)) {
                                //we need to empty the user current posts

                                $posts = $db->query("SELECT * FROM {$prefix}posts WHERE user_id='{$dId}' AND community_id='' AND page_id=''");
                                upgrader_move_posts($posts, $db);
                            }

                            //lets move comments if instructed
                            if (in_array('comments', $data)) {
                                $comments = $db->query("SELECT * FROM {$prefix}comments WHERE user_id ='{$dId}' ");
                                $insert = "INSERT INTO comments (comment_id,user_id,entity_id,entity_type,type,type_id,text,image,time) VALUES";
                                while($fetch = $comments->fetch_assoc()) {
                                    $userid = $fetch['user_id'];
                                    $commentId = $fetch['id'];
                                    $text = $fetch['text'];
                                    $type = ($fetch['type'] == 'post') ? 'feed' : $fetch['type'];
                                    $typeId = $fetch['type_id'];
                                    $imgPath = 'storage/'.$fetch['img_path'];
                                    $timestamp = $fetch['created_at'];
                                    $t = strtotime($timestamp);
                                    $time = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t), date('j', $t), date('Y', $t));
                                    $insert .= "('{$commentId}','{$userid}','{$userid}','user','{$type}','{$typeId}','{$text}','{$imgPath}','{$time}'),";
                                }

                                $insert = substr($insert, 0, strlen($insert) - 1);
                                db()->query($insert);
                            }

                            /**if (in_array('communities', $data)) {
                                db()->query("DELETE FROM groups");
                                db()->query("DELETE FROM group_members");//lets us be save
                                $communities = $db->query("SELECT  FROM {$prefix}communities WHERE user_id='{$dId}'");
                                $insert = "INSERT INTO communities(group_id,user_id,group_name,group_title,group_description,group_logo,privacy,group_created_time) VALUES";
                                while($fetch = $communities->fetch_assoc()) {
                                    $userid = $fetch['user_id'];
                                    $title = $fetch['title'];
                                    $slug = $fetch['slug'];
                                    $description = $fetch['descriiption'];
                                    $privacy = ($fetch['privacy'] == 0) ? 1 : 2;
                                    $timestamp = $fetch['created_at'];
                                    $t = strtotime($timestamp);
                                    $time = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t), date('j', $t), date('Y', $t));
                                    $logo = ($fetch['logo']) ? 'storage/'.$fetch['logo'] : '';
                                    $commId = $fetch['id'];
                                    $insert .= "('{$commId}','{$userid}','{$slug}','{$title}','{$description}','{$logo}','{$privacy}','{$time}'),";
                                }

                                $insert = substr($insert, 0, strlen($insert) - 1);
                                db()->query($insert);
                                //lets move the communities this member have joined
                                $members = $db->query("SELECT * {$prefix}community_members WHERE user_id='{$dId}");
                                $insert = "INSERT INTO group_members (member_id, member_group_id)VALUES";
                                while($fetch = $members->fetch_assoc()) {
                                    $comId = $fetch['community_id'];
                                    $userid = $fetch['user_id'];
                                    $insert .= "('{$userid}', '{$comId}'),";
                                }
                                $insert = substr($insert, 0, strlen($insert) - 1);
                                db()->query($insert);
                            }**/

                            if (in_array('albums', $data)) {
                                //db()->query("DELETE * FROM photo_albums");
                                $albums = $db->query("SELECT * FROM {$prefix}photo_albums WHERE user_id='{$dId}'");
                                $insert = "INSERT INTO photo_albums(id,entity_id,entity_type,title,description,privacy,time) VALUES";
                                while($fetch = $albums->fetch_assoc()) {
                                    $id = $fetch['id'];
                                    $title = $fetch['title'];
                                    $userid = $dId;
                                    $timestamp = $fetch['created_at'];
                                    $t = strtotime($timestamp);
                                    $time = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t), date('j', $t), date('Y', $t));
                                    $insert .= "('{$id}','{$userid}','user','{$title}','','1','{$time}'),";

                                    //lets move the photos now
                                    $slug = 'album-'.$id;
                                    $photos = $db->query("SELECT * FROM {$prefix}photos WHERE slug = '{$slug}'");
                                    $pInsert = "INSERT INTO medias (user_id,path,file_type,type,type_id,privacy,album_id) VALUES";
                                    while($f = $photos->fetch_assoc()) {
                                        $type  = 'album';
                                        $typeId = $dId;
                                        $path = 'storage/'.$f['path'];
                                        $pInsert .= "('{$userid}','{$path}','image','{$type}','{$typeId}','1','{$id}'),";
                                    }
                                    $pInsert = substr($pInsert, 0, strlen($pInsert) - 1);
                                    db()->query($pInsert);
                                }
                                $insert = substr($insert, 0, strlen($insert) - 1);
                                db()->query($insert);
                            }


                            if (in_array('connections', $data)) {
                                $connections = $db->query("SELECT * FROM {$prefix}connections WHERE user_id='{$dId}' or to_user_id='{$dId}'");
                                $insert = "INSERT INTO relationship (from_userid,to_userid,type,confirm,time) VALUES";
                                while($fetch = $connections->fetch_assoc()) {
                                    $from = $fetch['user_id'];
                                    $to = $fetch['to_user_id'];
                                    $type = $fetch['way'];
                                    $confirm = $fetch['confirmed'];
                                    $timestamp = $fetch['created_at'];
                                    $t = strtotime($timestamp);
                                    $time = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t), date('j', $t), date('Y', $t));
                                    $insert .= "('{$from}','{$to}','{$type}','{$confirm}','{$time}'),";
                                }
                                $insert = substr($insert, 0, strlen($insert) - 1);
                                db()->query($insert);
                            }

                        }
                    }
                    //$insert = substr($insert, 0, strlen($insert) - 1);


                }



                $message = "Successfully moved data";
            } else {
                $message = "Problem connecting to the database, please confirm the details and make sure the prefix is correct too";
            }
        } catch(Exception $e){
            $message = "Problem connecting to the database, please confirm the details and make sure the prefix is correct too";
        }
    }

    return $app->render(view('upgrader::move', array('message' => $message)));
}

function upgrader_move_posts($posts, $db) {
    $insert = "INSERT INTO feeds (photos,video,files,link_details,shared,shared_id,shared_count,location,type,feed_id,user_id,entity_id,entity_type,to_user_id,feed_content,time,timestamp,privacy) VALUES";
    while($fetch = $posts->fetch_assoc()) {
        $fId = $fetch['id'];
        $userid = $fetch['user_id'];
        $contentType = $fetch['content_type'];
        $typeContent = $fetch['type_content'];
        $video_path = $fetch['video_path'];
        $file_path = $fetch['file_path'];
        $file_path_name = $fetch['file_path_name'];
        $privacy = $fetch['privacy'];
        $shared = $fetch['shared'];
        $sharedId = $fetch['shared_id'];
        $sharedCount = $fetch['shared_count'];
        if ($privacy == 5) {
            $privacy = 3;
        } elseif (in_array($privacy, array(2,3,4))) {
            $privacy = 2;
        } else{
            $privacy = 1;
        }
        $photos = '';
        $video = ($video_path) ? 'storage/'.$video_path : '';
        $files = '';
        $linkDetails = '';
        if ($fetch['link']) {
            $link = perfectUnserialize($fetch['link']);
            $linkDetails = array(
                'type' => '',
                'title' => $link['title'],
                'description' => $link['description'],
                'link' => $link['link'],
                'image' => $link['image'],
                'code' => '',
                'provider' => '',
                'provider-url' => '',
                'imageWidth' => 200

            );
            $linkDetails = perfectSerialize($linkDetails);
        }
        if ($contentType == 'image') {
            $images = perfectUnserialize($typeContent);
            if (count($images)) {
                $photos = array();
                foreach($images as $img) {
                    //$img = str_replace('%d', '%w', $img);
                    $img = 'storage/'.$img;
                    $imgType = $userid.'-posts';
                    $db->query("INSERT INTO medias (user_id,path,file_type,type,type_id,privacy) VALUES(
                                                '{$userid}','{$img}','image','{$imgType}','{$userid}','{$privacy}')");
                    $photos[$db->insert_id] = $img;
                }
                $photos = perfectSerialize($photos);
            }
        } elseif($contentType == 'oembed') {
            $typeContent = perfectUnserialize($typeContent);
            $code = (isset($typeContent[0])) ? $typeContent[0] : null;
            $linkDetails = array(
                'type' => '',
                'title' => '',
                'description' => '',
                'link' => '',
                'code' => $code,
                'image' => '',
                'provider' => '',
                'provider-url' => '',
                'imageWidth' => 200

            );
            $linkDetails = perfectSerialize($linkDetails);
        }
        if ($file_path) {
            $file_path = 'storage/'.$file_path;
            $pathInfo = pathinfo($file_path);
            $file_ext = strtolower($pathInfo['extension']);
            $files = perfectSerialize(array(
                'path' => $file_path,
                'name' => $file_path_name,
                'extension' => $file_ext
            ));
        }
        $entity_id = $userid;
        $entity_type = 'user';
        $to_userid = $fetch['to_user_id'];
        $timestamp = $fetch['created_at'];
        $text = mysqli_escape_string(db(), $fetch['text']);
        $t = strtotime($timestamp);
        $time = mktime(date('H', $t), date('i', $t), date('s', $t), date('n', $t), date('j', $t), date('Y', $t));
        //photos,video,files,link_details,shared,shared_id,shared_count,location,type,
        $insert .= "('{$photos}','{$video}','{$files}','{$linkDetails}','{$shared}','{$sharedId}','{$sharedCount}','','feed','{$fId}','{$userid}','{$entity_id}','{$entity_type}','{$to_userid}','{$text}','{$time}','{$timestamp}','{$privacy}'),";
    }
    $insert = substr($insert, 0, strlen($insert) - 1);
    db()->query($insert);
}


function upgrade_now_pager($app) {
    $file = input('file');
    $path = input('path');
    $result = array(
        'status' => 0,
        'message' => '',
    );
    $upgrade = include(path('storage/uploads/'.input('path')).'upgrade.php');
    $path = path('storage/uploads/'.input('path'));
    @unlink(path($file));//lets delete the file
    //delete necessary files  that most not be overwritten
    @unlink(path('storage/uploads/'.input('path')).'config.php');
    @unlink(path('storage/uploads/'.input('path')).'.htaccess');
    delete_file(path('storage/uploads/'.input('path')).'/storage/');

    $ftpHost = config('ftp-host', '127.0.0.1');
    $ftpPort = config('ftp-port', 21);
    $ftpUsername = config('ftp-username');
    $ftpPassword = config('ftp-password');
    $ftpType = config('ftp-type', 'file');
    $ssl = config('enable-sftp', false);
    $ftpPath = config('ftp-path', '');

    $connId = ($ssl) ? @ftp_ssl_connect($ftpHost, $ftpPort) : @ftp_connect($ftpHost, $ftpPort);
    if ($connId) {


        $login = ftp_login($connId, $ftpUsername, $ftpPassword);
        if ($login) {
            ftp_pasv($connId, true);
            //lets change to this sociavibe installation
            if (!@ftp_chdir($connId, $ftpPath)) {
                $result['message'] = "Failed: Provide the correct path to your crea8socialPRO Installation";
                return json_encode($result);
            }
            ftp_copy($connId, $path, $ftpPath);
            $result['message'] = "Upgrade Done Successfully";
            $result['status'] = 1;
            $result['url'] = url('admincp');

            //delete the upgrade folder
            delete_file(path('storage/uploads/upgrade/'));
            return json_encode($result);
        } else {
            $result['message'] = "Failed to connect to ftp server, Incorrect login details";
            return json_encode($result);
        }
    } else {
        $result['message'] = "Failed to connect to ftp server, try again";
        return json_encode($result);
    }
    ftp_close($connId);


    return json_encode($result);
}

function ftp_copy($conn_id, $src_dir, $dst_dir) {

    $d = dir($src_dir);

    while($file = $d->read()) {

        if ($file != "." && $file != "..") {

            if (is_dir($src_dir."/".$file)) {

                if (!@ftp_chdir($conn_id, $dst_dir."/".$file)) {

                    ftp_mkdir($conn_id, $dst_dir."/".$file);
                    ftp_chmod($conn_id, 0644, $dst_dir."/".$file);
                }

                ftp_copy($conn_id,$src_dir."/".$file, $dst_dir."/".$file);
                ftp_chmod($conn_id, 0644, $dst_dir."/".$file);
            }
            else {

                $upload = ftp_put($conn_id, $dst_dir."/".$file, $src_dir."/".$file, FTP_BINARY);
                ftp_chmod($conn_id, 0644, $dst_dir."/".$file);
            }
        }
    }

    $d->close();
}

function upgrade_upload_pager($app) {
    $file = input_file('file');
    if ($file) {
        $uploader = new Uploader($file, 'file');
        if ($uploader->passed()) {
            $path = 'upgrade/'.time().'/';
            $uploader->setPath($path);
            if ($uploader->extension != 'zip') return 0;
            $file = $uploader->uploadFile()->result();
            return url('admincp/upgrade?file='.$file.'&path='.$path);
        }
    }

    return 0;
}