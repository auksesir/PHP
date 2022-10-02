<?php

# MAIN HTML TEMPLATE
function htmlTemplate($placeHolders, $values, $file) {
	
	$errors = array(); #array to hold any placeholders that are not found
	if (file_exists($file)) { #check if template file exists
		$template = file_get_contents($file); #getting the html page template contents
			
            if ((stripos($template,$template) === false)) { 
                $errors[] = $placeholder; #add to errors' array if placeholder does not exist
            }
		if (!empty($errors)) { #creating error msg if any of placeholders are not found
			$errorMsg =	htmlParagraph("ERROR the following placeholders were not found in template $file :", False) .
					htmlParagraph(implode(', ',$errors), False); 
			$values[3] = $errorMsg; #update placeholder value array 
		}
		#replace placeholders with values
		return str_replace($placeHolders, $values, $template);	
	} else {
		return htmlParagraph("ERROR: Template file \"$file not found!", False);
	} 
}

# TEMPLATE
function formOutput($formPlaceholders, $file) {
	
	$content = htmlTemplate(array_keys($formPlaceholders), array_values($formPlaceholders), $file);
	return $content;
}

###################################################################
# BUILDING NAVIGATION
function htmlNAVspe($navData) { 
  
        $html = "\t" . '<nav>' . PHP_EOL;
        $html .= "\t\t" . '<ul>' . PHP_EOL;
        foreach ($navData as $key => $menuitem) {
            $html .= "\t\t\t" . '<li><a href="index.php?view=' . $key . '">' . $menuitem . '</a></li>' . PHP_EOL;
        }
        $html .= "\t\t" . '</ul>' . PHP_EOL;
        $html .= "\t" . '</nav>' . PHP_EOL;
        return $html;
    }

###########################################################
#VALIDATING LOG IN DATA

# placeholders form that is used to validate log in information
function clearErrorLoginFormPlaceholders() {
 
        $placeHolders = [
                        '[+uName+]' => '',
                        '[+uNameError+]'=>'',
                        '[+passw+]' => '',
                        '[+passwError+]'=>''
                        ];
        return $placeHolders;
    }

# validating log in data
function validateLoginData($formData, $pdo, $formPlaceholders){
	global $formPlaceholders;
	global $errorsLogin;
    validateLogin($formData);
	return [$errorsLogin, $formPlaceholders];
}

function validateLogIn($formData) {
    
    global $formPlaceholders;
    global $errorsLogin;
    global $pdo;

	# if 'userName' is found to be unique, it is not existant in the database
    if ((uniqueUname($formData['uName'], $pdo) or ($formData['uName'] == ''))) {
        $errorsLogin['uName'] = "User Name does not exist";
		$formPlaceholders['[+uNameError+]'] = htmlentities($errorsLogin['uName']);
		$formPlaceholders['[+passwError+]'] = '';
        $formPlaceholders['[+uName+]'] = '';
        $formPlaceholders['[+passw+]'] = '';
		return;
    } else if ((userPassword($formData['uName'], $pdo) == $formData['password'])){
		$formPlaceholders['[+uName+]'] = '';
		$formPlaceholders['[+passw+]'] = '';
		$formPlaceholders['[+uNameError+]'] = '';
		$formPlaceholders['[+passwError+]'] = ''; 
		return; 
    } else {
		$errorsLogin['password'] = "Wrong password";
		$formPlaceholders['[+passw+]'] = '';
		$formPlaceholders['[+passwError+]'] = htmlentities($errorsLogin['password']);
		$formPlaceholders['[+uName+]'] = $formData['uName'];
		$formPlaceholders['[+uNameError+]'] = '';
	}
}

function userPassword($x, $pdo) {
	try {
		$stmt= $pdo->prepare(SQLselectPassword($x)['sql']);
		$stmt->execute(SQLselectPassword($x)['data']);
		$passw = $stmt->fetch(); #only need to fetch first row to see if uname found
        $passw = $passw['passw'];
        return $passw; #if row array is empty no uname found
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
	}
}

function SQLselectPassword($uName) {
    $sql = "SELECT passw FROM users WHERE userName = :uName" ;
    $data= ['uName' => $uName];
    return ['sql' => $sql,'data' => $data];
}

###################################################
#VALIDATING USER DATA

# placeholder form that is used to create new users and to edit info of the existing users
function clearNewUserFormPlaceholders() {
	#form placeholders should be NULL on intital form display before submission	
		$placeHolders = [
						 '[+fName+]'=>'',
						 '[+fNameError+]'=>'',
						 '[+sName+]'=>'',
						 '[+sNameError+]'=>'',
						 '[+email+]'=>'',
						 '[+emailError+]'=>'',
						 '[+telephone+]'=>'',
						 '[+telephoneError+]'=>'',
						 '[+uName+]'=>'',
						 '[+uNameError+]'=>'',
						 '[+passw+]'=>'',
						 '[+passwError+]'=>'',
						 '[+adminSelected+]'=>'',
						 '[+academicSelected+]'=>'',
						 '[+typeError+]'=>'',
						];
		return $placeHolders;
	}

# this function is used to pass $parArray values as parameters to $funcArray functions by using array_map	
# it is called in the function (validateFormData) below
function func($function, $parameter) {
    $function($parameter);
}
#process the submitted form data in $formData array   
function validateFormData($formData, $pdo) {
		 # these variables are global as they are passed to the functions listed in $funcArray
        global $pdo;
		$formPlaceholders = clearNewUserFormPlaceholders();
        global $formPlaceholders; # array of placeholderss
        $cleanData = array();
        global $cleanData; #array to hold form data which passes validation
        $errors = array();
        global $errors;	  #array to hold error messages to display
        
		$funcArray = ['validfName', 'validsName', 'validEmail', 'validTelephone', 'validuName', 'validPassword', 'validType'];
		$parArray = [$formData['fName'], $formData['sName'], $formData['email'], $formData['telephone'], $formData['uName'], $formData['passw'], $formData['uType'],];

        array_map("func", $funcArray, $parArray);
        
        return [$cleanData,$errors, $formPlaceholders];
    }

function validfName($x) {
	
	global $formPlaceholders;
	global $cleanData;
	global $errors;
	
	#a name should be a-z or A-Z characters with uppercase first character
	if (ctype_alpha($x)) {
		$cleanData['fName'] = trim($x); #store in clean data array
		$formPlaceholders['[+fName+]'] = htmlentities($cleanData['fName']);
		$formPlaceholders['[+fNameError+]'] = "";
	} else {
		$errors['fName'] = "Alphabetical, no spaces with uppercase first character";
		$formPlaceholders['[+fName+]'] = $x; #show incorrect data in form field
		$formPlaceholders['[+fNameError+]'] = htmlentities($errors['fName']);
	}
}

function validsName($x) {
	
	global $formPlaceholders;
	global $cleanData;
	global $errors;
	
	#a name should be a-z or A-Z characters with uppercase first character
	if (ctype_alpha(str_replace('-', '', (str_replace(' ', '', $x))))) {
		$cleanData['sName'] = trim($x); #store in clean data array
		$formPlaceholders['[+sName+]'] = htmlentities($cleanData['sName']);
		$formPlaceholders['[+sNameError+]'] = "";
	} else {
		$errors['sName'] = "Alphabetic, space or -";
		$formPlaceholders['[+sName+]'] = $x; #show incorrect data in form field
		$formPlaceholders['[+sNameError+]'] = htmlentities($errors['sName']);
	}
}

function validEmail($x) {

	global $formPlaceholders;
	global $cleanData;
	global $errors;
	
#use PHP built in filter var function to check valid email
#https://www.php.net/manual/en/function.filter-var
	if ($x == "" || filter_var($x, FILTER_VALIDATE_EMAIL)) {
		$cleanData['email'] = trim($x);
		$formPlaceholders['[+email+]'] = htmlentities($cleanData['email']);
		$formPlaceholders['[+emailError+]'] = "";
	} else {
		$errors['email'] = "Invalid email format";
		$formPlaceholders['[+email+]'] = $x; #show incorrect data in form field
		$formPlaceholders['[+emailError+]'] = htmlentities($errors['email']);
	}
}

function validTelephone($x) {
	
	global $formPlaceholders;
	global $cleanData;
	global $errors;
	
	if ($x == "" || ctype_alnum($x)) {
		$cleanData['telephone'] = trim($x); #store in clean data array
		$formPlaceholders['[+telephone+]'] = htmlentities($cleanData['telephone']);
		$formPlaceholders['[+telephoneError+]'] = "";
	} else {
		$errors['telephone'] = "Only alphanumeric characters allowed";
		$formPlaceholders['[+telephone+]'] = $x; #show incorrect data in form field
		$formPlaceholders['[+telephoneError+]'] = htmlentities($errors['telephone']);
	}
}

function validuName($x) {

	global $formPlaceholders;
	global $cleanData;
	global $errors;
	global $pdo;

	if ((strlen($x) >= 8) and ctype_alnum($x) and uniqueUname($x, $pdo)){
		$cleanData['uName'] = trim($x);
		$formPlaceholders['[+uName+]'] = htmlentities($cleanData['uName']);
		$formPlaceholders['[+uNameError+]'] = "";
	} else {
		$errors['uName'] = " >=8 alphanumeric and UNIQUE";
		$formPlaceholders['[+uName+]'] = $x; #show incorrect data in form field
		$formPlaceholders['[+uNameError+]'] = htmlentities($errors['uName']);
	}
}

function validPassword($x) {

	global $formPlaceholders;
	global $cleanData;
	global $errors;
	
	#match a-z
	$containsLower  = preg_match('/[a-z]/', $x);
	#match A-Z
	$containsUpper  = preg_match('/[A-Z]/', $x); 
	#match any digit
	$containsDigit   = preg_match('/\d/', $x);	   
	#match list of escaped spacial characters of ! - < > £ $ % & * ~ #
	$containsSpecial = preg_match('/[!\-\<\>\£\$\%\&\*\~\#]/', $x); 
	if ((strlen($x) < 8 ) or (!$containsLower) or (!$containsUpper) or 
		(!$containsDigit) or (!$containsSpecial)) {
			$errors['passw'] = " not in the required passw format";
			$formPlaceholders['[+passw+]'] = ""; #clear incorrect data in form field
			$formPlaceholders['[+passwError+]'] = htmlentities($errors['passw']);
			
		} else {
			$cleanData['passw'] = trim($x);
			$formPlaceholders['[+passw+]'] = htmlentities($cleanData['passw']);
			$formPlaceholders['[+passwError+]'] = "";
		}
}

function validType($x) {
	global $formPlaceholders;
	global $cleanData;
	global $errors;	
#white list validation of person titles
	$valid_types = array('admin', 'academic');
	if (in_array($x, $valid_types)) {
		$cleanData['uType'] = trim($x); 

		switch ($cleanData['uType']) {
			case 'admin' :
				$formPlaceholders['[+adminSelected+]'] = 'selected';
				break;
			case 'academic' :
				$formPlaceholders['[+academicSelected+]'] = 'selected';
				break;
		}
		$formPlaceholders['[+typeError+]'] = "";
	} else {
		$errors['uType'] = " is not a valid type";
		$formPlaceholders['[+typeError+]'] = htmlentities($errors['uType']);
	}
}

##########################################################
function findPlaceholdersValues($data){
	
	$formPlaceholders1 = clearNewUserFormPlaceholders();
					
	$formPlaceholders1['[+fName+]'] = $data['firstName'];
	$formPlaceholders1['[+sName+]'] = $data['surname'];
	$formPlaceholders1['[+email+]'] = $data['email'];
	$formPlaceholders1['[+telephone+]'] = $data['telephone'];
	$formPlaceholders1['[+uName+]'] = $data['userName'];
	$formPlaceholders1['[+passw+]'] = $data['passw'];
	if ($data['userType'] == 'admin') {
		$formPlaceholders1['[+adminSelected+]'] = 'selected';
	} else { 
		$formPlaceholders1['[+academicSelected+]'] = 'selected';    
	}

	return $formPlaceholders1;
}

#############################################################
# checking if logged in admin has lost its access
function checkLostAccess($data, $formPlaceholders2, $formPlaceholders1) {
	if (($_SESSION['uName'] == $data) AND 
	($formPlaceholders1 == 'admin') AND 
	(array_key_exists('[+academicSelected+]', $formPlaceholders2))) {
		return True;
	} else {
		return False;
	}
}

##########################################################
# check if no errors were reported when finding the full name of a user
function checkFullName($name) {
	if ($name == 'error') {
		$fullName = '';
		$content = htmlParagraph("ERROR full name of the user could not be found.", False);
	} else {
		$fullName = $name;
		$content = '';
	}
	return [$fullName, $content];
}

##################################################
# delete row
function deleteUser($id, $pdo) {
	#deleting the selected user
	$deletion = SQLdeleteRow($id, $pdo);
	if ($deletion == "noError") {
		return $content = htmlParagraph("Chosen user was deleted.", False);
	} else {
		return $content = htmlParagraph("ERROR chosen user was not deleted.", False);
	}
}

###########################################################
#CREATING AND USING MODULE RESULT TABLES

# extracting data from the files
function getDataFromFiles() {
	$files = ['dt.csv','jv.csv','p1.csv']; #data files
	$data = array();

	foreach($files as $filename) { 
		$data[] = file('data/'.$filename,FILE_IGNORE_NEW_LINES);
	}
	return $data;
}

# should be set to True if new data should be inserted into database
function checkIfEmpty($pdo) {
	try {
		$data = $pdo->query(SelectAllFromModules())->fetchAll();
		if (empty($data)) {
			return 'True';
		} 
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return 'error';
	}   
}

function insertDataModuleResults($pdo) {
	$content = '';
	$files = getDataFromFiles(); # extracting data from files
	$insert = insertResults($files, $pdo); # inserting data into database
	if ($insert == 'error') {
		$content .= htmlParagraph("ERROR data was not inserted into the database.", False);
	} else{
		$content .= htmlParagraph("Data was inserted into the database.", False);
	}
	return $content;
}

function SelectAllFromModules() {
	return "SELECT *  
			FROM moduleResults;";	
}

# creating moduleResults table in the database
function SQLcreateModuleResultsTable() {
    $sql = "CREATE TABLE IF NOT EXISTS moduleResults (
            moduleCode varchar(2) NOT NULL,
            studentID varchar(8) NOT NULL,
            moduleResult int NOT NULL,
            PRIMARY KEY (moduleCode, studentID)  
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    return $sql;
}

# inserting data into moduleResults table in the database
function insertResults($data, $pdo) {

    foreach($data as $array){
        foreach($array as $row) {
            $row = explode(',', $row);
			
            try {
                $stmt= $pdo->query(INSERTintoModuleResultsTable($row));
            } catch (PDOException $e) {
                $errorCode = $e->getCode();
                $errorMessage = $e->getMessage();
                echo htmlParagraph("$errorCode : $errorMessage", False);
				return 'error';
            }            
        }
    }
}

function INSERTintoModuleResultsTable($values) {
	return "INSERT INTO moduleResults (moduleCode, studentID, moduleResult) 
			VALUES ('$values[0]', '$values[1]', '$values[2]');";
	
}

# printing out the tables
function printOutModuleResults($module, $pdo) {

    $content = '';

    try {
        $data1 = $pdo->query(SQLformattedModuleResults($module))->fetchAll();
        $data2 = $pdo->query(SQLformattedAverageMark($module))->fetchAll();
        $data3 = $pdo->query(SQLformattedCountMarks($module))->fetchAll();
        $data2[0] = array('Statistic' => 'Average Mark') + $data2[0];
        $data3[0] = array('Statistic' => 'TOTAL students') + $data3[0];
        $data = array_merge($data1, $data2, $data3);
    
        if (empty($data1)) {
            $content .= htmlParagraph("No customer records in the database for" . ' ' . TitleOfResults($module, False), False);
        } else { # TitleOfResults($module) function finds the title to be displayed above the table
        	 $content .= htmlTable($data, TitleOfResults($module, True), NULL, $pdo);
        }
		return $content;
    } catch (PDOException $e) {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        echo htmlParagraph("$errorCode : $errorMessage", False);
		return 'error';
    }
}

# creating module results tables to be printed out
function SQLformattedModuleResults($module) {
	return "SELECT 
              (SELECT (CASE WHEN moduleResult >= 70 THEN '1st'
                    WHEN moduleResult >= 60 THEN '2.1'
                    WHEN moduleResult >= 50 THEN '2.2'
                    WHEN moduleResult >= 45 THEN '3rd'
                    WHEN moduleResult >= 40 THEN 'Pass'
                    ELSE 'Fail'
                END)) AS Statistic, 
              (SELECT count(CASE 
                    WHEN moduleResult >= 70 THEN '1st'
                    WHEN moduleResult >= 60 THEN '2.1'
                    WHEN moduleResult >= 50 THEN '2.2'
                    WHEN moduleResult >= 45 THEN '3rd'
                    WHEN moduleResult >= 40 THEN 'Pass'
                    ELSE 'Fail'
                    END)
                )    AS Number
                FROM moduleResults
                WHERE moduleCode = '$module'
                GROUP BY Statistic
				ORDER BY FIELD(Statistic,'1st','2.2','2.1','3rd','Pass','Fail');";
                
}

function SQLformattedAverageMark($module) {
	return "SELECT 
            ROUND(AVG(moduleResult),0) as Number
            FROM moduleResults
            WHERE moduleCode = '$module';";                
}

function SQLformattedCountMarks($module) {
	return "SELECT COUNT(moduleResult) AS Number 
            FROM moduleResults
            WHERE moduleCode = '$module';";                
}

function TitleOfResults($module, $title) {

    switch($module) {
        case 'dt':
			if ($title) {
            	return htmlHeading('Database technology', 3);
			} else {
				return htmlentities('Database technology');
			}
        case 'jv':
			if ($title) {
            	return htmlHeading('Javascript', 3);
			} else {
				return htmlentities('Javascript');
			}
        case 'p1':
			if ($title) {
            	return htmlHeading('Web programming using php', 3);
			} else {
				return htmlentities('Web programming using php');
			}
    }
}

################################################################
#CREATING AND USING USERS TABLE IN THE DATABASE
function SQLcreateUserTable() {
	
	$sql = "CREATE TABLE IF NOT EXISTS `users` (
			`userID` int NOT NULL AUTO_INCREMENT,
			`firstName` varchar(30) NOT NULL,
		    `surname` varchar(45) NOT NULL,
			`email` varchar(45) DEFAULT NULL,
			`telephone` varchar(30) DEFAULT NULL,
			`userName` varchar(30) NOT NULL,
			`passw` varchar(15) NOT NULL,
            `userType` varchar(10) NOT NULL,
			PRIMARY KEY (`userID`),
            UNIQUE KEY (`username`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	return $sql;  
}   

function insertingNewUserToDatabase($x, $pdo) {
	
	try {
		$stmt= $pdo->prepare(INSERTintoUsersTable($x)['sql']);
		$stmt->execute(INSERTintoUsersTable($x)['data']);
		return "noError";
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
	}
}

# inserting data into users table in the database
function INSERTintoUsersTable($values) {
	$sql = "INSERT INTO users (firstname, surname, email, telephone, username, passw, userType) 
			VALUES (:firstName, :surname, :email, :telephone, :userName, :passw, :userType);";
	$data = [ 
				'firstName' => $values['fName'], 
				'surname' => $values['sName'],
				'email' => $values['email'],
                'telephone' => $values['telephone'],
				'userName' => $values['uName'], 
				'passw' => $values['passw'],
                'userType' => $values['uType'] 
			];
	return ['sql' => $sql,'data' => $data];
}

function updateUsersTable($x, $id, $pdo) {
	try {
		$stmt= $pdo->prepare(SQLUpdateUsersTable($x, $id)['sql']);
		$stmt->execute(SQLUpdateUsersTable($x, $id)['data']);
		return "noError";
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return false;
	}
}

# updating users table in the database
function SQLUpdateUsersTable($values, $id) {
    $sql = "UPDATE users SET firstname=:firstName, 
                    surname=:surname, 
                    email=:email,          
                    telephone=:telephone, 
                    username=:userName, 
                    passw=:passw, 
                    userType=:userType 
                    WHERE userID = '$id';";
	$data = [ 
				'firstName' => $values['fName'], 
				'surname' => $values['sName'],
				'email' => $values['email'],
                'telephone' => $values['telephone'],
				'userName' => $values['uName'], 
				'passw' => $values['passw'],
                'userType' => $values['uType'] 
			];
	return ['sql' => $sql,'data' => $data];
}

# creating initial admin user to log in
function createInitialUser($admin, $pdo){
	try {
		$stmt= $pdo->prepare(INSERTintoUsersTable($admin)['sql']);
		$stmt->execute(INSERTintoUsersTable($admin)['data']);
		#display form data successfully written to customers table message
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return false;
	}
}

# deleting a row from the users table in the databases	
function SQLdeleteRow($id, $pdo) {
	try {
		#define prepared SQL statement to write new customer record
		#define associative array to hold data for the SQL
		#prepare and execute using PDO methods
		$stmt= $pdo->prepare(deleteRow($id)['sql']);
		$stmt->execute(deleteRow($id)['data']);
		return "noError";
		#display form data successfully written to customers table message
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return false;
	}	
			
}

function deleteRow($id) {
	$sql = "DELETE FROM users WHERE userID=:id;";
	$data = [
		'id' => $id
	];
	return ['sql' => $sql, 'data' => $data];
}

# checking if the user name chosen is unique
function uniqueUname($x, $pdo) {
	try {
		$stmt= $pdo->prepare(SQLselectUsername($x)['sql']);
		$stmt->execute(SQLselectUsername($x)['data']);
		$row = $stmt->fetch(); # only need to fetch first row to see if uname was found
        return (empty($row)) ? true : false; # if row array is empty no uname found
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return "error";
	}
}

function SQLselectUsername($uName) {
	#determines if a given $uname exits in the customers table
	$sql = "SELECT userName FROM users WHERE userName = :uName;";
	$data = ['uName' => $uName]; 
	return ['sql' => $sql,'data' => $data];
}

# checking user types
function userType($x, $pdo) {
	try {
		$stmt= $pdo->prepare(SQLselectUserType($x)['sql']);
		$stmt->execute(SQLselectUserType($x)['data']);
		$uType = $stmt->fetch(); #only need to fetch first row to see if uname found
        return ($uType['userType'] == 'admin') ? "True" : "False"; #if row array is empty no uname found
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return "error";
	}
}

function SQLselectUserType($uName) {
    $sql = "SELECT userType FROM users WHERE userName = :uName" ;
    $data= ['uName' => $uName];
    return ['sql' => $sql,'data' => $data];
}

# finding full name of a user 
function userFullName($x, $pdo) {
	try {
		$stmt= $pdo->prepare(SQLselectFullName($x)['sql']);
		$stmt->execute(SQLselectFullName($x)['data']);
		$fullName [] = $stmt->fetch();
        return $fullName[0]['firstName'] . ' ' . $fullName[0]['surname']; #if row array is empty no uname found
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return 'error';
	}
}

function SQLselectFullName($uName) {
    
	$sql = "SELECT firstName, surname FROM users WHERE userName = :uName";
    $data = ['uName' => $uName];
	return ['sql' => $sql,'data' => $data];       
}

########################################################
# CREATING ADMINUSER VIEW TABLE
function selectAllUsers($pdo) {
	try {
		$stmt = $pdo->query(SQLFormattedUserAdminTable())->fetchAll();
		return $stmt;
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return 'error';
	}
}

function SQLFormattedUserAdminTable() {
    return  "SELECT CONCAT(firstName, ' ', surname) AS Name,
                    userID  AS View,
                    userID AS Edit,
                    userID AS `Delete`
            FROM users;";
           
}

#########################################################
# getting data from the database for viewUser page 
function SQLformattedViewUsers($x, $pdo) {
	try {
		$stmt= $pdo->prepare(usersView($x)['sql']);
		$stmt->execute(usersView($x)['data']);
		$view = $stmt->fetch();
		if (empty($view)) {
			return 'error';
		} else {
        	return $view;
		} 
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
		return 'error';
	}
}

function usersView($id) {
    $sql = "SELECT *
            FROM users
            WHERE userID = :id;";
	$data= ['id' => $id];
    return ['sql' => $sql,'data' => $data];
}

############################################################################
# checking which users cannot be deleted by the user logged in; 
#initial admin and currently logged in users cannot be deleted 
function userID($pdo) {
	try {
		$id = array();
		$stmt1= $pdo->prepare(SQLselectUserID($_SESSION['uName'])['sql']);
		$stmt1->execute(SQLselectUserID($_SESSION['uName'])['data']);
		$id1 = $stmt1->fetch(); #only need to fetch first row to see if uname found
		$id1 = $id1['userID'];
		
		$stmt2= $pdo->prepare(SQLselectInitialAdminID()['sql']);
		$stmt2->execute(SQLselectInitialAdminID()['data']);
		$id2 = $stmt2->fetch(); #only need to fetch first row to see if uname found
        $id2 = $id2['userID'];
		array_push($id, $id1, $id2);
		return $id;
	} catch (PDOException $e) {
		$errorCode = $e->getCode();
		$errorMessage = $e->getMessage();
		echo htmlParagraph("$errorCode : $errorMessage", False);
	}
}

function SQLselectUserID($uName) {
    $sql = "SELECT userID FROM users WHERE userName = :uName";
    $data= ['uName' => $uName];
    return ['sql' => $sql,'data' => $data];
}

function SQLselectInitialAdminID() {
	$sql = "SELECT userID FROM users WHERE userName = :admin";
	$data = ['admin' => 'ubadmin01'];
	return ['sql' => $sql,'data' => $data];
}

##################################################
#CREATING A TABLE TO BE DISPLAYED ON THE PAGE
function htmlTable($data, $title, $links, $pdo) { # if $links parameter is set to True it means that userAdmin page table is being created 
    $linksArray = array('viewUser', 'editUser', 'deleteUser'); # array used to determine which link to uses
	//build HTML table from 2D data array
    $html = '';
	$html .= '<table>' . PHP_EOL;
   
    $html .= '<caption>' . $title . '</caption>';
	# create table header from the first row of data
	$html .= "\t<tr>"; # tab one indent in for table data and create table row tag
	foreach($data[0] as $key => $value) { # create each <th> element
		$key = htmlentities($key); 
		$html .= "<th>$key</th>";
	}
	$html .= "</tr>" . PHP_EOL; 
	# create table data rows
	foreach($data as $row) {
        $i = 0; # index to go through $linkArray
		$html .= "\t<tr>"; 
		foreach($row as $value) { # create each <td> element
			# 1st column (the name) is just printed out; 2nd, 3rd and 4th columns containing ID of a selected user need to be processed first
            if (is_numeric($value) and $links == True) {
				if (($i == 2 && in_array($value, userID($pdo)))) { # checking if the user can be deleted
					$data = file_get_contents('html/noDelete.html');
				} elseif ($i == 2) { # creating delete user link
					$value = $value;
					$placeholders = array(); #define array of HTML placeholders that are found in the template 
					$placeholders[] = '[+value+]';
					$values = array(); #array of variables to replace the HTML placeholders with
					$values[] = $value; # user that can be deleted id
					$template = file_get_contents('html/popUp.html');
					$data = str_replace($placeholders, $values, $template);	
				} else { # creating editUser and viewUser links
					$action = $linksArray[$i];
					$value = $value;
			
					$placeholders = array(); #define array of HTML placeholders that are found in the template 
					$placeholders[] = '+action+';
					$placeholders[] = '+value+';
					$values = array(); #array of variables to replace the HTML placeholders with
					$values[] = $action; # viewUser or editUser
					$values[] = $value; # id of a user that is being edited or deleted
					$template = file_get_contents('html/viewEdit.html');
					$data = str_replace($placeholders, $values, $template);
					
				}
				$i++;    
            } else {
                $data = htmlentities($value);   
            }
			$html .= "<td>$data</td>";
		}
		$html .= "</tr>" . PHP_EOL; 
	}
    
	$html .= "</table>" . PHP_EOL;
	return $html; 
}

################################################
#SETTING HEADINGS
function htmlHeading($text, $level) {
	$heading = trim($text);
	switch ($level) {
		case 1 :
		case 2 :
			$heading = ucwords($heading);
			break;
		case 3 :
		case 4 :
		case 5 :
		case 6 :
			$heading = ucfirst($heading);
			break;
		default: #traps unknown heading level exception
			$heading = '<FONT COLOR="#ff0000">Unknown heading level:' . $level . '</FONT>';
		}
	return '<h' . $level . '>' . htmlentities($heading) . '</h' . $level .  '>';
}

##############################################
#FORMATTING TEXT TO BE DISPLAYED ON THE PAGE
function htmlParagraph($text, $inline) {
	if ($inline) {
		return '<p id="inline">' . htmlentities(trim($text)) . '</p>';
	}
	return '<p>' . htmlentities(trim($text)) . '</p>';
}

##############################################
#CREATING BUTTON LINK AT THE BOTTOM OF THE PAGE
function addButton($link) {
	$button = '';
	$placeholders = array(); #define array of HTML placeholders that are found in the template 
	$placeholders[] = '+link+';
	$values = array(); #array of variables to replace the HTML placeholders with
	$values[] = $link;
	$template = file_get_contents('html/goBack.html');
	$button = str_replace($placeholders, $values, $template);
	return $button;
}

function myAutoloader($class){
    // Construct path to the class file
    include 'classes/' . $class . '.php';
}

?>