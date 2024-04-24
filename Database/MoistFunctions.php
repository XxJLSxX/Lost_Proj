<?php
require 'Connect.php';
/* Overall Functions */
class MoistFunctions
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function sqlExecute($sql)
    {
        $result = mysqli_query($this->connection, $sql);
        $data = array();
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data_row = array();
                foreach ($row as $r) {
                    array_push($data_row, $r);
                }
                array_push($data, $data_row);
            }
        }
        return $data;
    }


    public function showRecords(
        $tbl,
        $where = null,
        $join1 = null,
        $join1col1 = null,
        $join1col2 = null,
        $wherejoin1 = null,
        $join2 = null,
        $join2col1 = null,
        $join2col2 = null,
        $wherejoin2 = null,
        $join3 = null,
        $join3col1 = null,
        $join3col2 = null,
        $wherejoin3 = null,
        $orderBy = null
    ) {
        $sql = "SELECT * FROM $tbl";

        if ($join1 != null) {
            $sql .= " LEFT JOIN $join1 ON $join1col1 = $join1col2";
        }

        if ($join2 != null) {
            $sql .= " LEFT JOIN $join2 ON $join2col1 = $join2col2";
        }

        if ($join3 != null) {
            $sql .= " LEFT JOIN $join3 ON $join3col1 = $join3col2";
        }

        if ($where != null) {
            $sql .= " WHERE $where";
        } elseif ($wherejoin1 != null) {
            $sql .= " WHERE $wherejoin1";
        } elseif ($wherejoin2 != null) {
            $sql .= " WHERE $wherejoin2";
        } elseif ($wherejoin3 != null) {
            $sql .= " WHERE $wherejoin3";
        }

        if ($orderBy != null) {
            $sql .= " ORDER BY $orderBy DESC";
        }

        return $this->sqlExecute($sql);
    }

    public function UserRegistry($data, $selectedPaymentMethodID, $tbl)
    {
        $name = mysqli_real_escape_string($this->connection, $data['Name']);
        $username =  $data['User_Name'];
        $email = mysqli_real_escape_string($this->connection, $data['Email']);
        $password = mysqli_real_escape_string($this->connection, $data['Password']);

        $userData = $this->showRecords('users');
        foreach ($userData as $user) {
            if ($username == $user[2]) {
                return '<script>alert("Username Already Taken!");</script>';
            }
            if ($email == $user[3]) {
                return '<script>alert("Email Already Taken!");</script>';
            }
        }

        $sqlInsertUser = "INSERT INTO $tbl(Name, User_Name, Email, Password, Payment_Method) VALUES ('$name', '$username', '$email', '$password', '$selectedPaymentMethodID')";
        mysqli_query($this->connection, $sqlInsertUser);

        return '<script>alert("Successfully Added Your Account");</script>';
    }

    public function addQuery($data, $tbl)
    {
        $tbl_columns = implode(",", array_keys($data));
        $tbl_values = implode("','", $data);
        $sql = "INSERT INTO $tbl($tbl_columns) VALUES ('$tbl_values')";
        return mysqli_query($this->connection, $sql);
    }

    public function updateQuery($data, $tbl, $id)
    {
        $update = "";
        foreach ($data as $key => $value) {
            $update .= " $key='$value' ,";
        }
        $update = substr($update, 0, -1);
        $primary_key = array_keys($id)[0];
        $key_value = $id[$primary_key];
        $sql = "UPDATE $tbl SET {$update} WHERE $primary_key=$key_value";
        return mysqli_query($this->connection, $sql);
    }

    public function uploadFile($file, $target_dir, $new_file_name)
    {
        $fileExtension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $uploadOk = 1;

        if ($file["size"] > 20000000) {
            $uploadOk = 0;
        }

        if ($fileExtension != "jpg" && $fileExtension != "jpeg" && $fileExtension != "png") {
            $uploadOk = 0;
        } else {
            $target_file = $target_dir . $new_file_name;
        }

        if ($uploadOk == 0) {
        } else {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
            }
        }
        header("Refresh:0");
    }


    public function loginUser($email, $password)
    {
        $userData = $this->showRecords('Users', "email = '$email'");

        if (count($userData) > 0) {
            $userStatus = $userData[0][0];
            $hashedPassword = $userData[0][4];

            if ($userStatus == '1') {
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['Admin'] = true;
                    $_SESSION['User'] = null;
                    header("Location: ../Admin/");
                } else {
                    return '<script>alert("Wrong Password!");</script>';
                }
            } else {
                if (password_verify($password, $hashedPassword)) {
                    $_SESSION['User'] = true;
                    $_SESSION['Admin'] = null;
                    $_SESSION['User_ID'] = $userData[0][0];
                    header("Location: index.php");
                } else {
                    return '<script>alert("Wrong Password!");</script>';
                }
            }
        } else {
            return '<script>alert("Wrong Email!");</script>';
        }
    }

    /* ------------------------------------------------ Integration Point ------------------------------------------------ */
    /* Landing Page Functions */

    public function queryRandomByLimitOrderBy($table, $limit, $orderColumn, $not = null)
    {
        $query = "SELECT * FROM (SELECT * FROM $table $not ORDER BY RAND()) AS random_$table ORDER BY $orderColumn DESC LIMIT $limit";
        return $this->connection->query($query);
    }

    public function paginateItems($table, $limit = 5)
    {
        // Pagination variables
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page - 1) * $limit;

        // Sort by date
        $date_order = "";
        if (isset($_GET['latest']) == true) {
            $date_order = "ORDER BY g.Upload_Date desc"; // Sort by Upload_Date of table g (games)
        }

        // Sort by category
        $category_filter = "";
        if (isset($_GET['sort']) && !empty($_GET['sort'])) {
            $category = $_GET['sort'];
            $category_filter = "WHERE g.Category = '$category'"; // Filter by Category of table g (games)
        }

        // Fetch items for the current page
        $sql = "SELECT g.*, d.Developer_Name 
                FROM $table g
                INNER JOIN developer d ON g.Developer_ID = d.Developer_ID
                $category_filter
                $date_order
                LIMIT $start, $limit";
        $result = $this->connection->query($sql);

        // Fetch total number of items
        $total_items = $this->connection->query("SELECT COUNT(*) AS count FROM $table g $category_filter")->fetch_assoc()['count'];
        $total_pages = ceil($total_items / $limit);

        // Pagination links
        $prev_page = max(1, $page - 1);
        $next_page = min($total_pages, $page + 1);

        return [
            'items' => $result,
            'total_pages' => $total_pages,
            'prev_page' => $prev_page,
            'next_page' => $next_page,
            'latest' => $date_order,
            'sort' => $category_filter
        ];
    }

    public function populateLibrary($id, $limit = 5)
    {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($page - 1) * $limit;

        $date_order = "";
        if (isset($_GET['latest']) && $_GET['latest'] == true) {
            $date_order = "ORDER BY g.Upload_Date DESC";
        }

        $category_filter = "";
        if (isset($_GET['sort']) && !empty($_GET['sort'])) {
            $category = $_GET['sort'];
            $category_filter = "AND g.Category = '$category'";
        }

        $sql = "SELECT r.*, t.*, g.*, d.*, u.*
            FROM receipt r
            INNER JOIN transaction t ON r.Transaction_ID = t.Transaction_ID
            INNER JOIN games g ON t.Game_ID = g.Game_ID
            INNER JOIN developer d ON g.Developer_ID = d.Developer_ID
            INNER JOIN users u ON t.User_ID = u.User_ID
            WHERE u.User_ID = $id $category_filter
            $date_order
            LIMIT $start, $limit";
        $result = $this->connection->query($sql);

        $total_items = $this->connection->query("SELECT COUNT(*) AS count FROM receipt r INNER JOIN transaction t ON r.Transaction_ID = t.Transaction_ID INNER JOIN games g ON t.Game_ID = g.Game_ID INNER JOIN developer d ON g.Developer_ID = d.Developer_ID INNER JOIN users u ON t.User_ID = u.User_ID WHERE u.User_ID = $id $category_filter")->fetch_assoc()['count'];
        $total_pages = ceil($total_items / $limit);

        $prev_page = max(1, $page - 1);
        $next_page = min($total_pages, $page + 1);

        return [
            'items' => $result,
            'total_pages' => $total_pages,
            'prev_page' => $prev_page,
            'next_page' => $next_page,
            'latest' => $date_order,
            'sort' => $category_filter
        ];
    }


    public function paginateDevs($table, $limit = 4)
    {

        $pageDev = isset($_GET['page']) ? $_GET['page'] : 1;
        $start = ($pageDev - 1) * $limit;

        $sql = "SELECT * FROM $table g
                INNER JOIN developer d ON g.Developer_ID = d.Developer_ID
                LIMIT $start, $limit";
        $resultDev = $this->connection->query($sql);

        $total_items = $this->connection->query("SELECT COUNT(*) AS count FROM $table")->fetch_assoc()['count'];
        $total_pagesDev = ceil($total_items / $limit);

        $prev_pageDev = max(1, $pageDev - 1);
        $next_pageDev = min($total_pagesDev, $pageDev + 1);

        return [
            'itemsDev' => $resultDev,
            'total_pagesDev' => $total_pagesDev,
            'prev_pageDev' => $prev_pageDev,
            'next_pageDev' => $next_pageDev,
        ];
    }


    public function fetchGameData()
    {
        $games = [];
        $count = 1;

        // Fetch game details from the database
        $sql =
            "SELECT * FROM featured_post
            LEFT JOIN games ON featured_post.Game_ID = games.Game_ID
            LEFT JOIN developer ON games.Developer_ID = developer.Developer_ID";
        $result = $this->connection->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $games["Game $count"] = [
                    'id' => $row['Game_ID'],
                    'title' => $row['Game_Name'],
                    'developer' => $row['Developer_Name'],
                    'genre' => $row['Category'],
                    'description' => strip_tags($row['Game_Desc']),
                    'upload_date' => $row['Upload_Date'],
                    'price' => $row['Price']
                ];
                $count++;
            }
        } else {
            echo "No games found.";
        }

        return $games;
    }

    public function getGameInfo($id)
    {
        $game_info = [];

        $sql = "SELECT g.*, d.Developer_Name
                FROM Games g
                INNER JOIN Developer d ON g.Developer_ID = d.Developer_ID
                WHERE g.Game_ID = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $game_info = $result->fetch_assoc();
        }

        return $game_info;
    }

    public function getTransaction_Data($user_ID = null, $orderToday = null, $orderWeek = null)
    {
        if ($user_ID != null) {
            $user_ID = intval($user_ID);
        }
        $sql =  "SELECT
                    receipt.Receipt_ID,
                    receipt.Receipt_Date,
                    receipt.Receipt_Time,
                    transaction.Transaction_ID,
                    users.User_ID,
                    users.Name,
                    users.User_Name,
                    users.Email,
                    games.Game_ID,
                    games.Game_Name,
                    games.Price,
                        games.Category,
                        developer.Developer_Name
                FROM
                    receipt
                INNER JOIN
                    transaction ON receipt.Transaction_ID = transaction.Transaction_ID
                INNER JOIN
                    users ON transaction.User_ID = users.User_ID
                INNER JOIN
                    games ON transaction.Game_ID = games.Game_ID
                INNER JOIN
                    developer ON games.Developer_ID = developer.Developer_ID ";
        if ($user_ID != null) {
            $sql .= " WHERE users.User_ID = $user_ID ";
        }
        if ($orderToday != null) {
            $sql .= " AND receipt.Receipt_Date = CURDATE() ORDER BY receipt.Receipt_Time DESC";
        }
        if ($orderWeek != null) {
            $sql .= " AND receipt.Receipt_Date >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND receipt.Receipt_Date < DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 1 WEEK)  ORDER BY receipt.Receipt_Date DESC,receipt.Receipt_Time DESC";
        }
        //die($sql);
        // Execute the SQL query
        $transactionData = $this->sqlExecute($sql);

        return $transactionData;
    }

    /*---------------------------------------------------------------------- Last ----------------------------------------------------------------------*/
    public function getAverageRating($gameId)
    {
        $avgRatingSql = "SELECT AVG(Rate_Score) as 'Rate_Score' FROM rating WHERE Game_ID = ?";
        $stmt = $this->connection->prepare($avgRatingSql);
        $stmt->bind_param('i', $gameId);
        $stmt->execute();
        $result = $stmt->get_result();
        $avgRatingRow = $result->fetch_assoc();
        return $avgRatingRow['Rate_Score'];
    }

    public function getUserReview($gameId, $userId)
    {
        $userRevSql = "SELECT *, users.User_Name FROM rating JOIN users ON rating.User_ID = users.User_ID WHERE rating.Game_ID = ? AND rating.USER_ID = ?";
        $stmt = $this->connection->prepare($userRevSql);
        $stmt->bind_param('is', $gameId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if any rows were returned
        if ($result->num_rows > 0) {
            return $result; // Return the result set
        } else {
            return null; // Return null if no rows were found
        }
    }


    public function fetchReviews($gameId, $userId, $sorting = false)
    {
        $sortingQueryParam = "";
        if ($sorting === "rating") {
            $sortingQueryParam = "ORDER BY Rate_Score DESC";
        }

        $revSql = "SELECT *, users.User_Name FROM rating JOIN users ON rating.User_ID = users.User_ID WHERE rating.Game_ID = ? AND rating.USER_ID != ? $sortingQueryParam LIMIT 5";
        $stmt = $this->connection->prepare($revSql);
        $stmt->bind_param('is', $gameId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $totalReview = mysqli_num_rows($result);

        return [
            'result' => $result,
            'total_review' => $totalReview
        ];
    }

    public function checkTransaction($userId, $gameId)
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) AS count FROM transaction WHERE User_ID = ? AND Game_ID = ?");
        $stmt->bind_param("ii", $userId, $gameId);

        $stmt->execute();

        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        return $row['count'] > 0;
    }
    public function getdevelopers()
    {
        $dev_sql = "SELECT * FROM developer";
        $dev_result = mysqli_query($this->connection, $dev_sql);
        return $dev_result;
    }

    function submitReview($review, $rating, $user_id, $id)
    {
        $review_sql = "INSERT INTO Rating (Rate_Score, Review, Review_Date, Review_Time, User_ID, Game_ID) VALUES (?, ?, CURDATE(), CURTIME(), ?, ?)";
        $stmt = mysqli_prepare($this->connection, $review_sql);
        mysqli_stmt_bind_param($stmt, "isii", $rating, $review, $user_id, $id);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) > 0) {

            $this->updateAverageRating($id);
            $game_reviewed = true;
            header("Location: {$_SERVER['PHP_SELF']}?id=$id#rev-section");
            exit();
            return $game_reviewed;
        }
        mysqli_stmt_close($stmt);
    }

    // Function to edit a review
    function editReview($review, $rating, $user_id, $id)
    {
        $edit_review_sql = "UPDATE Rating SET Rate_Score = '$rating', Review = '$review' WHERE User_ID = '$user_id' AND Game_ID = '$id'";
        mysqli_query($this->connection, $edit_review_sql);

        $this->updateAverageRating($id);
        header("Location: {$_SERVER['PHP_SELF']}?id=$id#rev-section");
        exit();
    }

    function updateAverageRating($game_id)
    {
        $avg_rating_query = "SELECT AVG(Rate_Score) AS AverageRating FROM Rating WHERE Game_ID = '$game_id'";
        $result = mysqli_query($this->connection, $avg_rating_query);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
            $average_rating = $row['AverageRating'];

            $update_game_rating_sql = "UPDATE games SET Game_Rating = '$average_rating' WHERE Game_ID = '$game_id'";
            mysqli_query($this->connection, $update_game_rating_sql);
        }
    }


    function deleteReview($user_id, $id)
    {
        $delete_review_sql = "DELETE FROM Rating WHERE User_ID = '$user_id' AND Game_ID = '$id'";
        mysqli_query($this->connection, $delete_review_sql);

        $this->updateAverageRating($id);
        header("Location: {$_SERVER['PHP_SELF']}?id=$id#rev-section");
        exit();
    }

    function checkUserReview($id, $user_id)
    {
        if (isset($_SESSION['User'])) {
            $user_rev = "SELECT *, users.User_Name FROM rating JOIN users ON rating.User_ID = users.User_ID WHERE rating.Game_ID = $id AND rating.USER_ID = '$user_id'";
            $user_rev_result = mysqli_query($this->connection, $user_rev);
            return $user_rev_result;
        }
        return null;
    }
}
