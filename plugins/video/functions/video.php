<?php
function add_video($val) {
    $expected = array(
        'title' => '',
        'description' => '',
        'privacy' => 1,
        'code' => '',
        'source' => '',
        'status' => 1,
        'file_path' => '',
        'entity_type' => 'user',
        'entity_id' => get_userid(),
        'category_id' => '',
        'photo_path' => '',
        'auto_posted' => 0
    );

    /**
     * @var $title
     * @var $description
     * @var $privacy
     * @var $code
     * @var $source
     * @var $file_path
     * @var $entity_id
     * @var $entity_type
     * @var $status
     * @var $category_id
     * @var $photo_path
     * @var $auto_posted
     */
    extract(array_merge($expected, $val));

    $result = array('result' => true);

    $result = fire_hook('can.post.video', $result, array($entity_type, $entity_id));
    if (!$result['result']) return false;
    $videoFile = input_file('video_file');
    if ($videoFile) {
        $uploader = new Uploader($videoFile, 'video_file');
        $uploader->setPath(get_userid().'/'.date('Y').'/videos/')->disableCDN();
        $uploader->uploadVideo();
        $file_path = $uploader->result();
        $status = 0;
    }
    $time = time();
    $userid = get_userid();
    $slug = toAscii($title);
    if (empty($slug)) $slug = md5(time());
    if (video_exists($slug)) {
        $slug = md5($slug.time());
    }

    db()->query("INSERT INTO videos (auto_posted,title,slug,description,user_id,entity_type,entity_id,photo_path,category_id,source,code,status,file_path,privacy,time) VALUES(
        '{$auto_posted}','{$title}','{$slug}', '{$description}','{$userid}','{$entity_type}','{$entity_id}','{$photo_path}','{$category_id}','{$source}','{$code}','{$status}','{$file_path}','{$privacy}','{$time}'
    )");

    $videoId = db()->insert_id;
    if ($file_path) {
        //we need to send notification of video processing
        send_notification(get_userid(), 'video.processing', $videoId);
    }
    $video = get_video($videoId);
    fire_hook('video.added', null, array($video,$videoId));
    return $video;
}

function is_video_owner($video) {
    if (!is_loggedIn()) return false;
    if ($video['user_id'] == get_userid()) return true;
    return false;
}

function save_video($val, $video) {
    $expected = array(
        'title' => '',
        'description' => '',
        'featured' => 0,
        'privacy' => $video['privacy'],
        'category' => $video['category_id']
    );
    /**
     * @var $title
     * @var $description
     * @var $featured
     * @var $privacy
     * @var $category
     */
    extract(array_merge($expected, $val));
    $videoId = $video['id'];
    db()->query("UPDATE videos SET title='{$title}',description='{$description}',featured='{$featured}',category_id='{$category}',privacy='{$privacy}' WHERE id='{$videoId}'");
    fire_hook('video.admin.edited', null, array($videoId));

    return true;
}

function delete_video($id) {
    $video = get_video($id);
    //delete the row
    db()->query("DELETE FROM videos WHERE id='{$id}'");
    if ($video['source'] == 'upload') {
        delete_file(path($video['photo_path']));
        delete_file(path($video['file_path']));
    }
    $query = db()->query("SELECT feed_id FROM feeds WHERE type_id='upload-video' AND type_data = {$id} AND feed_content = '' AND photos = '' AND link_details = ''");
    if ($query->num_rows > 0) {
        $feeds = fetch_all($query);
        foreach($feeds as $feed) {
            remove_feed($feed['feed_id']);
        }
    }
    return true;
}

function video_exists($slug) {
    $query = db()->query("SELECT slug FROM videos WHERE slug='{$slug}' LIMIT 1");
    if ($query and $query->num_rows > 0) return true;
    return false;
}

function get_video($id) {
    $query = db()->query("SELECT * FROM videos WHERE id='{$id}' OR slug='{$id}' LIMIT 1");
    if ($query) return $query->fetch_assoc();
    return false;
}

function get_all_videos($cat, $term, $limit = 10) {

    $sql = "SELECT * FROM videos ";

    $where = "";
    if ($cat and $cat != 'all') {
        $where = " category_id='{$cat}' ";
    }
    if ($term) {
        $where  .= ($where) ? " AND (title LIKE '%{$term}%' OR description LIKE '%{$term}%' ) " : " (title LIKE '%{$term}%' OR description LIKE '%{$term}%' )";
    }
    if ($where) $sql .= "WHERE {$where} ";
    $sql .= "ORDER BY time desc";
    return paginate($sql, $limit);
}

function get_videos($type, $category = 'all', $term = null, $userid = null, $limit = 10, $filter = 'all', $withTitle = false) {
    $sql = "SELECT * FROM videos ";
    $whereClause = "";
    $userid = ($userid) ? $userid : get_userid();
    if ($type == 'mine') {
        $whereClause .= ($whereClause) ? " AND user_id='{$userid}' ": " user_id='{$userid}'";
    }

    if ($type == 'user-profile') {
        $w = " user_id='{$userid}' AND entity_type='user' ";
        $privacy = array(1);
        if (is_loggedIn()) {
            if ($userid == get_userid()) $privacy = array_merge($privacy,array(2,3));
            if (friend_status($userid) == 2) $privacy[] = 2;
        }
        $privacy = implode(',', $privacy);
        $w .= " AND privacy IN ({$privacy}) ";
        $whereClause .= ($whereClause) ? " AND {$w} ": " {$w}";
    }

    if ($category and $category != 'all') {
        $whereClause .= ($whereClause) ? " AND category_id='{$category}' " : "category_id='{$category}' ";
    }

    if ($filter and $filter == 'featured') {
        $whereClause .= ($whereClause) ? " AND featured='1' " : " featured='1' ";
    }
    if ($term) {
        $whereClause  .= ($whereClause) ? " AND (title LIKE '%{$term}%' OR description LIKE '%{$term}%' ) " : " (title LIKE '%{$term}%' OR description LIKE '%{$term}%' )";
    }

    if ($type == 'browse') {
        $privacyClause = "(privacy = 1 OR user_id='{$userid}' ";
        $users = array(0);
        $users = array_merge($users, get_following($userid));

        $users = array_merge($users, get_friends($userid));
        $users = implode(',', $users);
        $privacyClause .= " OR (privacy='2'  AND user_id IN ({$users}))) ";
        $whereClause .= ($whereClause) ? " AND {$privacyClause} " : " {$privacyClause} ";
    }


    if ($whereClause) $sql .= " WHERE {$whereClause} ";
    if ($type != 'mine') $sql .= " AND status='1'";
    if ($withTitle) $sql .= " AND title != '' ";
    if ($filter and $filter == 'top') {
        $sql .= " ORDER BY view_count desc";
    } else {
        $sql .= " ORDER BY time desc";
    }

    //$query = db()->query($sql);
    //exit(db()->error);
    //exit($sql);
    $limit = ($limit)  ? $limit : config('video-list-limit', 10);
    return paginate($sql, $limit);
}

function get_related_videos($video, $limit = 6) {
    $title = $video['title'];
    $explode = explode(" ", $title);
    $videoId = $video['id'];
    $sql = "SELECT * FROM videos WHERE id != '{$videoId}'  AND (";
    $where = "";
    foreach($explode as $t) {
        $where .= ($where) ? " OR title LIKE '%{$t}%' " : "title LIKE '%{$t}%'";
    }
    $sql .= $where.') ORDER BY time desc';
    //exit($sql);
    return paginate($sql, $limit);

}

function get_video_owner($video) {
    $result = array(
        'name' => '',
        'image' => '',
        'link' => '',
        'id' => ''
    );

    if ($video['entity_type'] == 'user')  {
        $user = find_user($video['user_id'], false);
        $result['name'] = get_user_name($user);
        $result['image'] = get_avatar(200, $user);
        $result['link'] = profile_url(null, $user);
        $result['id'] = $user['id'];
    }

    return fire_hook('get.video.owner', $result);
}

function get_video_url($video) {
    return url('video/'.$video['slug']);
}

function get_video_categories() {
    $cacheName = "video-categories";
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $db = db()->query("SELECT * FROM video_categories WHERE parent_id='0' ORDER BY category_order ASC");
        $result = fetch_all($db);
        set_cacheForever($cacheName, $result);
        return $result;
    }
}

function get_video_category($id) {
    $query = db()->query("SELECT * FROM video_categories WHERE id='{$id}'");
    if ($query) return $query->fetch_assoc();
    return false;
}

function get_video_parent_categories($id) {
    $cacheName = 'video-parent-categories-'.$id;
    if (cache_exists($cacheName)) {
        return get_cache($cacheName);
    } else {
        $db = db()->query("SELECT * FROM video_categories WHERE parent_id='{$id}' ORDER BY category_order ASC");
        $result = fetch_all($db);
        set_cacheForever($cacheName, $result);
        return $result;
    }
}

function update_video_category_order($catId, $no, $parentId = null) {
    db()->query("UPDATE video_categories SET category_order='{$no}' WHERE id='{$catId}'");
    if ($parentId) {
        forget_cache('video-parent-categories-'.$parentId);
    } else {
        forget_cache("video-categories");
    }
    return true;
}

function delete_video_category($category) {
    $id = $category['id'];
    db()->query("DELETE FROM video_categories WHERE id='{$id}'");
    forget_cache('video-categories');
    if ($category['parent_id']) forget_cache('video-parent-categories-'.$category['parent_id']);
    return true;
}
function save_video_category($val, $cat) {
    /**
     * @var $title
     * @var $category
     */
    extract($val);
    $englishValue = $title['english'];
    $slug = $cat['title'];
    foreach($title as $langId => $t) {
        if (!$t) $t = $englishValue;
        (phrase_exists($langId, $slug)) ? update_language_phrase($slug, $t, $langId, 'video-category') : add_language_phrase($slug, $t, $langId, 'video-category');
    }

    $categoryId = $cat['id'];
    db()->query("UPDATE video_categories SET parent_id='{$category}' WHERE id='{$categoryId}'");
    fire_hook('video.category.edit', null, array($cat));
    forget_cache('video-categories');
    if ($category) forget_cache('video-parent-categories-'.$category);
    return true;
}

function video_add_category($val) {
    /**
     * @var $title
     * @var $category
     */
    extract($val);
    $titleSlug = 'video_category_'.md5(time().serialize($val)).'_title';
    $englishValue = $title['english'];
    foreach($title as $langId => $t) {
        if (!$t) $t = $englishValue;
        add_language_phrase($titleSlug, $t, $langId, 'video-category');
    }
    $slug = toAscii($englishValue);
    if (empty($slug)) $slug = md5(time());
    if (video_category_exists($slug)) {
        $slug = md5($slug.time());
    }

    db()->query("INSERT INTO video_categories (title,parent_id,slug) VALUES(
    '{$titleSlug}','{$category}','{$slug}'
    )");

    $insertedId = db()->insert_id;
    fire_hook('video.category.add', null, array($insertedId));
    forget_cache('video-categories');
    if ($category) forget_cache('video-parent-categories-'.$category);
    return true;
}

function video_category_exists($slug) {
    $db = db()->query("SELECT id FROM video_categories WHERE slug='{$slug}' LIMIT 1");
    if ($db and $db->num_rows > 0) return true;
    return false;
}