<?php
	$viewHeading = "ERROR";
	$id = $_GET['view'];
	$content = htmlParagraph('404 error - ' . $id . ' view key not known', False);
?>