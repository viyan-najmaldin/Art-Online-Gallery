<?php
  session_start();
  $count = 0;
  // connecto database
  
  $title = "Index";
  require_once "./template/header.php";
  require_once "./functions/database_functions.php";
  $conn = db_connect();
  $row = select4Latestart($conn);
?>
      <!-- Example row of columns -->
      <p class="lead text-center text-muted">Latest Arts</p>
      <div class="row">
        <?php foreach($row as $art) { ?>
      	<div class="col-md-3">
      		<a href="art.php?artisbn=<?php echo $art['art_isbn']; ?>">
           <img class="img-responsive img-thumbnail" src="./bootstrap/img/<?php echo $art['art_image']; ?>">
          </a>
      	</div>
        <?php } ?>
      </div>
<?php
  if(isset($conn)) {mysqli_close($conn);}
  require_once "./template/footer.php";
?>