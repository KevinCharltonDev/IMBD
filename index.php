<?php
session_start();

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Indiana Music Business Directory</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
<link href="css/custom.css" rel="stylesheet" type="text/css">
<link href="css/media.css" rel="stylesheet" type="text/css">
<script src="js/functions.js"></script>
<script src="js/validate.js"></script>
</head>
<body>
<?php require 'php/header.php'; ?>
<section>
<h2>Search</h2>
<div class="content">
<form action="search.php" method="GET" id="searchForm">
<table>
<tr>
	<td>Business Name or Description: </td>
	<td><input type="text" name="search"></td>
</tr>
<tr>
	<td>Location: </td>
	<td><input type="text" name="location"></td>
</tr>
</table>
<input type="submit" value="Search">
</form>
<script type="text/javascript">
document.getElementById('searchForm').onsubmit = function() {
	onlySendNonEmptyInput('searchForm');
};
</script>
</div>
</section>
</body>
</html>
