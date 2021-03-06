<?php

    // Initialize the session
    session_start();

    if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
        header('location: welcome.php');
        exit;
    }

    // Include config file
    require_once 'config.php';

    // Define variables and initialize with empty values
    $username = $password = '';
    $usernameErr = $passwordErr = $errorMessage = '';

    // this check if the method passed is POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        // Check if username is empty
        if (empty(trim($_POST['username']))) {
            $usernameErr = 'Please enter username';
        } else {
            $username = trim($_POST['username']);
        }

        // Check if password is empty
        if (empty(trim($_POST['password']))) {
            $passwordErr = 'Please enter your password';
        } else {
            $password = trim($_POST['password']);
        }

        // Validate credentials
        // && AND
        // || OR
        // != NOT
        if (empty($usernameErr) && empty($passwordErr)) {
            // Prepare a select statement
            $sql = 'SELECT id, username, password FROM users WHERE username = ?';
            $statement = mysqli_prepare($link, $sql);
            if ($statement) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($statement, 's', $paramUsername);

                // Set parameters
                $paramUsername = $username;

                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($statement)) {
                    // Store result
                    mysqli_stmt_store_result($statement);

                    if (mysqli_stmt_num_rows($statement) == 1) {
                        // Bind result variables
                        mysqli_stmt_bind_result($statement, $id, $username, $hashedPassword);

                        if (mysqli_stmt_fetch($statement)) {
                            if (password_verify($password, $hashedPassword)) {
                                // Password is correct, so start a new session
                                session_start();

                                // Store data in session variables
                                $_SESSION['loggedIn'] = true;
                                $_SESSION['id'] = $id;
                                $_SESSION['username'] = $username;

                                // Redirect user to welcome page
                                header('location: welcome.php');
                            } else {
                                // Display an error message if password is not valid
                                // $passwordErr = 'The username or password you entered was not valid';
                                $errorMessage = 'The username or password you entered was not valid';
                            }
                        }
                    } else {
                        // Display error message if username doesn't exist
                        // $usernameError = 'The username or password you entered was not valid';
                        $errorMessage = 'The username or password you entered was not valid';
                    }
                } else {
                    echo 'Oops! Something went wrong. Please try again later.';
                }

                // Close statement
                mysqli_stmt_close($statement);
            }
        }

        // Close connection
        mysqli_close($link);
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="wrapper">
            <h2>Login</h2>
            <p>Please fill in your credentials to login</p>
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                <div class="form-group <?= (!empty($errorMessage) || !empty($usernameErr)) ? 'has-error' : ''; ?>">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= $username ?>">
                    <span class="help-block"><?= $usernameErr ?></span>
                </div>
                <div class="form-group <?= (!empty($errorMessage) || !empty($passwordErr)) ? 'has-error' : ''; ?>">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" value="<?= $password ?>">
                    <span class="help-block"><?= $passwordErr ?></span>
                    <span class="help-block"><?= $errorMessage ?></span>
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-primary" value="Login">
                </div>
                <p>Don't have an account? <a href="register.php">Register</a></p>
            </form>
        </div>
    </body>
</html>