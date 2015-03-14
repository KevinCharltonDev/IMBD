<?php
session_start();

require 'query/search_query.php';
require 'connect/config.php';
require 'functions.php';

$results = null;
$search = '';
$searchloc = '';
$page = 1;
$resultsPerPage = 10;

if(isset($_GET['search']))
	$search = trim($_GET['search']);
if(isset($_GET['location']))
	$searchloc = trim($_GET['location']);
if(isset($_GET['page']))
	$page = (int) $_GET['page'];
if($page < 1)
	$page = 1;

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$results = search($conn, $search, $searchloc, $page, $resultsPerPage);
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD
<?php
if($search != '') {
	echo " Search: " . htmlspecialchars($search);
}
?>
</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php
if(isset($results['Error'])) {
	printError($results["Message"]);
}
else {
	$count = count($results);
	if($count === 0)
		printMessage("No results were found.");
	
	echo "<div class='content'>\n";
	foreach($results as $result) {
		$id = htmlspecialchars($result['Id']);
		$name = htmlspecialchars($result['Name']);
		$type = htmlspecialchars(spTypeToString($result['Type']));
		$description = htmlspecialchars($result['Description']);
		
		echo "<h3><a href='listing.php?id={$id}'>{$name} ({$type})</a></h3>\n<div class = 'ListingBounds'>";
		echo "<p>{$description}</p>\n";
		echo "</div>";
		
	}
	
	if($page > 1) {
		$prevPage = $page - 1;
		$prevLink = htmlspecialchars("search.php?search={$search}&location={$searchloc}&page={$prevPage}");
		echo HTMLTag::create("a")->attribute("href", $prevLink)->innerHTML("Previous Page")->html();
		echo '&nbsp;&nbsp;';
	}
	if($count === $resultsPerPage) {
		$nextPage = $page + 1;
		$nextLink = htmlspecialchars("search.php?search={$search}&location={$searchloc}&page={$nextPage}");
		echo HTMLTag::create("a")->attribute("href", $nextLink)->innerHTML("Next Page")->html();
	}
	echo "</div>\n";
}
?>
</section>
</body>
</html>
