<?php
	session_start();
	require_once "./functions/admin.php";
	$title = "List art";
	require_once "./template/header.php";
	require_once "./functions/database_functions.php";
	$conn = db_connect();
	$result = getAll($conn);
?>
	<p class="lead"><a href="admin_add.php">Add New Art</a></p>
	<a href="admin_signout.php" class="btn btn-primary">Sign out!</a>
	<table class="table" style="margin-top: 20px">
		<tr>
			<th>ID</th>
			<th>Title</th>
			<th>Artist</th>
			<th>Image</th>
			<th>Description</th>
			<th>Price</th>
			<th>Publisher</th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
		</tr>
		<?php while($row = mysqli_fetch_assoc($result)){ ?>
		<tr>
			<td><?php echo $row['art_isbn']; ?></td>
			<td><?php echo $row['art_title']; ?></td>
			<td><?php echo $row['art_artist']; ?></td>
			<td><?php echo $row['art_image']; ?></td>
			<td><?php echo $row['art_descr']; ?></td>
			<td><?php echo $row['art_price']; ?></td>
			<td><?php echo getPubName($conn, $row['publisherid']); ?></td>
			<td><a href="admin_edit.php?artisbn=<?php echo $row['art_isbn']; ?>">Edit</a></td>
			<td><a href="admin_delete.php?artisbn=<?php echo $row['art_isbn']; ?>">Delete</a></td>
		</tr>
		<?php } ?>
	</table>

<?php
	if(isset($conn)) {mysqli_close($conn);}
	require_once "./template/footer.php";
?>