<?php
$viewHeading = htmlHeading('User Administration',2);
$content = '';
$content .= htmlParagraph("Add new user: ", True); 
$content .= file_get_contents('html/userAdmin.html'); # adding new user link to the page

#choosing which page to display
if (isset($_GET['action'])) {
	$_SESSION['action'] = $_GET['action'];
	$i = $_SESSION['action'];
	$file = 'views/' . $i . '.php';
	if (file_exists($file)) {
		include $file;
	} else { #selected view file not found - report error
		$viewHeading = "ERROR";
		$content = htmlParagraph("404 error - $file not found!", False);
	}
} else { # else if delete link is pressed find the ID value of a selected user
	if (isset($_POST['idValue'])) {
		$_SESSION['idValue'] = $_POST['idValue']; 
	}
	# if deletion is confirmed, delete user
	if (isset($_POST['delete'])){
		$id = $_SESSION['idValue'];
		$content .= deleteUser($id, $pdo);
	}

	# displaying all the users that are existant in the database
	$data = selectAllUsers($pdo);

	if ($data != 'error') {
		# creating a table to display
		$table = array();
		foreach($data as $array) {	
			$table[] = $array;
		}
		$content .= htmlTable($table, NULL, True, $pdo);
	} else {
		$content .= htmlParagraph("ERROR users cannot be displayed.", False);
	}
}

?>

