<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD</title>
<link href="css/default.css" rel="stylesheet" type="text/css">
</head>
<body>
<h1>Indiana Music Business Directory</h1>
<?php require 'header.php'; ?>
<section>
<h2>Search</h2>
<div class="content">
<form action="search.php" method="GET">
	By business name/description.<br> 
	<input type="text" name="s"><br>
	<input type="submit" value="Search">
	<input type="hidden" name="page" value="1">
</form>
</div>
</section>
</body>
</html>
