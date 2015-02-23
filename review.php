<?php
session_start();

require 'query/review_query.php';

if(isset($_POST['id']))
	$id = trim($_POST['id']);
if(isset($_POST['review']))
	$review = trim($_POST['review']);
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
review($id, $review, $_SESSION['Email']);
?>
</section>
</body>
</html>
