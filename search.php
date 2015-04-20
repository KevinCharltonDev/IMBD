<?php
session_start();

require 'query/search_query.php';
require 'query/service_query.php';
require 'connect/config.php';
require 'php/functions.php';
require 'php/data.php';

$_search = isset($_GET['search']) ? trim($_GET['search']) : '';
$_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$_services = isset($_GET['services']) ? trim($_GET['services']) : '';

function pageUrl($search, $services, $page) {
	return "search.php?search={$search}&services={$services}&page={$page}";
}

if($_page < 1)
	$_page = 1;

$resultsPerPage = 20;

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "account.php";
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$allServices = getAllServices($conn);
$selectedServices = isset($_GET['services']) ?
	array_map("intval", explode('-', $_services)) :
	array();

$results = searchAll($conn, $_search, $selectedServices, $_page, $resultsPerPage);
$count = count($results);
for($i = 0; $i < $count; $i++) {
	$id = (int) $results[$i]['Sp_Id'];
	$results[$i]['Services'] = getServices($conn, $id);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>
<?php
echo $_search != '' ? htmlspecialchars($_search) . " - Search" : "Search";
?>
</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
<script src="js/validate.js"></script>
</head>
<body>
<?php require 'php/header.php'; ?>
<section>
<?php
if(isset($results['Error'])) {
	printError($results["Message"]);
}
?>
<h2>Search</h2>
<div class="content">
<form action="search.php" method="GET" id="searchForm">
<input type="text" name="search" value="<?php echo htmlspecialchars($_search); ?>">
<?php
$table = new HTMLTable();
	$table->setClass("services");
	$rowCount = 0;
	foreach($allServices as $service) {
		$input = HTMLTag::create("input", true, true)->
			attribute("type", "checkbox")->
			attribute("value", htmlspecialchars($service['S_Id']));
			
		if(in_array($service['S_Id'], $selectedServices)) {
			$input->attribute("checked", "checked");
		}
			
		$table->cell($input->html() . htmlspecialchars($service['Name']));
			
		if($rowCount % 3 == 2)
			$table->nextRow();
		$rowCount++;
	}
	
	echo $table->html();
?>
<input type="hidden" name="services" value="" id="selectedServices">
<input type="submit" value="Search">
</form>
<script type="text/javascript">
document.getElementById('searchForm').onsubmit = function() {
	combineCheckboxValues('searchForm', 'selectedServices');
	onlySendNonEmptyInput('searchForm');
};
</script>
</div>
</section>
<section>
<h2>Results</h2>
<div class="content">
<?php
if($count === 0) {
	echo "<p>No results were found.</p>";
}

foreach($results as $result) {
	$id = (int) $result['Sp_Id'];
	$name = htmlspecialchars($result['Name']);
	$type = businessTypeString($result['Type']);
	$description = htmlspecialchars($result['Description']);
	
	echo "<h3><a href='listing.php?id={$id}'>{$name} ({$type})</a></h3>\n<div class = 'ListingBounds'>";
	echo $description;
	
	if(count($result['Services']) > 0)
		echo '<div style="font-style: italic;margin-top: 10px;">';
	
	for($i = 0; $i < count($result['Services']); $i++) {
		$name = htmlspecialchars($result['Services'][$i]['Name']);
		if($i > 0)
			echo " | ";
		
		echo $name;
	}
	
	if(count($result['Services']) > 0)
		echo "</div>";
	
	echo "</div>";
	
}

if($_page > 1) {
	$prevPage = $_page - 1;
	$prevLink = htmlspecialchars(pageUrl($_search, $_services, $prevPage));
	echo HTMLTag::create("a")->attribute("href", $prevLink)->innerHTML("&lt; Previous")->html();
	echo '&nbsp; ';
}
if($count === $resultsPerPage) {
	$nextPage = $_page + 1;
	$nextLink = htmlspecialchars(pageUrl($_search, $_services, $nextPage));
	echo HTMLTag::create("a")->attribute("href", $nextLink)->innerHTML("Next &gt;")->html();
}
?>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>
