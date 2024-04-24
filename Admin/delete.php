
<?php 

require "../Database/Connect.php";

if(isset($_POST['id'])) {
    $id = $_POST['id'];

    $query = "DELETE FROM users WHERE User_ID = '$id'";
    $res = mysqli_query($connection, $query);


} 
?>
