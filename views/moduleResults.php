<?php
$viewHeading = htmlHeading("Module results",2);
$content = '';
$tables = '';
$modules = ['dt', 'jv', 'p1'];
        

	# creating tables to print out
	foreach ($modules as $module) {
		$table =  printOutModuleResults($module, $pdo);
		if ($table != 'error') {
			$tables .= $table;
		} else {
			$tables .= htmlParagraph("ERROR" . TitleOfResults($module) . "table was not printed", False);
		}
	}

	$placeholders[] = '[+tables+]';
	$values[] = $tables;

	$template = file_get_contents('html/moduleResults.html'); 
	$content .= str_replace($placeholders, $values, $template); # creating a page view

?>