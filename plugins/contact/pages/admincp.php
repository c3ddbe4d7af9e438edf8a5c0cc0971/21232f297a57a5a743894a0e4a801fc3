<?php

function list_pager($app){
    $app->setTitle(lang("contact::list"));
    get_menu("admin-menu", "contact-manager")->setActive();
    return $app->render(view('contact::admincp/list', array('contacts' => get_all_contacts())));
}

function view_pager($app){
    $app->setTitle(lang("contact::view"));
    get_menu("admin-menu", "contact-manager")->setActive();
    $contact_id = input('contact_id');
    if(!$contact_id) return false; // if contact_id not exist
    return $app->render(view('contact::admincp/view', array('contacts' => view_contact($contact_id))));
}




function reply_pager($app){
    $app->setTitle(lang("contact::reply"));


    $message = null;
    $val = input('val') ? input('val') : null;
    if($val){
        if(reply_message($val)){
            $message = "Reply Sent!";
        }
    }

    get_menu("admin-menu", "contact-manager")->setActive();
    $contact_id = input('contact_id');
    if(!$contact_id) return false; // if contact_id not exist
    return $app->render(view('contact::admincp/reply', array('contacts' => reply($contact_id),'message' => $message)));
}


function forward_pager($app){
    $app->setTitle(lang("contact::forward"));


    $message = null;
    $val = input('val') ? input('val') : null;
    if($val){
        if(forward_message($val)){
            $message = "Message Sent!";
        }
    }

    get_menu("admin-menu", "contact-manager")->setActive();
    $contact_id = input('contact_id');
    if(!$contact_id) return false; // if contact_id not exist
    return $app->render(view('contact::admincp/Forward', array('contacts' => forward($contact_id),'message' => $message)));
}

function delete_pager($app){
    $app->setTitle(lang("contact::delete"));
    //get_menu("admin-menu", "contact-manager")->setActive();
    return $app->render(view('contact::admincp/delete'));
}


function manage_pager($app) {
    $action = input('action', 'order');
    $app->setTitle(lang('contact::contact'));
    switch($action) {
        case 'delete':
            $contact_id = input('contact_id');
            delete_help($contact_id);
            return redirect_back();
            break;
        case 'edit':
            $id = input('contact_id');
            $help = get_help($id);
            if (!$help) return redirect_back();
            $val = input('val');
            if ($val) {
                save_help($val, $contact_id);
                return (input('val.category')) ? redirect_to_pager('admincp-helps') : redirect_to_pager('admincp-help-category');
            }
            return $app->render(view('help::edit', array('help' => $help)));
            break;

    }
}





