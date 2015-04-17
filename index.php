<?php
session_start();

if(!isset($_SESSION['Email'])) {
	$_SESSION['Redirect'] = "account.php";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Indiana Music Business Directory</title>
<link rel="icon" type="image/x-icon" href="images/favicon.ico">
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
<p>The Indiana Music Business Directory began in 2014 as a project by Robert Willey and his music industry students in the School of Music at Ball State University <a href=http://tinyurl.com/indianamusic>tinyurl.com/indianamusic</a> in order to help develop the music business infrastructure in Indiana and help clients connect with providers.
</p>
<p>In 2015 Jason Toomey and Kevin Charlton created this web-based app to make it easier to add and access information from a desktop computer or mobile device. The work was done as a capstone project under the direction of Willey and Lan Lin, their CS495 professor.
</p>
<form action="search.php" method="GET" id="searchForm">
<input type="text" name="search">
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
