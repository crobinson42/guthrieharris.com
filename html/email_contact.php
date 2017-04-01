<?php
ini_set('display_errors', 'On');

if(!isset($_POST['name']) ||
        !isset($_POST['email']) ||
        !isset($_POST['comment'])) {
        die('We are sorry, but there appears to be a problem with the form you submitted.');
    }

    $name = $_POST['name']; // required
    $email_from = $_POST['email']; // required
    $comment = $_POST['comment']; // required
    $phone = $_POST['phone']; // required

    $email_message = "Form details below.\n\n";
    $email_message .= "Name: ".$name."\n";
    $email_message .= "Email: ".$email_from."\n";
    $email_message .= "Phone: ".$phone."\n";
    $email_message .= "Comment: ".$comment."\n";


// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($email_message,100);

// send email
mail("laura@guthrieharris.com","www.guthrieharris.com Contact Form",$msg);
mail("starr@guthrieharris.com","www.guthrieharris.com Contact Form",$msg);
?>