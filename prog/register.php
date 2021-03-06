<?php
    require_once 'config.php';

    $username = $password = $confirmPassword = '';
    $usernameErr = $passwordErr = $confirmPasswordErr = '';

    // this check if the method passed is POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (empty(trim($_POST['username']))) {
            $usernameErr = 'Please enter a username';
        } else {
            $sql = 'SELECT * FROM users WHERE username = ?';
            $statement = mysqli_prepare($link, $sql);
            if ($statement) {
                mysqli_stmt_bind_param($statement, 's', $paramUsername);
                $paramUsername = trim($_POST['username']);
                if (mysqli_stmt_execute($statement)) {
                    mysqli_stmt_store_result($statement);

                    if (mysqli_stmt_num_rows($statement) == 1) {
                        $usernameErr = 'This username is already taken';
                    } else {
                        $username = $paramUsername;
                    }
                } else {
                  echo 'Oops! Something went wrong';
                }
            
            mysqli_stmt_close($statement);
            }
        }

        if (empty(trim($_POST['password']))) {
            $passwordErr = 'Please enter a password';
        } elseif (strlen(trim($_POST['password'])) < 6) {
            $passwordErr = 'Password must have 6 characters';
        } else {
            $password = trim($_POST['password']);
        }

        if (empty(trim($_POST['confirm_password']))) {
            $confirmPasswordErr = 'Please enter a confirm password';
        } else {
            $confirmPassword = trim($_POST['confirm_password']);
            if (empty($passwordErr) && ($password != $confirmPassword)) {
                $confirmPasswordErr = 'Password did not match.';
            }
        }

        if (empty($usernameErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
            $sql = 'INSERT INTO users (username, password) VALUES (?, ?)';
            $statement = mysqli_prepare($link, $sql);
            if ($statement) {
                mysqli_stmt_bind_param($statement, 'ss', $paramUsername, $paramPassword);

                $paramUsername = $username;
                $paramPassword = password_hash($password, PASSWORD_DEFAULT);

                if (mysqli_stmt_execute($statement)) {
                    header('login.php');
                } else {
                    echo 'Something went wrong. Please try again later.';
                }

                mysqli_stmt_close($statement);
            }
        }
    }

    mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up</title>
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <div class="wrapper">
    <h2>Sign Up</h2>
    <p>Please fill this form to sign up.</p>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
      <div class="form-group <?= !empty($usernameErr) ? 'has-error' : ''; ?>">
        <label>Username</label>
        <input type="text" name="username" class="form-control" value="<?= $username ?>">
        <span class="help-block"><?= $usernameErr ?></span>
      </div>

      <div class="form-group <?= !empty($passwordErr) ? 'has-error' : ''; ?>">
        <label>Password</label>
        <input type="password" name="password" class="form-control" value="">
        <span class="help-block"><?= $passwordErr ?></span>
      </div>

      <div class="form-group <?= !empty($confirmPasswordErr) ? 'has-error' : ''; ?>">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" value="">
        <span class="help-block"><?= $confirmPasswordErr ?></span>
      </div>

      <div class="form-group">
        <input type="submit" class="btn btn-primary" value="Submit">
        <input type="reset" class="btn btn-default" value="Reset">
        <p>Already have an account? <a href="login.php">Login here</a>
      </div>
    </form>
  </div>
</body>
