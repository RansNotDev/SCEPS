<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Log in</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./devnull_access/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="./devnull_access/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="./devnull_access/dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <div class="login-logo">
      <a href="../../index2.html"><b>School Club Event </b>Planner System</a>
    </div>
    <!-- /.login-logo -->
    <div class="card">
      <div class="card-body login-card-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <form action="index.php" method="post">
        <div class="input-group mb-3">
    <input type="text" class="form-control" name="username" placeholder="Username"
          title="Username must be up to 12 characters with no Whitespaces."
          minlength="6" maxlength="12" required> <!-- It only accepts Username that having 12 characters with no Whitespaces.-->
    <div class="input-group-append">
      <div class="input-group-text">
        <span class="fas fa-user"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
          <input type="password" class="form-control" name="password" placeholder="Password" 
        pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])\S{6,20}$"
          title="Password must be 6-20 characters, contain number, uppercase, and lowercase letter, with no Whitespaces."
          minlength="6" maxlength="20" required> <!-- It only accepts password that having least one digit, one uppercase, and one lowercase letter, with no Whitespaces-->
    <div class="input-group-append">
    <div class="input-group-text">
    <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-8">
              <div class="icheck-primary">
                <input type="checkbox" id="remember">
                <label for="remember">
                  Remember Me
                </label>
              </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
              <button type="submit" class="btn btn-primary btn-block" value="Submit">Sign In</button>
            </div>
            <!-- /.col -->
          </div>
        </form>
        <br><br>
      </div>
      <!-- /.login-card-body -->
    </div>
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="../../plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../../dist/js/adminlte.min.js"></script>
</body>

</html>

<?php
include './connections/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the password and role from the database
    $stmt = $conn->prepare("SELECT password, role FROM club_members WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_hashed_password, $role);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $stored_hashed_password)) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Close the statement before redirection or alert
            $stmt->close();

            // Redirect based on the user's role
            if ($role === 'Admin') {
                header("Location: dashboard.php");
                exit(); // Stop further execution
            } else {
                echo "<script type='text/javascript'>alert('Invalid Credentials.');</script>";
                exit(); // Stop further execution after showing the alert
            }
        } else {
            echo "<script type='text/javascript'>alert('Invalid Credentials.');</script>";
            $stmt->close(); // Close the statement before exit
            exit(); // Stop further execution after showing the alert
        }
    } else {
        echo "<script type='text/javascript'>alert('Invalid Credentials.');</script>";
        $stmt->close(); // Close the statement before exit
        exit(); // Stop further execution after showing the alert
    }
}
?>