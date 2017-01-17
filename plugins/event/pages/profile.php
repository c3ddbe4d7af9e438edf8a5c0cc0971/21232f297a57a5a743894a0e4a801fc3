<?php
if (is_loggedIn()) get_menu('dashboard-main-menu', 'events')->setActive();

function event_profile_pager($app) {
    return $app->render(view('event::profile/home', array('feeds' => get_feeds('event', $app->profileEvent['event_id']))));
}

function event_edit_profile_pager($app) {
    if (!is_event_admin($app->profileEvent)) redirect(event_url(null, $app->profileEvent));
    $message = null;
    $val = input('val');
    if ($val) {
        $validator = validator($val, array(
            'category' => 'required',
            'title' => 'required',
            'location' => 'required',
            'start_day' => 'required',
            'start_month' => 'required',
            'start_year' > 'required',
        ));

        if (validation_passes()) {
            $eventId  = save_event($val, $app->profileEvent['event_id']);

            return redirect(event_url(null, $app->profileEvent));
        } else {
            $message = validation_first();
        }
    }
    return $app->render(view('event::profile/edit', array('message' => $message)));
}

function invite_user_pager($app) {
    $eventId = input('id');
    $userid = input('userid');
    event_invite_user($eventId, $userid);

    return count_event_invited($eventId);
}

function search_invite_user_pager($app) {
    $eventId = input('id');
    $term = input('term');

    $users = '';
    foreach(event_get_invite_friends($term) as $user) {
        $users .= view('event::profile/invite/display', array('user' => $user, 'eventId' => $eventId));
    }

    return $users;
}

function rsvp_pager($app) {
    $eventId = input('id');
    $rsvp = input('v');
    event_rsvp($eventId, $rsvp);

    return json_encode(array(
        'going' => count_event_going($eventId),
        'maybe' => count_event_maybe($eventId),
        'invited' => count_event_invited($eventId)
    ));
}

function upload_cover_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $eventId = input('id');
    $event = find_event($eventId);
    if (!$event) return json_encode($result);
    if (!is_event_admin($event)) return json_encode($result);

    if (input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath('event/'.$event['event_id'].'/'.date('Y').'/photos/cover/');
        if ($uploader->passed()) {
            $original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->result();


            //delete the old resized cover
            if ($event['event_cover_resized']) {
                delete_file(path($event['event_cover_resized']));
            }

            //lets now crop this image for the resized cover
            $uploader->setPath('event/'.$event['event_id'].'/'.date('Y').'/photos/cover/resized/');
            $cover = $uploader->crop(0,  0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['image'] = url_img($cover);
            $result['original'] = url_img($original);
            $result['id'] = $uploader->insertedId;
            update_event_details(array('event_cover' => $original, 'event_cover_resized' => $cover), $event['event_id']);
            $result['status'] = 1;
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function reposition_cover_pager($app) {
    $pos = input('pos');
    $width = input('width', 623);
    $eventId = input('id');
    $event = find_event($eventId);
    if (!$event) return false;
    if (!is_event_admin($event)) return false;

    $cover = path($event['event_cover']);
    $uploader = new Uploader($cover, 'image', false , true);
    $uploader->setPath('event/'.$event['event_id'].'/'.date('Y').'/photos/cover/resized/');
    $pos = abs($pos);
    $pos = ($pos / $width);
    $yCordinate = 0;
    $srcWidth = $uploader->getWidth();
    $srcHeight = $srcWidth * 0.4;
    if (!empty($pos) & $pos < $srcWidth) {
        $yCordinate = $pos  * $uploader->getWidth();
    }
    $cover = $uploader->crop(0,  $yCordinate, $srcWidth, $srcHeight)->result();

    //delete old resized image if available
    if ($event['event_cover_resized']) {
        delete_file(path($event['event_cover_resized']));
    }
    update_event_details(array('event_cover_resized' => $cover), $event['event_id']);
    return url_img($cover);
}

function remove_cover_pager($app) {
    $eventId = input('id');
    $event = find_event($eventId);
    if (!$event) return false;
    if (!is_event_admin($event)) return false;
    delete_file(path($event['event_cover_resized']));

    update_event_details(array('event_cover' => '', 'event_cover_resized' => ''), $event['event_id']);
}

 