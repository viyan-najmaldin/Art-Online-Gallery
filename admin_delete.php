<?php
	$art_isbn = $_GET['artisbn'];

	require_once "./functions/database_functions.php";
	$conn = db_connect();

	$query = "DELETE FROM arts WHERE art_isbn = '$art_isbn'";
	$result = mysqli_query($conn, $query);
	if(!$result){
		echo "delete data unsuccessfully " . mysqli_error($conn);
		exit;
	}
	header("Location: admin_art.php");
?>