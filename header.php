<h1>Indiana Music Business Directory</h1>
<nav class="nav">
<ul>
<li><a href="index.php">Home</a></li>
<li><a href="add.php">Add Business</a></li>
<?php
if(isset($_SESSION['Email'])) {
	echo "<li><a href='account.php'>My Account</a></li>\n";
	echo "<li><a href='logout.php'>Sign Out</a></li>\n";
}
else {
	echo "<li><a href='createaccount.php'>Create Account</a></li>\n";
	echo "<li><a href='login.php'>Sign In</a></li>\n";
}
?>
</ul>
</nav>