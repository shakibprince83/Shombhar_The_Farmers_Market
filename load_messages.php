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
} elseif(isset($_SESSION['sellerid'])) {
    $current_user_type = 'seller';
    $current_user_id = $_SESSION['sellerid'];
} elseif(isset($_SESSION['workerid'])) {
    $current_user_type = 'worker';
    $current_user_id = $_SESSION['workerid'];
}

$other_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$other_user_type = isset($_GET['user_type']) ? $_GET['user_type'] : '';

// Query to fetch messages
$query = "SELECT * FROM messages 
          WHERE (sender_type = '$current_user_type' AND sender_id = '$current_user_id' 
                 AND receiver_type = '$other_user_type' AND receiver_id = '$other_user_id')
             OR (sender_type = '$other_user_type' AND sender_id = '$other_user_id' 
                 AND receiver_type = '$current_user_type' AND receiver_id = '$current_user_id')
          ORDER BY timestamp";

$result = mysqli_query($con, $query);
$messages = [];

while($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

// Update read status
$update_query = "UPDATE messages SET is_read = 1 
                 WHERE sender_type = '$other_user_type' AND sender_id = '$other_user_id' 
                 AND receiver_type = '$current_user_type' AND receiver_id = '$current_user_id'";
mysqli_query($con, $update_query);

echo json_encode($messages);
?>