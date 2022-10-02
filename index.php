<?php

require_once 'includes/functions.php';  # user defined functions
require_once 'includes/config.php';  # database configuration datas
spl_autoload_register('myAutoloader');

session_start(); # starting he session

$headTitle = 'ERROR'; # if view file not found error message is displayed
$viewHeading = 'ERROR';
$form = ''; # log in; log out form
$nav = array();  # navigation
$nav['home'] = 'Home Page';

if ($databaseConnected) { # if database connected
	$admin = [
		'fName' => 'Admin',
		'sName' => 'User',
		'email' => NULL,
		'telephone' => NULL,
		'uName' => 'ubadmin01',
		'passw' => 'DCSadmin-01',
		'uType' => 'admin'
	];

	$content = ''; # content to fill in placeholder

	if (uniqueUname($admin['uName'], $pdo) == "error") {
		$content .= htmlParagraph("ERROR initial admin could not be created.", False);
	} else if (uniqueUname($admin['uName'], $pdo)) {
		createInitialUser($admin, $pdo);
	}

	###############################################
	#LOGGING IN AND LOGGING OUT
	

	# if log in button is pressed
	if (isset($_POST['login'])) {
		$formPlaceholders = clearErrorLoginFormPlaceholders();	
        #data in $_POST to be checked
	    $formData = validateLoginData($_POST, $pdo, $formPlaceholders); 
        $errors = $formData[0];
        $formPlaceholders = $formData[1]; 
        
        # if log in name or password are incorrect
        if (!empty($errors)) {    
            #replace all placeholders with their values
            $form = formOutput($formPlaceholders, 'html/loginForm.html');
        } else {# if log in name and password are correct
                $_SESSION['uName'] = $_POST['uName'];
                $_SESSION['passw'] = $_POST['password'];              	
        } 
	} else { # at first clean log in form is displayed
		$formPlaceholders = clearErrorLoginFormPlaceholders();
        $form = formOutput($formPlaceholders, 'html/loginForm.html');
	}

	# after logging in log out form is displayed
	if (isset($_SESSION['uName']) and isset($_SESSION['passw'])) {
		$name = userFullName($_SESSION['uName'], $pdo); 
        $userFullName = checkFullName($name); # checking if no error was returned
        $fullNameLoggedIn = $userFullName[0];
        $content = $userFullName[1];
        if ($content == '') { # if no error was returned
            $placeHolders[] = '[+fullName+]'; 
            $form = htmlTemplate($placeHolders, $fullNameLoggedIn, 'html/logoutForm.html');
        } else {
            $form = htmlTemplate($placeHolders, $content, 'html/logoutForm.html');
        }
	}

	# when log out button is pressed
	if (isset($_POST['logout'])) {
		if (isset($_POST['logout'])) {
            $_SESSION = array(); # $_SESSION values deleted
            if (ini_get("session.use_cookies")) {
                $yesterday = time() - (24 * 60 * 60);
                $params = session_get_cookie_params();
                setcookie(session_name(), '', $yesterday,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]);
            }
            session_destroy(); # session is terminated
            # returning to home page after each log out
            header('Location: '.$_SERVER['PHP_SELF'].'?view=home'); 
        }
	} 

	#####################################################
	#CREATING NAV MENU AND CHOSING WHICH PAGE TO DISPLAY
	# defining NAV html
	if (isset($_SESSION['uName']) AND (isset($_SESSION['passw']))) {
		$nav['moduleResults'] = 'Module Results';
        $userType = userType($_SESSION['uName'], $pdo);
        if ($userType == "error") {
            $content .= htmlParagraph("ERROR user type of the logged in user could not be found.", False);
        }
        if ($userType == "True") { # only admins can access this page
            $nav['userAdmin'] = 'User Administration';
            }		
    }
	
	# getting the view
	if (!isset($_GET['view'])) { 
		$i = 'home'; # display default landing view
	} else {
		$i = $_GET['view']; # else get requested view
	}

	# if logged in admin user was changed to academic user
	if ((isset($_SESSION['uName'])) and (userType($_SESSION['uName'], $pdo) == 'False') and ($i == 'userAdmin')) { 
		$i = 'home'; # display default landing view
	}
	
	# choosing which file to display
	if (isset($nav[$i])) { 
		$headTitle = $nav[$i]; 
		$file = 'views/' . $i . '.php'; 
		if (file_exists($file)) {
			include $file;	
		} else { # selected view file not found - report error
			$headTitle = "ERROR";
			$content = htmlParagraph("404 error - $file not found!", False);
		}
	} else {
		$headTitle = "ERROR";
		$file =  'views/404.php'; # $id not valid, POTENTIAL user hack report error
	}
	
} else {
	$content = "Database not connected.";
}

#######################################################
#BUILDING HTML PAGE TO DISPLAY
$placeholders = array(); #define array of HTML placeholders that are found in the template 
$placeholders[] = '[+title+]'; 
$placeholders[] = '[+heading+]';
$placeholders[] = '[+form+]';
$placeholders[] = '[+NAV+]';
$placeholders[] = '[+content+]';

$values = array(); #array of variables to replace the HTML placeholders with
$values[] = $headTitle;
$values[] = $viewHeading;
$values[] = $form;
$values[] = htmlNAVspe($nav); 
$values[] = $content;
#generate HTML 
$html = htmlTemplate($placeholders, $values, 'html/page.html'); 
echo $html; 

?>
