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

// Get message data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$receiver_id = $data['receiver_id'];
$receiver_type = $data['receiver_type'];
$message_text = mysqli_real_escape_string($con, trim($data['message_text']));

if(empty($message_text)) {
    die(json_encode(['error' => 'Message cannot be empty']));
}

// Insert message
$query = "INSERT INTO messages 
          (sender_type, sender_id, receiver_type, receiver_id, message_text) 
          VALUES 
          ('$current_user_type', '$current_user_id', '$receiver_type', '$receiver_id', '$message_text')";

if(mysqli_query($con, $query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Failed to send message']);
}
?>