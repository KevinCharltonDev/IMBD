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
<?php require 'header.php'; ?>
<article>
	<h1>Indiana Music Business Directory</h1>
	<section>
	<h2>Title for text below</h2>
	<p>Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. </p>
	</section>
	<section>
	<div class = "searchbar">
	<h2 style="position:relative; left:8px">Search</h2>
	</div>
	<form action="search.php" method="GET">
		<br>By business name/description.<br> 
		<input type="text" name="s"><br>
		<input type="submit" value="Search">
		<input type="hidden" name="page" value="1">
	</form>
	</section>
</article>
<footer>
This is the footer.
</footer>
</body>
</html>
