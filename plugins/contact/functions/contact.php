<?php
function submit_contact($val) {
    $db = db();
    $name = sanitizeText($val['name']);
    $email = sanitizeText($val['email']);
    $subject = sanitizeText($val['subject']);
    $message = sanitizeText($val['message']);
    $sent_date = sanitizeText($val['sent_date']);
    $sent_year = sanitizeText($val['sent_year']);
    $db->query("INSERT INTO contact (name,email,subject,message,sent_date,sent_year)VALUES(
        '{$name}','{$email}','{$subject}','{$message}','{$sent_date}','{$sent_year}'
    )");
    mailer()->setAddress($email)->setSubject($subject)->setMessage($message)->send();
//    exit($db->error);
    fire_hook('contact.added', null, array($val));
    return true;
}


function reply_message($val) {
    $db = db();
    $email = sanitizeText($val['email']);
    $subject = sanitizeText($val['subject']);
    $message = sanitizeText($val['message']);
    $db->query("INSERT INTO reply (email,subject,message)VALUES(
     '{$email}','{$subject}','{$message}'
    )");

    mailer()->setAddress($email)->setSubject($subject)->setMessage($message)->send();
//    exit($db->error);
        fire_hook('contact.added', null, array($val));
        return true;
    }


function forward_message($val) {
    $db = db();
    $email = sanitizeText($val['email']);
    $subject = sanitizeText($val['subject']);
    $message = sanitizeText($val['message']);
    $db->query("INSERT INTO reply (email,subject,message)VALUES(
     '{$email}','{$subject}','{$message}'
    )");
    mailer()->setAddress($email)->setSubject($subject)->setMessage($message)->send();
//    exit($db->error);
    fire_hook('contact.added', null, array($val));
    return true;
}


    function count_total_contact() {
        $q = db()->query("SELECT * FROM contact");
        return $q->num_rows;
    }

    function get_all_contacts() {
        $sql = "SELECT * FROM contact order by contact_id desc";
        return paginate($sql, 10);
    }

    function view_contact($contact_id) {
        $db = db();
        return fetch_all($db->query("SELECT * FROM contact WHERE contact_id = '$contact_id' "));
    }

    function reply($contact_id) {
        $db = db();
        return fetch_all($db->query("SELECT * FROM contact WHERE contact_id = '$contact_id' "));
    }

    function forward($contact_id) {
        $db = db();
        return fetch_all($db->query("SELECT * FROM contact WHERE contact_id = '$contact_id' "));
    }

    function delete_contact_message($contact_id){
        $db = db();
        $db->query("DELETE FROM contact WHERE contact_id = '$contact_id' ");
        return true;
    }

function delete_message_pager($app){
    $app->setTitle(lang("contact::delete-message"));
    $message = null;
    $contact_id = input('id') ? input('id') : null;
    $val = input('val');
    if($contact_id){
        if ($val) {
            marketplace_execute_form($val);
            return redirect_to_pager('marketplace-slug', array('appends' => '/m'));
        }
        $listing = marketplace_get_listing($listing_id)[0];
        return $app->render(view('marketplace::delete_listing', array('categories' => $categories, 'listing' => $listing, 'message' => $message)));
    }
}