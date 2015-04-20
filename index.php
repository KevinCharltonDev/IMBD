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
<h2>Welcome</h2>
<div class="content">
<p>Welcome to the Indiana Music Business Directory.  To get started, click '<a href="createaccount.php">here</a>' to create an account or search for businesses below. Creating an account allows you to write reviews and add your business or organization to the directory.  Music groups and individual musicians are also welcome to add themselves to the directory.</p>
<p>
If your business is already in the directory, you can request permission to edit that information. You will need to create an account and find your business.  There will be a link at the top of the page allowing you to request edit permission for that business.  Your request will need to be approved by an administrator.
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
<section>
<h2>About Us</h2>
<div class="content">
<p>The Indiana Music Business Directory began in 2014 as a project by Robert Willey and his music industry students in the School of Music at Ball State University (<a href=http://tinyurl.com/indianamusic>tinyurl.com/indianamusic</a>). Its goal was to help develop the music business infrastructure in Indiana and help clients connect with providers.
</p>
<p>In 2015, Jason Toomey and Kevin Charlton created this web-based app to make it easier to add and access information from a desktop computer or mobile device. The work was done as a capstone project under the direction of Willey and Lan Lin, their Software Engineering professor.
</p>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>
