<?php
session_start();
require '../Database/MoistFunctions.php';
$moistFunctions = new MoistFunctions($connection);

if (!isset($_SESSION['Admin']) && !isset($_SESSION['User'])) {
    header("Location: ../Main/index.php");
}

if (isset($_SESSION['User'])) {
    $userID = $_SESSION['User_ID'];
    if (isset($_GET['Today'])) {
        $transactionData = $moistFunctions->getTransaction_Data($userID, true);
    } elseif (isset($_GET['Week'])) {
        $transactionData = $moistFunctions->getTransaction_Data($userID, null, true);
    } else
        $transactionData = $moistFunctions->getTransaction_Data($userID);
} elseif (isset($_SESSION['Admin'])) {
    if (isset($_GET['Today'])) {
        $transactionData = $moistFunctions->getTransaction_Data(null, true);
    } elseif (isset($_GET['Week'])) {
        $transactionData = $moistFunctions->getTransaction_Data(null, null, true);
    } else
        $transactionData = $moistFunctions->getTransaction_Data();
} else echo "No Transaction Data";

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/header_css.css">
    <link rel="stylesheet" href="../css/transactions_css.css?+1">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <title>Transaction Records</title>
</head>

<body>
    <?php include '../header.php'; ?>
    <!------------------------------------------------ Receipt Popup ------------------------------------------------>
    <div class="modal fade" tabindex="-1" id="Receipt-Form" aria-labelledby="UpdatePopup" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="Receipt-FormPop">
                <div class="modal-body" id="ReceiptForm-Container">
                    <!---------- Content is in ../Popups/purchaseReceipt.php ---------->
                </div>
            </div>
        </div>
    </div>

    <div class="transaction-sec">
        <?php if (isset($_SESSION['Admin'])) { ?>
            <p class="trans-head">Transaction Records</p>
        <?php } ?>
        <?php if (isset($_SESSION['User'])) { ?>
            <p class="trans-head">Purchase History</p>
        <?php } ?>

        <div id="transaction-records">
            <div id="transaction-filters">
                <form action="" method="get">
                    <button id="today-btn" name="Today" <?php if (isset($_GET['Today'])) echo "style='background-color: rgb(189, 196, 212);
    color: rgb(0, 0, 0);'"; ?>>Today</button>
                    <button id="this-week-btn" name="Week" <?php if (isset($_GET['Week'])) echo "style='background-color: rgb(189, 196, 212);
    color: rgb(0, 0, 0);'"; ?>>This Week</button>
                    <button id="before-btn" name="All" <?php if (isset($_GET['All'])) echo "style='background-color: rgb(189, 196, 212);
    color: rgb(0, 0, 0);'";?>>All Transactions</button>
                </form>
            </div>
            <div id="transaction-list">
                <?php
                if ((isset($_SESSION['User'])) || (isset($_SESSION['Admin']))) {
                    if (count($transactionData) > 0) {
                        foreach ($transactionData as $transaction) :
                ?>
                            <div class="transaction">
                                <div class="transaction-left">
                                    <h2><?php echo $transaction[9]; ?></h2>
                                    <?php if (isset($_SESSION['Admin'])) { ?>
                                        <p>Purchased by: <strong><?php echo $transaction[6]; ?></strong></p>
                                    <?php } ?>
                                    <p>Date: <?php echo $transaction[1]; ?> <?php echo $transaction[2]; ?></p>
                                </div>
                                <div class="transaction-right">
                                    <p><?php if ($transaction[10] > 0) {
                                            echo '$' . $transaction[10];
                                        } else {
                                            echo "FREE";
                                        }
                                        ?>
                                    </p>
                                    <button data-bs-toggle="modal" data-bs-target='#Receipt-Form' id="edit-id" onclick="popupReceipt(<?= $transaction[0]; ?>)">
                                        View Receipt</button>
                                </div>
                            </div>
                <?php
                        endforeach;
                    } else {
                        echo "
                            <div class='transaction'>
                                <h1>No Transaction Records to Display...</h1>
                            </div>
                            ";
                    }
                }
                ?>
            </div>
        </div>


</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
</script>

<script>
    let receiptForm = document.getElementById("Receipt-FormPop");
    let receiptForm_Container = document.getElementById("ReceiptForm-Container");

    function popupReceipt(value) {
        var id = value;

        function sendToPHP(value) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    console.log("Value sent to PHP successfully");
                    receiptForm.classList.add("show-edit");
                    receiptForm_Container.classList.add("show-edit-container");
                    receiptForm_Container.scrollTop = 0;
                    document.body.style.overflow = 'hidden';
                    document.documentElement.style.overflow = 'hidden';
                    document.getElementById("ReceiptForm-Container").innerHTML = this.responseText;
                }
            };
            xhttp.open("GET", "../Popups/viewReceipt.php?id=" + value, true);
            xhttp.send();
        }
        sendToPHP(id);
    }
</script>