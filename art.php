<?php
  session_start();
  $art_isbn = $_GET['artisbn'];
  // connect t database
  require_once "./functions/database_functions.php";
  $conn = db_connect();

  $query = "SELECT * FROM arts WHERE art_isbn = '$art_isbn'";
  $result = mysqli_query($conn, $query);
  if(!$result){
    echo "Can't retrieve data " . mysqli_error($conn);
    exit;
  }

  $row = mysqli_fetch_assoc($result);
  if(!$row){
    echo "Empty art";
    exit;
  }

  $title = $row['art_title'];
  require "./template/header.php";
?>
      <p class="lead" style="margin: 25px 0"><a href="arts.php">Back </a> > <?php echo $row['art_title']; ?></p>
      <div class="row">
        <div class="col-md-3 text-center">
          <img class="img-responsive img-thumbnail" src="./bootstrap/img/<?php echo $row['art_image']; ?>">
        </div>
        <div class="col-md-6">
          <h4>Art Description</h4>
          <p><?php echo $row['art_descr']; ?></p>
          <h4>Art Details</h4>
          <table class="table">
          	<?php foreach($row as $key => $value){
              if($key == "art_descr" || $key == "art_image" || $key == "publisherid" || $key == "art_title"){
                continue;
              }
              switch($key){
                case "art_isbn":
                  $key = "ID";
                  break;
                case "art_title":
                  $key = "Title";
                  break;
                case "art_artist":
                  $key = "Artist";
                  break;
                case "art_price":
                  $key = "Price";
                  break;
              }
            ?>
            <tr>
              <td><?php echo $key; ?></td>
              <td><?php echo $value; ?></td>
            </tr>
            <?php 
              } 
              if(isset($conn)) {mysqli_close($conn); }
            ?>
          </table>
          <form method="post" action="cart.php">
            <input type="hidden" name="artisbn" value="<?php echo $art_isbn;?>">
            <input type="submit" value="Purchase / Add to cart" name="cart" class="btn btn-primary">
          </form>
       	</div>
      </div>
<?php
  require "./template/footer.php";
?>