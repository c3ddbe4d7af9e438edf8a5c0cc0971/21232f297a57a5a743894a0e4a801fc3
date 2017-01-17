<?php
function contact_pager($app) {
    $app->setTitle(lang('contact-us'));
    $message = null;
    $val = input('val') ? input('val') : null;
    if($val){
        if(submit_contact($val)){
            $message = " ";
        }
    }
    return $app->render(view('contact::contact', array('message' => $message)));
}