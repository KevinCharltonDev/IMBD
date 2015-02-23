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
<?php
if(isset($_SESSION['Email'])) {
	echo "<br><h2>My account.</h2><div class='content'>
	<table>
		<tr>
			<td>Email: </td>
			<td>{$_SESSION['Email']}</td>
		</tr>
	</table>
	<table>
		<form action='updatepassword.php' method='POST'>
		<br>
		<tr>
			<td>Update Password</td>
		</tr>
		<tr>
			<td>Old password: </td>
			<td><input type='password' name='oldpassword'></td>
		</tr>
		<tr>
			<td>New password: </td>
			<td><input type='password' name='newpassword'></td>
		</tr>
		<tr>
			<td><input type='submit' value='Submit'></td>
		</tr>
		</form>
	</table>
	</form>
	</div>";
}
else{
	echo "<br><h2>Create an account.</h2><div class='content'>
	<form action='createaccount.php' method='POST'>
	<table>
		<tr>
			<td>Screen Name: </td>
			<td><input type='text' name='screenname'></td>
		</tr>
		<tr>
			<td>Email: </td>
			<td><input type='text' name='email'></td>
		</tr>
		<tr>
			<td>Password: </td>
			<td><input type='password' name='password'></td>
		</tr>
		<tr>
			<td>Security Question: </td>
			<td><input type='text' name='squestion'></td>
		</tr>
		<tr>
			<td>Security Answer: </td>
			<td><input type='text' name='sanswer'></td>
		</tr>
		<tr>
			<td><input type='submit' value='Submit'></td>
		</tr>
	</table>
	</form>
	</div>";
}
?>
</section>
</body>
</html>