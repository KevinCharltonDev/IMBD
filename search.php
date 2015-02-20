<?php
session_start();

require 'query/search_query.php';

$results = null;
$search = '';
$searchloc = '';
$page = 1;
$resultsPerPage = 10;

if(isset($_GET['s']))
	$search = trim($_GET['s']);
if(isset($_GET['l']))
	$searchloc = trim($_GET['l']);
if(isset($_GET['page']))
	$page = (int) $_GET['page'];
if($page < 1)
	$page = 1;

$results = search($search, $searchloc, $page, $resultsPerPage);
?>

<script type = "text/javascript">
				function reviewBox(id){
					var string = id;
					var input = document.getElementById(id);
					 if(input.style.display == 'block')
						input.style.display = 'none';
					else
						input.style.display = 'block';
				}
</script>

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
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<?php
require 'functions.php';
require 'print_error.php';

if(isset($results['Error'])) {
	printErrorFromCode($results["Code"]);
}
else {
	$count = count($results);
	
	echo "<div class='content'>\n";
	foreach($results as $result) {
		$id = htmlspecialchars($result['Id']);
		$name = htmlspecialchars($result['Name']);
		$type = htmlspecialchars(spTypeToString($result['Type']));
		$description = htmlspecialchars($result['Description']);
		
		echo "<h3><a href='listing.php?id={$id}'>{$name} ({$type})</a></h3>\n<div class = 'ListingBounds'>";
		echo "<p>{$description}</p>\n";
		echo "</div><h4 onclick = 'reviewBox(\"{$id}\")' style='position:relative; bottom:20px; left:16px'><u>Review this listing</u></h4>";
		echo "<form id = '{$id}' style='margin-left:24px' hidden><textarea rows='8' cols ='30'></textarea><br><input type='submit' value='Submit'></form>";
		
	}
	
	if($page > 1) {
		$prevPage = $page - 1;
		$prevLink = htmlspecialchars("search.php?s={$search}&page={$prevPage}");
		echo '<a href="' . $prevLink . '">Previous Page</a> &nbsp;&nbsp;';
	}
	if($count === $resultsPerPage) {
		$nextPage = $page + 1;
		$nextLink = htmlspecialchars("search.php?s={$search}&page={$nextPage}");
		echo '<a href="' . $nextLink . '">Next Page</a>';
	}
	echo "</div>\n";
}
?>
</section>
</body>
</html>
