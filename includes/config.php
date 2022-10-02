<?php
$host = 'mysqlsrv.dcs.bbk.ac.uk';
$db   = 'asirei01db'; 	
$user = 'asirei01';		
$pass = 'bbkmysql';		
$charset = 'utf8mb4';	

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];


try {
	$pdo = new PDO($dsn, $user, $pass, $options);
	$databaseConnected = true;

	# checking if the databse is empty, to insert the information if it is
	$checkIfEmpty = checkIfEmpty($pdo);	
	if ($checkIfEmpty == 'True') {
		$content .= insertDataModuleResults($pdo);
	} else if ($checkIfEmpty == 'error') {
		$content .= htmlParagraph("ERROR database could not be checked.", False);
	} 	
	
	$stmt = $pdo->query(SQLcreateUserTable()); #PDO query method to create table from given sql
	$stmt = $pdo->query(SQLcreateModuleResultsTable());
} catch (PDOException $e) {
	$databaseConnected = false;
	$errorCode = $e->getCode();
	$errorMessage = $e->getMessage();
	$content = htmlParagraph("$errorCode : $errorMessage", False);
	echo $content;
}
?>