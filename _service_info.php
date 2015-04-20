<?php 
session_start();

if(!isset($_SESSION['Email']) || $_SESSION['Type'] !== 2) {
	header("HTTP/1.0 404 Not Found");
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<style type="text/css">
h3 {
	margin: 0;
	padding: 0;
	text-decoration: underline;
}
h4 {
	margin: 0;
	padding: 0;
	font-weight: normal;
	font-style: italic;
}
div {
	margin-left: 20px;
	margin-top: 0;
	maring-bottom: 0;
}

body {
	-webkit-column-count: 2; /* Chrome, Safari, Opera */
	-moz-column-count: 2; /* Firefox */
	column-count: 2;
	-webkit-column-rule-style: solid; /* Chrome, Safari, Opera */
	-moz-column-rule-style: solid; /* Firefox */
	column-rule-style: solid;
}

p {
	padding: 0;
	margin: 0;
}
</style>
</head>
<body>
<p style="font-weight: bold;">Refresh this page when services or service fields are added or deleted to update the information.</p><br><br>
<?php
require 'connect/config.php';
require 'query/service_query.php';

function serviceTypeToString($type) {
	$array = array("Yes / No", "Text up to 60 characters", "Text up to 255 characters",
		"Integer", "Decimal Number", "One value from a list of possible values / other values not allowed",
		"One value from a list of possible values / other values not allowed", "Many values from a list of possible values / other values not allowed",
		"Many values from a list of possible values / other values allowed");
		
	return $array[$type];
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$serviceMetadata = getAllServiceMetadata($conn);
$allServices = getAllServices($conn);
$count = count($serviceMetadata);
$index = 0;

echo "<h3>All Services</h3>";
foreach($allServices as $service) {
	echo $service['Name'] . " | ";
}
echo "<br><hr>";

foreach($serviceMetadata as $serviceName => $serviceData) {
	echo "<h3>{$serviceName}</h3>";
	echo "<p>" . $serviceData['Description'] . "</p>";
	
	echo "<div>";
	foreach($serviceData['Columns'] as $columnName => $columnData) {
		echo "<h4>{$columnName}</h4>";
		echo "<div>";
		echo trim($columnData['Description']) !== "" ? "Description: " . $columnData['Description'] . "<br>" : "";
		echo "Type: " . serviceTypeToString($columnData['Type']) . "<br>";
		
		if($columnData['Type'] > 4)
			echo "Possible Values Key: " . $columnData['PossibleValuesKey'];
		
		echo "</div>";
	}
	echo "<br>";
	echo "</div>";
}

echo "<br><br>";

$sql = "SELECT * FROM POSSIBLE_VALUES ORDER BY `Key`";
$results = $conn->query($sql);
echo "<hr>";
echo "<h3>Possible Value Lists</h3>";
$lastKey = "";
while($row = $results->fetch_assoc()) {
	if($row['Key'] != $lastKey)
		echo "<br><h4>" . $row['Key'] . "</h4>";
	echo "<div>" . $row['Value'] . "</div>";
	
	$lastKey = $row['Key'];
}

$results->close();

$conn->close();
?>
</body>
</html>