<?php

$errors = array();      
$data = array();      

//validation
    if (empty($_POST['sdate']))
        $errors['sdate'] = 'Please provide a valid start date.';

    if (empty($_POST['edate']))
        $errors['edate'] = 'Please provide a valid end date.';

    if (empty($_POST['interval']))
        $errors['interval'] = 'Please select an interval.';

    if ( ! empty($errors)) {
        // if there are items in our errors array, return those errors
        $data['success'] = false;
        $data['errors']  = $errors;
    } else {

        $data['success'] = true;
        $data['message'] = 'Success!';
    }

    // return all our data to an AJAX call
    echo json_encode($data);
?>