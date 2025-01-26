<?php
include_once('dbconnection.php');

// Ensure user is logged in
if(!isset($_SESSION['customerid']) && !isset($_SESSION['sellerid']) && !isset($_SESSION['workerid'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

// Determine current user type and ID
if(isset($_SESSION['customerid'])) {
    $current_user_type = 'customer';
    $current_user_id = $_SESSION['customerid'];
    $query = "SELECT seller_id as id, seller_name as name, 'seller' as type FROM seller";
} elseif(isset($_SESSION['sellerid'])) {
    $current_user_type = 'seller';
    $current_user_id = $_SESSION['sellerid'];
    $query = "SELECT 
        (SELECT customer_id FROM customer WHERE customer_id IN (SELECT sender_id FROM messages WHERE receiver_type = 'seller' AND receiver_id = '$current_user_id')) as id,
        (SELECT customer_name FROM customer WHERE customer_id = id) as name,
        'customer' as type";
} elseif(isset($_SESSION['workerid'])) {
    $current_user_type = 'worker';
    $current_user_id = $_SESSION['workerid'];
    $query = "SELECT seller_id as id, seller_name as name, 'seller' as type FROM seller";
}

$result = mysqli_query($con, $query);
$users = [];

while($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode($users);
?>