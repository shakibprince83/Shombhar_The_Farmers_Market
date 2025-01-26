<?php
include("config.php");

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $sql = "SELECT * FROM customer WHERE reset_token = '$token' AND reset_token_expiry > NOW()";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

            // Update the password and clear the token
            $sql = "UPDATE customer SET password = '$newPassword', reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = '$token'";
            if (mysqli_query($con, $sql)) {
                echo "Password has been reset successfully.";
                echo "<a href='login.php'>Click here to login</a>";
            } else {
                echo "Failed to reset password.";
            }
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>
<form method="post">
    <label for="password">Enter your new password:</label>
    <input type="password" name="password" id="password" required>
    <button type="submit">Reset Password</button>
</form>
