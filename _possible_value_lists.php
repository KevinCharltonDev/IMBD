<?php 
session_start();

require 'connect/config.php';
require 'query/service_query.php';
require 'php/functions.php';

$conn = new mysqli(SERVER_NAME, NORMAL_USER, NORMAL_PASSWORD, DATABASE_NAME);

if(isPostSet('key', 'values', 'action')) {
	$key = mysqli_real_escape_string($conn, $_POST['key']);
	$values = separate($_POST['values'], ',');
	
	if($_POST['action'] === 'add') {
		foreach($values as $value) {
			addPossibleValue($conn, $key, $value);
		}
	}
	else if($_POST['action'] === 'delete') {
		foreach($values as $value) {
			deletePossibleValue($conn, $key, $value);
		}
	}
	
	redirect("_possible_value_lists.php");
	exit;
}

if(!isset($_SESSION['Email']) || $_SESSION['Type'] !== 2) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

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
</style>
</head>
<body>
<?php
$results = $conn->query("SELECT DISTINCT `Key` FROM POSSIBLE_VALUES");
$listsDropDown = new HTMLDropDown("key");

while($row = $results->fetch_row()) {
	$listsDropDown->option($row[0], $row[0]);
}

$results->close();

echo "<h3>Create a New List</h3>";
echo "<p>Make sure the list name does not exist.<br>If it exists, the values will be added to that list instead of a new list.</p><br>";
echo "<form action='_possible_value_lists.php' method='POST'>";
echo "List Name (key)<br>";
echo "<input type='text' name='key'><br><br>";
echo "Values (separate with commas)<br>";
echo "<textarea name='values'></textarea>";
echo "<input type='hidden' name='action' value='add'><br><br>";
echo "<input type='submit' value='Create List'>";
echo "</form>";

echo "<br><hr>";

echo "<h3>Add Values to an Existing List</h3>";
echo "<form action='_possible_value_lists.php' method='POST'>";
echo "Choose a list<br>";
echo $listsDropDown->html();
echo "<br><br>";
echo "Values (separate with commas)<br>";
echo "<textarea name='values'></textarea>";
echo "<input type='hidden' name='action' value='add'><br><br>";
echo "<input type='submit' value='Add Values'>";
echo "</form>";

echo "<br><hr>";

echo "<h3>Delete Values from a List</h3>";
echo "<form action='_possible_value_lists.php' method='POST'>";
echo "Choose a list<br>";
echo $listsDropDown->html();
echo "<br><br>";
echo "Values (separate with commas)<br>";
echo "<textarea name='values'></textarea>";
echo "<input type='hidden' name='action' value='delete'><br><br>";
echo "<input type='submit' value='Delete Values'>";
echo "</form>";

$conn->close();
?>
</body>
</html>