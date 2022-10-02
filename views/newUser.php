<?php

$content = '';

$content .= htmlHeading('Add new user', 4);

if (isset($_POST['Submitted'])) { 
	# validating data submitted
	$formData = validateFormData($_POST, $pdo); 
	#extract clean data, errors and placeholders and placeholder data from arrays returned
	$cleanData = $formData[0];
	$errors = $formData[1];
	$formPlaceholders = $formData[2]; 	

	if (empty($errors)) {
	#o inserting data into the database if no errors were found
		$insertingData = insertingNewUserToDatabase($_POST, $pdo);
		
		# appropriate message is returned
		if ($insertingData == 'noError') {
			$newName = $cleanData['fName'] . ' ' . $cleanData['sName'];
			$content .=  htmlParagraph("New user $newName was stored in a database.", False);
		} else {
			$content .= htmlParagraph("ERROR new user was not created.", False);
		}
	} else { # else form with error messages is displayed
		$output = formOutput($formPlaceholders, 'html/userForm.html');
		$content .= $output;
	}
} else {
	# else empty form is displayed
	$output = formOutput(clearNewUserFormPlaceholders(), 'html/userForm.html');
	$content .= $output;
}	

# adding go back button to the bottom of the page
$link = 'userAdmin';
$button = addButton($link);
$content .= $button;

?>