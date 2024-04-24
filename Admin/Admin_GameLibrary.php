<?php
session_start();
require '../Database/MoistFunctions.php';
if (!isset($_SESSION['Admin'])) {
    header("Location: ../Main/index.php");
}
$moistFunctions = new MoistFunctions($connection);
$moistFunction = new MoistFunctions($connection);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$paginationData = $moistFunction->paginateItems("games");
$itemsResult = $paginationData['items'];
$total_pages = $paginationData['total_pages'];
$prev_Page = $paginationData['prev_page'];
$next_Page = $paginationData['next_page'];
$date_order = $paginationData['latest'];
$category_filter = $paginationData['sort'];

$devs = $moistFunctions->showRecords('developer');

if (isset($_POST['Add'])) {
    $data = [];
    $Gname = $_POST['Game_Name'];
    //Input Date in Database Table
    $data['Game_Downloads'] = '0';
    $data['Upload_Date'] = date('Y-m-d');
    $data['Game_Rating'] = '0';
    foreach ($_POST as $name => $val) {
        if ($name !== 'Add' && $name !== 'GameImage' && $name !== 'GameBackground' && $name !== 'Screenshot1' && $name !== 'Screenshot2' && $name !== 'Screenshot3') {
            $data[$name] = $val;
        }
    }
    //Create Folder
    $folderPath = "../Games/$Gname";
    if (!is_dir($folderPath)) {
        //Create if existing
        mkdir($folderPath, 0777);
        try {
            $action = $moistFunctions->addQuery($data, 'games');
        } catch (Exception $e) {
            echo "Error: $e";
            die();
        }

        //Save and Rename image for GameImage
        $target_dir = "../Games/$Gname/";
        $moistFunctions->uploadFile($_FILES["GameImage"], $target_dir, "Image." . "png");
        $moistFunctions->uploadFile($_FILES["GameBackground"], $target_dir,  "Background." . "png");
        $moistFunctions->uploadFile($_FILES["Screenshot1"], $target_dir,  "Screenshot1." . "png");
        $moistFunctions->uploadFile($_FILES["Screenshot2"], $target_dir, "Screenshot2." . "png");
        $moistFunctions->uploadFile($_FILES["Screenshot3"], $target_dir,  "Screenshot3." . "png");
    } else {
        echo '<script>alert("Game Already Added!");</script>';
    }
}

if (isset($_POST['Edit'])) {
    $id = $_POST['u_id'];
    $data = $moistFunctions->showRecords('games', null, 'developer', 'games.Developer_ID', 'developer.Developer_ID', "games.Game_ID='$id'");

    $Gname = $_POST['Game_Name'];
    $folderPath = "../Games/" . $data[0][1];
    $new_folderPath = "../Games/$Gname";

    rename($folderPath, $new_folderPath);

    $datas = array();
    foreach ($_POST as $name => $val) {
        if ($name !== 'Edit' && $name !== 'GameImage' && $name !== 'GameBackground' && $name !== 'Screenshot1' && $name !== 'Screenshot2' && $name !== 'Screenshot3' && $name !== 'u_id') {
            $datas[$name] = $val;
        }
    }

    try {
        $action = $moistFunctions->updateQuery($datas, 'games', ['Game_ID' => $id]);
        
        $target_dir = $new_folderPath . "/";
        $moistFunctions->uploadFile($_FILES["GameImage"], $target_dir, "Image." . "png");
        $moistFunctions->uploadFile($_FILES["GameBackground"], $target_dir, "Background." . "png");
        $moistFunctions->uploadFile($_FILES["Screenshot1"], $target_dir, "Screenshot1." . "png");
        $moistFunctions->uploadFile($_FILES["Screenshot2"], $target_dir,  "Screenshot2." . "png");
        $moistFunctions->uploadFile($_FILES["Screenshot3"], $target_dir,  "Screenshot3." . "png");
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        die();
    }
    
}


?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/header_css.css?+3">
    <link rel="stylesheet" href="../css/Game_Library_CSS.css?+3">
    <link rel="stylesheet" href="../css/All_Admin_CSS.css?+8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <title>Admin Game Library</title>
</head>

<body>
    <?php
    include "../header.php";
    ?>
    <!------------------------------- Confirmation ng Delete ------------------------------------>
    <div class="popup-area" id="admin-delete-popUp">
        <div class="popup-con" id="admin-delete-pup">
            <div class="popup-title">
                <p>Are sure you want to delete this game?</p>
            </div>
            <div class="popup-links">
                <button class="cancel-delete" onclick="removeDeletePop()">Cancel</button>
                <button id="admin-continue-del" name="continue-delete" type="submit" class="continue-delete">Continue</button>
            </div>
        </div>
    </div>
    <!------------------------------------------------------------ Add Game Popup ------------------------------------------------------------>
    <div class="modal fade" id="GameAdd-Form" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <center>
                        <form action="" method="post" enctype="multipart/form-data">
                            <a href="index.php">
                                <img src="../img/logo.png" style="aspect-ratio: 2 / 1; width: 150px;">
                            </a>
                            <p style="font-size: 25px; margin-top: 16px; margin-bottom: 29px">Add a New Game</p>

                            <label for="name">Game Name</label>
                            <input type="text" name="Game_Name" placeholder="Name" required><br>

                            <label for="developer">Game Developer</label>
                            <select name="Developer_ID" class="form-select" onmousedown="if(this.options.length>5){this.size=5;}" onchange='this.size=0;' onblur="this.size=0;" required>
                                <option value="" disabled selected>Select Game Developer</option>
                                <?php
                                if (count($devs) > 0) {
                                    foreach ($devs as $dev) {
                                        echo "<option value ='$dev[0]'>$dev[1]</option>";
                                    }
                                }
                                ?>
                            </select><br>

                            <label for="price">Game Price</label>
                            <input type="float" name="Price" placeholder="Price" required><br>

                            <label for="genre">Game Genre</label>
                            <select name="Category" class="form-select" required>
                                <option value="" disabled selected>Select Game Category</option>
                                <option value="1">Action</option>
                                <option value="2">Adventure</option>
                                <option value="3">RPG</option>
                                <option value="4">Simulation</option>
                                <option value="5">Strategy </option>
                            </select>
                            <br>
                            <label for="game_image">Game Image</label>
                            <input type="file" id="inputFile" class="file-upload" name="GameImage" placeholder="Upload" accept="image/png, image/jpeg" required><br>

                            <label for="game_image">Game Background</label>
                            <input type="file" id="inputFile" class="file-upload" name="GameBackground" placeholder="Upload" accept="image/png, image/jpeg" required><br>

                            <label for="game_image">Game Screenshots</label>
                            <input type="file" id="inputFile" class="file-upload" name="Screenshot1" style="margin-bottom: 15px;" placeholder="Upload Screenshot 1" accept="image/png, image/jpeg" required>
                            <input type="file" id="inputFile" class="file-upload" name="Screenshot2" style="margin-bottom: 15px;" placeholder="Upload Screenshot 2" accept="image/png, image/jpeg" required>
                            <input type="file" id="inputFile" class="file-upload" name="Screenshot3" placeholder="Upload Screenshot 3" accept="image/png, image/jpeg" required>
                            <br>

                            <label for="game_desc">Game Description</label>
                            <textarea name="Game_Desc" rows="4" placeholder="Write description here..." required></textarea><br>

                            <input type="submit" name='Add' class="submit-button"><br>
                            <a href="" style="margin-top: 10px; color: white; text-decoration: none;" data-bs-dismiss="modal">Cancel</a>
                        </form>
                    </center>
                </div>
            </div>
        </div>
    </div>
    <!------------------------------------------------------------ Update Game Popup ------------------------------------------------------------>
    <div class="modal fade" tabindex="-1" id="EditGame-Form" aria-labelledby="UpdatePopup" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" id="EditGame-FormPop">
                <div class="modal-body" id="EditGame-Container">
                    <!---------- Content is in ../Edit/editGames.php ---------->
                </div>
            </div>
        </div>
    </div>

    <!------------------------------------ Main Body ------------------------------------>
    <section id="section">
        <div class="game-library-container">
            <div class="library-header">
                <div class="library-title">
                    <p>Games</p>
                </div>
                <div class="game-sort-options">
                    <div class="sort-controls">
                        <p>Sort by:</p>
                        <a href="?all=true#section4" <?php if (isset($_GET['all'])) echo 'style="background-color: #545d6c;color: rgb(0, 0, 0);"'; ?>>All Games</a>
                        <a href="?latest=1<?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?>#section4" <?php if (isset($_GET['latest'])) echo 'style="background-color: #545d6c;color: rgb(0, 0, 0);"'; ?>>Latest</a>
                        <form id="filterForm" action="#section4" method="get">
                            <select name="sort" placeholder="Genre" id="sort">
                                <option value="" disabled <?php echo (!isset($_GET['sort']) ? 'selected' : ''); ?> hidden>Genre</option>
                                <option value="action" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'action' ? 'selected' : ''); ?>>Action</option>
                                <option value="adventure" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'adventure' ? 'selected' : ''); ?>>Adventure</option>
                                <option value="rpg" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'rpg' ? 'selected' : ''); ?>>RPG</option>
                                <option value="simulation" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'simulation' ? 'selected' : ''); ?>>Simulation</option>
                                <option value="strategy" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'strategy' ? 'selected' : ''); ?>>Strategy</option>
                            </select>
                        </form>
                    </div>
                    <a class="library-add-button" href="#" data-bs-target="#GameAdd-Form" data-bs-toggle="modal">Add Game</a>
                </div>
            </div>

            <!------------------------------------ ------------- ------------------------------------>
            <div class="game-records">
                <?php while ($row = $itemsResult->fetch_assoc()) : ?>
                    <div class="game-entry">
                        <div class="game-image-container">
                            <img src="../Games/<?php echo $row['Game_Name']; ?>/Image.png">
                        </div>
                        <div class="game-details-container">
                            <div class="game-title-section">
                                <p class="game-lib-title" onclick="window.location.href = '../Main/Game_Profile.php?id=<?php echo $row['Game_ID']; ?>'"><?php echo $row['Game_Name']; ?></p>
                                <button class='edit-button' data-bs-toggle="modal" data-bs-target="#EditGame-Form" id="edit-id" onclick="popupEdit(<?= $row['Game_ID']; ?>)">
                                    Edit
                                </button>
                            </div>
                            <div class="game-info-section">
                                <p class="game-developer"><?php echo $row['Developer_Name']; ?></br></p>
                                <p class="game-genre"><?php echo $row['Category']; ?></p>
                                <?php $game_id = $row['Game_ID'];
                                $avg_rating_sql = "SELECT AVG(Rate_Score) as 'Rate_Score' FROM rating WHERE Game_ID = $game_id";
                                $avg_rating_result = mysqli_query($connection, $avg_rating_sql);
                                $avg_rating_row = $avg_rating_result->fetch_assoc();

                                $rating = $avg_rating_row['Rate_Score'];  ?>
                                <div class="rating" data-rating="<?php echo $rating; ?>">
                                    <?php
                                    // Determine how many full stars to display
                                    $fullStars = floor($rating);

                                    // Generate full stars
                                    for ($i = 1; $i <= $fullStars; $i++) {
                                        echo '<span class="star filled">&#9733;</span>';
                                    }

                                    // Generate empty stars to fill up to 5 stars
                                    for ($i = $fullStars + 1; $i <= 5; $i++) {
                                        echo '<span class="star">&#9733;</span>';
                                    }
                                    ?>
                                </div>
                                <button class='delete-button' onclick="openDeletePop(<?= $row['Game_ID']; ?>)">Delete</button>
                                <script>
                                    let deletePop = document.getElementById('admin-delete-popUp');
                                    let deletes = document.getElementById('admin-delete-pup');
                                    var continueBtn = document.getElementById('admin-continue-del');

                                    function openDeletePop(id) {
                                        console.log(id);
                                        deletePop.style.visibility = 'visible';
                                        deletes.classList.add('popup-con-open');
                                        document.body.style.overflow = 'hidden';
                                        document.documentElement.style.overflow = 'hidden';
                                        continueBtn.addEventListener('click', function() {
                                            console.log("Continue button clicked!");
                                            deleteRecord(id);

                                        });
                                    }

                                    function deleteRecord(id) {
                                        console.log(id);
                                        var xhr = new XMLHttpRequest();
                                        xhr.open("POST", "delete_game.php", true);
                                        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                                        xhr.onreadystatechange = function() {
                                            if (xhr.readyState == 4 && xhr.status == 200) {
                                                console.log(id);
                                                window.location.reload();
                                            }
                                        };
                                        xhr.send("id=" + id);
                                    }

                                    function removeDeletePop() {
                                        deletePop.style.visibility = 'hidden';
                                        deletes.classList.remove('popup-con-open');
                                        document.body.style.overflow = 'auto';
                                        document.documentElement.style.overflow = 'auto';
                                    }
                                </script>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="pagination-links">
                <!-- Display pagination links -->
                <a href="?page=1
                    <?php
                    echo isset($_GET['latest']) ? '&latest=1' : '';
                    echo isset($_GET['all']) ? '&all=true' : '';
                    echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '';
                    ?>#section4" class="pagination-button">&#10094;&#10094;</a>
                <a href="?page=
                    <?php
                    echo $prev_Page;
                    echo isset($_GET['all']) ? '&all=true' : '';
                    echo isset($_GET['latest']) ? '&latest=1' : '';
                    echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '';
                    ?>#section4" class="pagination-button">&#10094;</a>

                <?php for ($i = max(1, $page - 1); $i <= min($page + 1, $total_pages); $i++) : ?>
                    <a href="?page=
                        <?php
                        echo $i;
                        echo isset($_GET['all']) ? '&all=true' : '';
                        echo isset($_GET['latest']) ? '&latest=1' : '';
                        echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '';
                        ?>#section4" <?php
                                        if ($i == $page)
                                            echo 'class="page-highlight"';
                                        ?> class="pagination-button"><?php echo $i; ?></a>
                <?php endfor; ?>

                <a href="?page=
                    <?php
                    echo $next_Page;
                    echo isset($_GET['all']) ? '&all=true' : '';
                    echo isset($_GET['latest']) ? '&latest=1' : '';
                    echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '';
                    ?>#section4" class="pagination-button">&#10095;</a>
                <a href="?page=
                    <?php
                    echo $total_pages;
                    echo isset($_GET['all']) ? '&all=true' : '';
                    echo isset($_GET['latest']) ? '&latest=1' : '';
                    echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '';
                    ?>#section4" class="pagination-button">&#10095;&#10095;</a>
            </div>
        </div>
    </section>
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js?+1" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>

<script>
    document.getElementById('sort').addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
</script>

<script>
    let editForm = document.getElementById("EditGame-FormPop");
    let editForm_Container = document.getElementById("EditGame-Container");

    function popupEdit(value) {
        var id = value;

        function sendToPHP(value) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    console.log("Value sent to PHP successfully");
                    editForm_Container.scrollTop = 0;
                    document.body.style.overflow = 'hidden';
                    document.documentElement.style.overflow = 'hidden';
                    document.getElementById("EditGame-Container").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "../Popups/gameEdit.php?id=" + value, true);
            xhttp.send();
        }
        sendToPHP(id);
    }

    function removepopupEdit() {
        editForm.style.visibility = 'visible';
        editForm_Container.style.visibility = 'visible';
        //editForm.classList.remove("show-edit");
        //editForm_Container.classList.remove("show-edit-container");
        document.body.style.overflow = 'auto';
        document.documentElement.style.overflow = 'auto';
    }
</script>