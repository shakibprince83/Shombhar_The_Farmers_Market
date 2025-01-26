<?php
if(!isset($_SESSION)) { session_start(); }
error_reporting(E_ALL & ~E_NOTICE  &  ~E_STRICT  &  ~E_WARNING);
include_once("config.php");
include_once("dbconnection.php");

// Authentication check
if(!isset($_SESSION['customerid']) && !isset($_SESSION['sellerid']) && !isset($_SESSION['workerid'])) {
    header("Location: customerreglogin.php");
    exit();
}

// Determine user type and ID
if(isset($_SESSION['customerid'])) {
    $user_type = 'customer';
    $user_id = $_SESSION['customerid'];
} elseif(isset($_SESSION['sellerid'])) {
    $user_type = 'seller';
    $user_id = $_SESSION['sellerid'];
} elseif(isset($_SESSION['workerid'])) {
    $user_type = 'worker';
    $user_id = $_SESSION['workerid'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Chat System</title>
    <!-- Include your existing CSS files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Chat container styling */
        .chat-container {
            display: flex;
            height: 80vh;
            margin-top: 80px;
        }

        /* User list styling */
        .user-list {
            width: 250px;
            background-color: #f1f1f1;
            overflow-y: auto;
            padding: 10px;
        }

        /* Chat area styling */
        .chat-area {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #ddd;
        }

        /* Message list styling */
        .message-list {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }

        /* Input area styling */
        .message-input {
            display: flex;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .message-input textarea {
            flex-grow: 1;
            margin-right: 10px;
            resize: none;
            border-radius: 5px;
            padding: 10px;
            border: 1px solid #ddd;
        }

        /* User item styling */
        .user-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
        }

        .user-item:hover {
            background-color: #e1e1e1;
        }

        /* Message bubble styling */
        .sent-message {
            align-self: flex-end;
            max-width: 60%;
            background-color: #dcf8c6;
            margin: 5px 0;
            padding: 10px 15px;
            border-radius: 15px 15px 0 15px;
            word-wrap: break-word;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .received-message {
            align-self: flex-start;
            max-width: 60%;
            background-color: #ffffff;
            margin: 5px 0;
            padding: 10px 15px;
            border-radius: 15px 15px 15px 0;
            word-wrap: break-word;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <?php include_once('header.php'); ?>

    <div class="container chat-container">
        <div class="user-list" id="userList">
            <!-- Users will be dynamically loaded here -->
        </div>
        <div class="chat-area">
            <div class="message-list" id="messageList">
                <!-- Messages will be dynamically loaded here -->
            </div>
            <div class="message-input">
                <textarea id="messageText" placeholder="Type your message..." rows="1"></textarea>
                <button id="sendMessage" class="btn btn-primary">Send</button>
            </div>
        </div>
    </div>

    <script>
        const currentUserType = '<?php echo $user_type; ?>';
        const currentUserId = <?php echo $user_id; ?>;
    </script>
    <script src="messages.js"></script>
</body>
</html>
