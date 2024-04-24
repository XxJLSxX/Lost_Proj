<?php
session_start();
require '../Database/MoistFunctions.php';

$users = new MoistFunctions($connection);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$records_per_page = 10;

$start_from = ($page - 1) * $records_per_page;

$users_data =  $users->showRecords('users', 'User_ID > 1');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="../css/sweetalert.js"></script>
    <link rel="stylesheet" href="../css/design2.css">
    <link rel="stylesheet" href="../css/header_css.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>User Dashboard</title>
    <style>
        body {
            font-family: "Montserrat", sans-serif;
            background-color: #15181d;
            display: flex;
            align-items: center;

            flex-direction: column;
            position: relative;
            height: 100vh;
        }
        .swal-modal {
            background-color: #5b6e88;
        }
        .swal-title {
            color: white;
        }
        .swal-text {
            color: white;
        }
        .titolo{
            color: white;
            margin: 100px 0 0 0px;
            font-size: 40px;
            align-self: start;
            width: 100%;
            padding: 0 2% 0 2%;
        }
    </style>
</head>

<body>
    <?php include '../header.php'; ?>
    <p class="titolo">Users</p>
    <table class="tbl">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">ID</th>
                <th scope="col">Name</th>
                <th scope="col">Username</th>
                <th scope="col">Email</th>
                <th scope="col">Payment Method</th>
                <th scope="col">Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $count = 1 + ($page - 1) * $records_per_page;
            foreach ($users_data as $user_data) {
            ?>
                <tr>
                    <td data-label="#"><?php echo $count++; ?></td>
                    <td data-label="ID"><?php echo $user_data[0]; ?></td>
                    <td data-label="NAME"><?php echo $user_data[1]; ?></td>
                    <td data-label="USER_NAME"><?php echo $user_data[2]; ?></td>
                    <td data-label="EMAIL"><?php echo $user_data[3]; ?></td>
                    <td data-label="PAYMENT_METHOD"><?php echo $user_data[5]; ?></td>
                    <td data-label="DELETE"><button id="btn" class="delete-btn-custom" onclick="confirmDelete(<?php echo $user_data[0]; ?>)">Delete</button>
                    </td>
                    <script>
                        function confirmDelete(id) {
                            console.log(id);
                            swal({
                                title: "Are you sure?",
                                text: "Once deleted, you will not be able to recover this data!",
                                icon: "warning",
                                buttons: {
                                    cancel: "Cancel",
                                    confirm: {
                                        text: "Yes, delete it!",
                                        value: true,
                                        visible: true,
                                        className: "btn-danger",
                                        closeModal: true
                                    }
                                },
                                dangerMode: true,
                            }).then((willDelete) => {
                                if (willDelete) {
                                    console.log(id);
                                    deleteRecord(id);
                                } else {
                                    swal("Data deletion canceled!", {
                                        icon: "info",
                                    });
                                }
                            });
                        }

                        function deleteRecord(id) {
                            console.log(id);
                            var xhr = new XMLHttpRequest();

                            xhr.open("POST", "delete.php", true);
                            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

                            xhr.onreadystatechange = function() {
                                if (xhr.readyState == 4 && xhr.status == 200) {
                                    var response = xhr.responseText;
                                    console.log(id);
                                    if (response.trim() == "success") {
                                        swal("Data deleted successfully!", {
                                            icon: "success",
                                        }).then(() => {
                                            console.log("good");
                                            window.location.reload();
                                        });
                                    } else {
                                        swal("Data deleted successfully!", {
                                            icon: "success",
                                        }).then(() => {
                                            if (document.querySelectorAll('.tbl tbody tr').length === 1 && <?php echo $page; ?> > 1) {
                                                window.location.href = 'admin.php?page=<?php echo $page - 1; ?>';
                                            } else {
                                                window.location.reload();
                                            }
                                        });
                                    }
                                }
                            };

                            xhr.send("id=" + id);
                        }
                    </script>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php
        $query = "SELECT COUNT(*) AS total_records FROM users";
        $result = mysqli_query($connection, $query);
        $row = mysqli_fetch_assoc($result);
        $total_records = $row['total_records'];
        $total_pages = ceil($total_records / $records_per_page);

        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='user_table.php?page=" . $i . "'";

            if ($i == $page) {
                echo " class='active'";
            }

            echo ">" . $i . "</a>";
        }
        ?>
    </div>
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>