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
<?php require 'php/css_include.php'; ?>
<script src="js/functions.js"></script>
<script src="js/validate.js"></script>
</head>
<body>
<?php require 'php/header.php'; ?>
<section>
<h2>Welcome</h2>
<div class="content">
<img src="images/imbd_logo.jpg" style="float:right;width:130px;">
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
<p>The Indiana Music Business Directory began in 2014 as a project by Robert Willey and his music industry students in the School of Music at Ball State University. Its goal is to help develop the music business infrastructure in Indiana and help clients connect with providers.
</p>
<p>In 2015, Jason Toomey and Kevin Charlton created this web-based app to make it easier to add and access information from a desktop computer or mobile device. The work was done as a capstone project under the direction of Willey and Lan Lin, their Software Engineering professor.
</p>
</div>
</section>
<section>
<h2>Disclaimer</h2>
<div class="content">
<p>
The information contained in IMBD has been supplied by a variety of third party sources, and changes on a continual basis. As with any information database, there may be inaccuracies or delays in updating the information. Although the IMBD uses reasonable efforts to update the database and improve the accuracy of the information contained therein, IMBD makes no guarantees, warranties or representations of any kind with regard to, and cannot ensure the accuracy, completeness, timeliness, quality or reliability of, any information made available from it. We specifically disclaim any and all liability for any loss or damage of any kind that you may incur, directly or indirectly, in connection with or arising from, your access to, use of or reliance upon the IMBD, including any errors or omissions in the information contained therein. Your use of and reliance upon any information contained in the IMBD is solely at your own risk.
</p>
</div>
</section>
<?php include 'php/footer.php'; ?>
</body>
</html>
