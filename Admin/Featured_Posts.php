<?php
session_start();
require '../Database/MoistFunctions.php';
if (!isset($_SESSION['Admin'])) {
  header("Location: ../Main/index.php");
}

$moistFunction = new MoistFunctions($connection);
$moistFunctions = new MoistFunctions($connection);

$featureData = $moistFunction->showRecords('featured_post', NULL, 'games', 'featured_post.Game_ID', 'games.Game_ID', 'featured_post.Featured_ID');
$featured = $moistFunction->showRecords('featured_post');
$games = $moistFunction->showRecords('games');

if (isset($_POST['Update'])) {
  $id = $_POST['Featured_ID'];

  foreach ($_POST as $name => $val) {
    if ($name !== 'Update') {
      $datas[$name] = $val;
    }
  }
  try {
    $action = $moistFunctions->updateQuery($datas, 'featured_post', ['Featured_ID' => $id]);
  } catch (Exception $e) {
    echo "Error: $e";
    die();
  }
  header("Refresh:0");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Featured Games</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/Featured_CSS.css">
  <link rel="stylesheet" href="../css/All_Admin_CSS.css">
  <link rel="stylesheet" href="../css/header_css.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
    rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
    crossorigin="anonymous">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
  <!--Para sa Search-->
  <link rel="stylesheet" href="https://unpkg.com/@jarstone/dselect/dist/css/dselect.css">
  <script src="https://unpkg.com/@jarstone/dselect/dist/js/dselect.js"></script>
</head>

<body class="feature-body">
  <?php include '../header.php'; ?>
  <!------------------------------------------------------------------------ Update Featured ------------------------------------------------------------------------>
  <div class="modal fade" id="EditFeatured-Form" tabindex="-1" aria-labelledby="EditFeatured" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content"  id="EditFeatured-FormPop">
        <div class="modal-body" id="EditFeatured-Container">
          <center>
            <form action="" method="post" enctype="multipart/form-data">
              <p style="font-size: 25px; margin-top: 16px; margin-bottom: 29px">Update Featured Games<br><img src="../img/game_logo.svg" style="margin-top: 14px; width: 150px; height: 150px;"></p>
              
              <label for="name">Feature ID</label><br>
              <select name='Featured_ID' class='form-select' onmousedown="if(this.options.length>5){this.size=5;}"  onchange='this.size=0;' required>
                <option value='' disabled selected>Feature ID</option>
                    <?php if (count($featured) > 0) {
                        foreach ($featured as $feat) {
                            echo "<option value ='$feat[0]'>$feat[0]</option>";
                        }
                    } ?>
              </select><br>

              <label for="email">Game to Feature</label><br>
              <select name='Game_ID' class='form-select' id="select_game"  onmousedown="if(this.options.length>8){this.size=8;}"  onchange='this.size=0;' onblur="this.size=0;"  required>
                <option value='' disabled selected>Game Name</option>       
                    <?php if (count($games) > 0) {
                        foreach ($games as $game) {
                            echo "<option value ='$game[0]'>$game[1]</option>";
                        }
                    } ?>
              </select><br>
                  
              <input type="submit" name="Update" class="submit-button">
              <button class="submit-button" data-bs-dismiss="modal">Cancel</button>
            </form>
          </center>  
        </div>
      </div>
    </div>
  </div>

  <div class="feat_section">
    <div class="head_sec">
      <p>Featured Posts</p>
    </div>
    <div class="featured-container">
      <div class="featured-game" style="background-image: url('../Games/<?php echo $featureData[0][2] ?>/Image.png');">
        <div class="featured-game-title">
          <h3><?= $featureData[0][2] ?></h3>
        </div>
      </div>
      <div class="featured-game" style="background-image: url('../Games/<?php echo $featureData[1][2] ?>/Image.png');">
        <div class="featured-game-title">
          <h3><?= $featureData[1][2] ?></h3>
        </div>
      </div>
      <div class="featured-game" style="background-image: url('../Games/<?php echo $featureData[2][2] ?>/Image.png');">
        <div class="featured-game-title">
          <h3><?= $featureData[2][2] ?></h3>
        </div>
      </div>
      <div class="featured-game" style="background-image: url('../Games/<?php echo $featureData[3][2] ?>/Image.png');">
        <div class="featured-game-title">
          <h3><?= $featureData[3][2] ?></h3>
        </div>
      </div>
      <div class="featured-game" style="background-image: url('../Games/<?php echo $featureData[4][2] ?>/Image.png');">
        <div class="featured-game-title">
          <h3><?= $featureData[4][2] ?></h3>
        </div>
      </div>
    </div>
    <div class="update-button">
      <button class="update-btn" data-bs-toggle="modal" data-bs-target="#EditFeatured-Form">Update Featured</button>
    </div>
  </div>



</body>

</html>

<script 
  src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
  integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" 
  crossorigin="anonymous">
</script>

<script>
  var select_box_element = document.querySelector('#select_game');

  dselect(select_box_element, {
    search: true,
    maxHeight:'200px'
    //Para sa elements niya if need niyo baguhin https://github.com/jarstone/dselect
  });
</script>