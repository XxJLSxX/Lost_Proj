<?php
session_start();
require '../Database/MoistFunctions.php';
$moistFunctions = new MoistFunctions($connection);


$id = $_GET["id"] ?? NULL;

$pageDev = isset($_GET['page']) ? $_GET['page'] : 1;
$paginationData = $moistFunctions->paginateDevs("developer");
$resultDev = $paginationData['itemsDev'];
$total_pagesDev = $paginationData['total_pagesDev'];
$prev_PageDev = $paginationData['prev_pageDev'];
$next_PageDev = $paginationData['next_pageDev'];

$devs = $moistFunctions->showRecords('developer');
$data = $moistFunctions->showRecords('developer', "Developer_ID = '$id'");


if (isset($_POST['Add'])) {
    $data = [];
    $nodup = 1;
    $dname = $_POST['Developer_Name'];
    foreach ($_POST as $name => $val) {
        if ($name !== 'Add' && $name !== 'Developer_Image') {
            $data[$name] = $val;
        }
    }

    for ($i = 0; $i < count($devs); $i++) {
        $cmp1 = (strtolower($devs[$i][1]));
        $cmp2 = (strtolower($data['Developer_Name']));
        if ($cmp1 == $cmp2) $nodup = 0;
    }

    if ($nodup == 1) {
        try {
            $action = $moistFunctions->addQuery($data, 'developer');
            //Create Folder
            $folderPath = "../Developer/$dname";
            if (!is_dir($folderPath)) {
                //Create if existing
                mkdir($folderPath, 0777);
            }
            $target_dir = "../Developer/$dname/";
            $moistFunctions->uploadFile($_FILES["Developer_Image"], $target_dir, "Image" . ".png");
            $moistFunctions->uploadFile($_FILES["Developer_Background"], $target_dir, "Background" . ".png");
        } catch (Exception $e) {
            echo "Error: $e";
            die();
        }
    } else {
        echo '<script>alert("Developer Already Exists!");</script>';
    }
}

if (isset($_POST['Edit'])) {
    $id = $_POST['u_id'];
    $devData = $moistFunctions->showRecords('developer', "Developer_ID = '$id'");
    $Dname = $_POST['Developer_Name'];
    $folderPath = "../Developer/" . $devData[0][1];
    $new_folderPath = "../Developer/$Dname";

    if (is_dir($folderPath)) {
        if (strcmp($Dname, $devData[0][1]) != 0) {
            $existingDevData = $moistFunctions->showRecords('developer', "Developer_Name = '$Dname'");
            if (!empty($existingDevData)) {
                echo '<script>alert("Developer Already Exists!");</script>';
            } else {
                rename($folderPath, $new_folderPath);

                $datas = [];
                foreach ($_POST as $name => $val) {
                    if ($name !== 'Edit' && $name !== 'GameImage' && $name !== 'GameBackground' && $name !== 'Screenshot1' && $name !== 'Screenshot2' && $name !== 'Screenshot3' && $name !== 'u_id') {
                        $datas[$name] = $val;
                    }
                }

                try {
                    $action = $moistFunctions->updateQuery($datas, 'Developer', ['Developer_ID' => $id]);
                } catch (Exception $e) {
                    echo "Error: $e";
                    die();
                }

                $target_dir = $new_folderPath . "/";
                $moistFunctions->uploadFile($_FILES["Developer_Image"], $target_dir, "Image" . ".png");
                $moistFunctions->uploadFile($_FILES["Developer_Background"], $target_dir, "Background" . ".png");
            }
        }
    }
    header("Refresh:0");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/header_css.css?+2">
    <link rel="stylesheet" href="../css/footer_css.css">
    <link rel="stylesheet" href="../css/Game_Devs_css.css?+5">
    <link rel="stylesheet" href="../css/Game_Library_CSS.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Game Developers</title>
</head>

<body>
    <?php include '../header.php' ?>
    <!------------------------------------------------------ Add Dev ------------------------------------------------------>
    <div class="modal fade" id="AddDev-Form" tabindex="-1" aria-labelledby="AddDev" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" >
                <div class="modal-body">
                    <form action="" method="post" enctype="multipart/form-data" style="display: flex; align-items:center; justify-content:center;">
                        <p style="font-size: 25px; margin-top: 16px; margin-bottom: 29px">Add Developer<br><img src="../img/default-icon.png" style="margin-top: 14px; width: 150px; height: 150px;"></p>

                        <label for="developer_image">Developer Image</label>
                        <input type="file" id="inputFile" class="file-upload" name="Developer_Image" placeholder="Upload" accept="image/png, image/jpeg" required><br>

                        <label for="developer_image">Developer Background</label>
                        <input type="file" id="inputFile" class="file-upload" name="Developer_Background" placeholder="Upload" accept="image/png, image/jpeg" required><br>

                        <label for="name">Developer Name</label><br>
                        <input type="text" name="Developer_Name" placeholder="Developer Name" required><br>

                        <label for="email">Email</label><br>
                        <input type="email" name="Developer_Email" placeholder="Email Address" required><br>

                        <label for="address">Address</label><br>
                        <input type="text" name="Developer_Address" placeholder="Developer Address" required><br>

                        <label for="about_desc">About Description:</label><br>
                        <textarea name="Developer_Desc" rows="4" placeholder="Developer Description" required style="width: 80%; border-radius:15px;"></textarea><br>

                        <input type="submit" name="Add" class="submit-button">
                        <button class="submit-button" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!------------------------------------------------------ Edit Dev ------------------------------------------------------>
    <div class="modal fade" id="EditDev-Form" tabindex="-1" aria-labelledby="AddDev" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="width: 600px;">
            <div class="modal-content"  id="EditDevs-FormPop">>
                <div class="modal-body" id="EditDevs-Container">
                    <!---------- Content is in ../Edit/gamesEdit.php ---------->
                </div>
            </div>
        </div>
    </div>

    <!------------------------------------------------------------------------ Main Body ------------------------------------------------------------------------>
    <div class="game-dev-section-container">
        <div class="top-part-dev-sec">
            <p class="devhead">Game Developers</p>
            <?php if (isset($_SESSION['Admin'])) { ?>
                <button class="Dev-AddButton" data-bs-toggle="modal" data-bs-target="#AddDev-Form">Add Developer</button>
            <?php } ?>
        </div>

        <div class="developer_container">
            <?php while ($row = $resultDev->fetch_assoc()) : ?>
                <div class="developer_card">
                    <div class="developer_profile-img">
                        <img src="../Developer/<?php echo $row['Developer_Name']; ?>/Image.png" alt="<?php echo $row['Developer_Name'] ?>">
                    </div>
                    <h5 class="developer_card-title"><?php echo $row['Developer_Name'] ?></h5>
                    <div class="dev-buttons">
                        <a href="../Main/Dev_Page.php?id=<?= $row['Developer_ID'] ?>" class="developer_btn">View Profile</a>

                        <?php
                        if (isset($_SESSION['Admin'])) : ?>
                            <button class='developer_btn' data-bs-toggle="modal" data-bs-target='#EditDev-Form' id="edit-id" onclick="popupEdit(<?= $row['Developer_ID']; ?>)">
                                Edit Developer</button>
                        <?php endif ?>

                    </div>

                </div>
            <?php endwhile; ?>
        </div>

        <div class="pagination-links">
            <!-- Display pagination links -->
            <a href="?page=1" class="pagination-links-buttons">&#10094;&#10094;</a>

            <a href="?page=<?php echo $prev_PageDev; ?>" class="pagination-links-buttons">&#10094;</a>

            <?php for ($i = max(1, $pageDev - 1); $i <= min($pageDev + 1, $total_pagesDev); $i++) : ?>
                <a href="?page=<?php echo $i; ?>" <?php
                                                    if ($i == $pageDev)
                                                        echo 'class="page-highlight"';
                                                    ?> class="pagination-links-buttons"><?php echo $i; ?></a>
            <?php endfor; ?>

            <a href="?page=<?php echo $next_PageDev; ?>" class="pagination-links-buttons">&#10095;</a>

            <a href="?page=<?php echo $total_pagesDev; ?>" class="pagination-links-buttons">&#10095;&#10095;</a>
        </div>
    </div>

</body>

</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>

<script>
    let editForm = document.getElementById("EditDevs-FormPop");
    let editForm_Container = document.getElementById("EditDevs-Container");

    function popupEdit(value) {
        var id = value;

        function sendToPHP(value) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    console.log("Value sent to PHP successfully");
                    editForm.classList.add("show-edit");
                    editForm_Container.classList.add("show-edit-container");
                    editForm_Container.scrollTop = 0;
                    document.body.style.overflow = 'hidden';
                    document.documentElement.style.overflow = 'hidden';
                    document.getElementById("EditDevs-Container").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "../Popups/devsEdit.php?id=" + value, true);
            xhttp.send();
        }
        sendToPHP(id);
    }
</script>