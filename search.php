<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>IMBD
<?php
if(isset($_GET["s"])) {
	echo " - " . htmlspecialchars($_GET["s"]);
}
?>
</title>
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
</article>
<?php

?>
<footer>
This is the footer.
</footer>
</body>
</html>
