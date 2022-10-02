<?php

$content = '';

$_SESSION['id'] = $_GET['id'];
$id = $_SESSION['id'];

# getting data from the database to be displayed
$data[] = SQLformattedViewUsers($id, $pdo);

if ($data[0] != 'error') { # if no error was reported
    # getting full name of the user selected to be displayed
    $userName = $data[0]['userName'];
    $name = userFullName($userName, $pdo); 
    $userFullName = checkFullName($name); # checking if no error was reported
    $fullName = $userFullName[0];
    $content .= $userFullName[1];

    # heading and content created
    $caption = htmlHeading('Viewing details for selected user: ' . $fullName, 4);
    $content .= htmlTable($data, $caption, NULL, $pdo);

    # adding go back button to the bottom of the page   
    $link = 'userAdmin';
    $button = addButton($link);
    $content .= $button;
} else { # if error was reported
    $content .= htmlParagraph("ERROR user was not found.", False);
}
 ?>
