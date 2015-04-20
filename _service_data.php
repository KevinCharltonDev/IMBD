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

p {
	padding: 0;
	margin: 0;
}

table {
	border-collapse: collapse;
}

td {
	border: 1px solid black;
}

th {
	background-color: rgb(220, 220, 220);
	border: 1px solid black;
}
</style>
</head>
<body>
<p style="font-weight: bold;">Refresh this page when services or service fields are added or deleted to update the information.</p><br><br>
<?php
require 'connect/config.php';
require 'query/service_query.php';
require 'php/functions.php';

function serviceTypeToString($type) {
	$array = array("Yes / No", "Text up to 60 characters", "Text up to 255 characters",
		"Integer", "Decimal Number", "One value from a list of possible values",
		"One value from a list of possible values", "Many values from a list of possible values",
		"Many values from a list of possible values / other");
		
	return $array[$type];
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);
$allServices = getAllServices($conn);

foreach($allServices as $service) {
	echo "<h3>{$service['Name']}</h3>";
	$sql = "CALL GetServiceData('{$service['Name']}', null)";
	$results = $conn->query($sql);
	echo "<table>";
	echo "<tr>";
	$fields = $results->fetch_fields();
	foreach($fields as $field) {
		echo "<th>" . $field->name . "</th>";
	}
	echo "</tr>";

	while($row = $results->fetch_row()) {
		echo "<tr>";
		foreach($row as $value) {
			echo "<td>" . htmlspecialchars($value) . "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "<br><br>";

	if($conn->more_results()) {
		$conn->next_result();
	}
}

$conn->close();
?>
</body>
</html>