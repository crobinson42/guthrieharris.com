<?php

if(!isset($_POST['name']) ||
        !isset($_POST['email']) ||
        !isset($_POST['comment'])) {
        died('We are sorry, but there appears to be a problem with the form you submitted.');
    }

    $name = $_POST['name']; // required
    $email_from = $_POST['email']; // required
    $comment = $_POST['comment']; // required
    $phone = $_POST['phone']; // required

died('here');

    $email_message = "Form details below.\n\n";
    $email_message .= "Name: ".clean_string($name)."\n";
    $email_message .= "Email: ".clean_string($email_from)."\n";
    $email_message .= "Phone: ".clean_string($phone)."\n";
    $email_message .= "Comment: ".clean_string($comment)."\n";


// use wordwrap() if lines are longer than 70 characters
$msg = wordwrap($email_message,100);

// send email
mail("coryrobinson42@gmail.com","www.guthrieharris.com Contact Form",$msg);
?>