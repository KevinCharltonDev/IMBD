<?php 
session_start();

require 'connect/config.php';
require 'query/service_query.php';
require 'php/functions.php';

function serviceTypeToString($type) {
	$array = array("Yes / No", "Text up to 60 characters", "Text up to 255 characters",
		"Integer", "Decimal Number", "* One value from a list of possible values / other values not allowed",
		"* Do not use this option, but same as option above", "* Many values from a list of possible values / other values not allowed",
		"* Many values from a list of possible values / other values allowed");
		
	return $array[$type];
}

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(!isset($_SESSION['Email']) || $_SESSION['Type'] !== 2) {
	header("HTTP/1.0 404 Not Found");
	exit;
}


if(isPostSet('service', 'column', 'description', 'type', 'possiblevalues')) {
	$serviceName = $_POST['service'];
	$columnName = $_POST['column'];
	$description = $_POST['description'];
	$type = (int) $_POST['type'];
	$possibleValuesKey = $_POST['possiblevalues'];
	
	if($possibleValuesKey === '' && $type >= 5) {
		redirect("_add_service.php");
		exit;
	}
	
	addColumn($conn, $serviceName, $columnName, $description, $type, $possibleValuesKey);
	redirect("_add_service.php");
	exit;
}
else if(isPostSet('service', 'description')) {
	$serviceName = $_POST['service'];
	$description = $_POST['description'];
	
	createService($conn, $serviceName, $description);
	redirect("_add_service.php");
	exit;
}


$allServices = getAllServices($conn);
$serviceDropDown = new HTMLDropDown("service");
foreach($allServices as $service) {
	$serviceDropDown->option($service['Name'], $service['Name']);
}

$sql = "SELECT DISTINCT `Key` FROM POSSIBLE_VALUES";
$results = $conn->query($sql);
$possibleValueDropDown = new HTMLDropDown("possiblevalues");
$possibleValueDropDown->option("None", "");

while($row = $results->fetch_row()) {
	$possibleValueDropDown->option($row[0], $row[0]);
}

$conn->close();

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8" />
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

a {
	text-decoration: none;
}

a:hover {
	color: green;
}
</style>
</head>
<body>
<?php

echo "<h3>Add a Service</h3>";
echo "<p>Make sure this service is not already in the database. ( <a href='_service_info.php'>_service_info.php</a> )<br>Description is shown on the service page when entering data.<br>Leave it blank if you don't want a description to be printed on that page.</p>";
echo "<form action='_add_service.php' method='POST'>";
echo "<br>Name (up to 60 characters)<br>";
echo "<input type='text' name='service'>";
echo "<br><br>";
echo "Description (up to 255 characters)<br>";
echo "<textarea name='description'></textarea><br>";
echo "<input type='submit' value='Add'>";
echo "</form>";
echo "<br><hr>";

echo "<h3>Add a Service Field</h3>";
echo "<p>This description field does not show up on the website.  It is just for notes.</p>";
echo "<form action='_add_service.php' method='POST'>";
echo "<br>Choose a Service<br>";
echo $serviceDropDown->html();
echo "<br><br>";
echo "Name (up to 60 characters)<br>";
echo "<input type='text' name='column'>";
echo "<br><br>";
echo "Description (up to 255 characters)<br>";
echo "<textarea name='description'></textarea><br><br>";
echo "Data Type<br>";
echo "<select name='type'>";
for($i = 0; $i <= 8; $i++) {
	echo "<option value='{$i}'>" . serviceTypeToString($i) . "</option>";
}
echo "</select><br><br>";
echo "Possible Values Key ( values are listed on <a href='_service_info.php'>_service_info.php</a> )<br>";
echo "If selected data type begins with *, a key must be chosen.<br>";
echo "If data type does not begin with a *, select 'None'.<br>";
echo $possibleValueDropDown->html();
echo "<br><br>";
echo "<input type='submit' value='Add'>";
echo "</form>";

echo "<br><br>";
?>
<script type="text/javascript">
var forms = document.getElementsByTagName("form");
for(var i = 0; i < forms.length; i++) {
	forms[i].onsubmit = function() { return confirm("Are you sure you want to add this?"); };
}
</script>
</body>
</html>