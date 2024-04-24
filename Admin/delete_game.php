<?php 
require "../Database/Connect.php";
require "../Database/MoistFunctions.php";
if (!isset($_SESSION['Admin'])) {
  header("Location: ../Main/index.php");
}
if (isset($_POST['id'])){
    $id = $_POST['id'];
    $moistFunctions = new MoistFunctions($connection);
    
    $data = $moistFunctions -> showRecords('games', "Game_ID='$id'");
    $dirPath = "../Games/" .$data[0][1];
    $files = glob($dirPath."/*"); 
    foreach($files as $file) { 
        if(is_file($file))  
          // Delete the given file 
            unlink($file);  
    } 
    rmdir($dirPath);
    $query = "DELETE FROM games WHERE games.Game_ID = '$id'";
    $sql = mysqli_query($connection, $query);
}

