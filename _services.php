<?php 
session_start();

if(!isset($_SESSION['Email']) || $_SESSION['Type'] !== 2) {
	header("HTTP/1.0 404 Not Found");
	exit;
}
?>
<!DOCTYPE html>
<html>
<body>
<p>
<a href="_service_info.php" target="_blank">Service Info</a><br>
<a href="_service_data.php" target="_blank">Service Data</a><br>
<a href="_add_service.php" target="_blank">Add</a><br>
<a href="_delete_service.php" target="_blank">Delete</a><br>
<a href="_possible_value_lists.php" target="_blank">Lists</a>
</p>
</body>
</html>