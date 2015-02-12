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

<header>
	<div class="left">
	Home
	</div>
	<div class="right">
	Sign in
	</div>
</header>
<article>
	<h1>Indiana Music Business Directory</h1>
	<section>
	<h2>Title for text below</h2>
	<p>Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. Some text will go here. </p>
	</section>
	<section>
	<h2>Search</h2>
	<form action="search.php" method="GET">
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
