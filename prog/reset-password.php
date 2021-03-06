<?php
    // Initialize session
    session_start();

    // Check if the user is logged in, otherwise redirect to login page
    if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
        header('location: login.php');
        exit;
    }

    // Include config file
    require_once 'config.php';

    // Define variables and initialize with empty values
    $newPassword = $confirmPassword = '';
    $newPasswordErr = $confirmPasswordErr = '';

    // Processing form data when form is submitted
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Validate new password
        if (empty(trim($_POST['new_password']))) {
            $newPasswordErr = 'Please enter new password';
        } else if (strlen(trim($_POST['new_password'])) < 6) {
            $newPasswordErr = 'Password must have at least 6 characters';
        } else {
            $newPassword = trim($_POST['new_password']);
        }

        // Validate confirm password
        if (empty(trim($_POST['confirm_password']))) {
            $confirmPasswordErr = 'Please confirm the password';
        } else {
            $confirmPassword = trim($_POST['confirm_password']);
            if (empty($newPasswordErr) && ($newPassword != $confirmPassword)) {
                $confirmPasswordErr = 'Password did not match.';
            }
        }

        // Check input errors before updating the database
        if (empty($newPasswordErr) && empty($confirmPasswordErr)) {
            // Prepare an update statement
            $sql = 'UPDATE users SET password = ? WHERE id = ?';
            $statement = mysqli_prepare($link, $sql);
            if ($statement) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($statement, 'si', $paramPassword, $paramId);

                // Set parameters
                $paramPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $paramId = $_SESSION['id'];

                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($statement)) {
                    // Password updated successfully. Destroy the session, and redirect to the login page
                    session_destroy();
                    header('location: login.php');
                    exit;
                } else {
                    echo 'Oops! Something went wrong. Please try again.';
                }

                // Close statement
                mysqli_stmt_close($statement);
            }
        }

        // Close connection
        mysqli_close($link);

        //$2y$10$o73F9dhLmJ6YvVHQLcwQh.xSu4fu5wh2pU24xjfXJ4VaTKcSD7nb6
        //$2y$10$O39T9DjD7.rxBpQ1J8tVyOcdHW4XOOsFaYwOj9vaZKW4aQqMqNaI.
        //$2y$10$.VREt2tpPzzm5J0ehQKaWu8X.dFu1i8bv3EH4NzmPJpedO.o6T.ru
    }
?>
<!DOCTYPE html>
<html>
    <meta charset="UTF-8">
    <head>
        <title>Reset Password</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="wrapper">
            <h2>Reset Password</h2>
            <p>Please fill out this form to reset your password</p>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                <div class="form-group <?= !empty($newPasswordErr) ? 'has-error' : ''?>">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" value="<?= $newPassword ?>">
                    <span class="help-block"><?= $newPasswordErr ?></span>
                </div>

                <div class="form-group <?= !empty($confirmPasswordErr) ? 'has-error' : ''?>">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" value="<?= $confirmPassword ?>">
                    <span class="help-block"><?= $confirmPasswordErr ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" value="Submit" class="btn btn-primary">
                    <a class="btn btn-link" href="welcome.php">Cancel</a>
                </div>
            </form>
        </div>
    </body>
</html>