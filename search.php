<?php
session_start();

require 'query/search_query.php';

$results = null;
$search = '';
$page = 1;
$resultsPerPage = 10;

if(isset($_GET['s']))
	$search = trim($_GET['s']);
if(isset($_GET['page']))
	$page = (int) $_GET['page'];
if($page < 1)
	$page = 1;

$results = search($search, $page, $resultsPerPage);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD
<?php
if($search != '') {
	echo " - " . htmlspecialchars($search);
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php require 'header.php'; ?>
<article>
	<h1>Indiana Music Business Directory</h1>
</article>
<article>
<?php
if(isset($results['Error'])) {
	echo "<p>Could not connect to the database</p>";
}
else {
	$count = 0;
	foreach($results as $result) {
		if(isset($result['Count'])) {
			$count = $result['Count'];
		}
		else {
			$id = htmlspecialchars($result['Id']);
			$name = htmlspecialchars($result['Name']);
			$type = htmlspecialchars($result['Type']);
			$description = htmlspecialchars($result['Description']);
			
			echo "<h3><a href='listing.php?id={$id}'>{$name} ({$type})</a></h3>\n<p>{$description}</p>\n";
		}
	}
	
	if($count === 0) {
			echo "<p>No results were found.</p>";
	}
	if($page > 1) {
		$prevPage = $page - 1;
		$prevLink = "search.php?s={$search}&amp;page={$prevPage}";
		echo "<a href='{$prevLink}'>Previous Page</a> ";
	}
	if($count === $resultsPerPage) {
		$nextPage = $page + 1;
		$nextLink = "search.php?s={$search}&amp;page={$nextPage}";
		echo "<a href='{$nextLink}'>Next Page</a>";
	}
}
?>
</article>
<footer>
This is the footer.
</footer>
</body>
</html>
