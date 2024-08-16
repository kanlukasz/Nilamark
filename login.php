<?php
 // Global database/session initialization.
 require_once "./config.php";
 require_once "./cookies.php";

 // Check if we have a location to return to after logging in.
 $redirect = NULL;
 if($_GET['location'] != '') {
  $redirect = $_GET['location'];
 }

 // If we're already logged in, redirect back to the homepage.
 if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
  header("location: ./bookmarks.php?folder=1");
  exit;
 }

 // Check if there's a cookie that should be used to log in.
 if(isset($_COOKIE["remember_me"])) {
  $token = filter_input(INPUT_COOKIE, 'remember_me', FILTER_SANITIZE_STRING);
  if(token_is_valid($token, $pdo)){
   $id = find_user_by_token($token, $pdo);
   if($id['id']) {
    $stmt = $pdo->prepare("SELECT username, admin FROM accounts WHERE id = :id");
    $stmt->bindParam(":id", $id['id'], PDO::PARAM_STR);
    if($stmt->execute()){
     if($row = $stmt->fetch()){
      session_start();
      $_SESSION["loggedin"] = true;
      $_SESSION["id"] = $id;
      $_SESSION["username"] = $row["username"];
      $_SESSION["admin"] = $row["admin"];

      // If we have a redirect URL, go there.
      if($redirect) {
       header("location:" . $redirect);
      } else { // Otherwise, go back to the home page.
       header("location: bookmarks.php?folder=1");
      }
     }
    }
   }
  }
 }
 
 $username = "";
 $password = "";
 $username_err = "";
 $password_err = "";
 
 // Check the input if we POST to this file.
 if($_SERVER["REQUEST_METHOD"] == "POST"){

  // Check if username is empty.
  if(empty(trim($_POST["username"]))){
    $username_err = "Please enter a username.";
  } else{
   $username = trim($_POST["username"]);
  }
   
  // Check if password is empty.
  if(empty(trim($_POST["password"]))){
   $password_err = "Please enter a password.";
  } else{
   $password = trim($_POST["password"]);
  }
  
  // Check credentials against database.
  if(empty($username_err) && empty($password_err)){
   if ($stmt = $pdo->prepare("SELECT id, username, password, admin FROM accounts WHERE username = :username")){
    $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
    $param_username = trim($_POST["username"]);
    if($stmt->execute()){
     // Check if the account exists. If it does, compare its password with the provided password.
     if($stmt->rowCount() == 1){
      if($row = $stmt->fetch()){
       $id = $row["id"];
       $username = $row["username"];
       $hashed_password = $row["password"];
       $admin = $row["admin"];
       if(password_verify($password, $hashed_password)){
        // If we made it here, the password is correct. Start a new session!
        session_start();
        $_SESSION["loggedin"] = true;
        $_SESSION["id"] = $id;
        $_SESSION["username"] = $username;
        $_SESSION["admin"] = $admin;

        // Check if we want to remember the session.
        if($_POST["remember"] == "remembered") {
         $selector = bin2hex(random_bytes(16));
         $validator = bin2hex(random_bytes(32));

         $expiry_sec = time() + 30 * 24 * 60 * 60;
         $expiry_date = date('Y-m-d H:i:s', $expiry_sec);
         $validator_hashed = password_hash($validator, PASSWORD_DEFAULT);

         $remember_stmt = $pdo->prepare("INSERT INTO nm_logins(account_id, expiry, token_selector, token_validator) VALUES (:account_id, :expiry_date, :selector, :validator_hashed)");
         $remember_stmt->bindParam(":account_id", $id);
         $remember_stmt->bindParam(":expiry_date", $expiry_date);
         $remember_stmt->bindParam(":selector", $selector);
         $remember_stmt->bindParam(":validator_hashed", $validator_hashed);

         if($remember_stmt->execute()) {
          setcookie('remember_me', $selector . ":" . $validator, $expiry_sec);
         }
        }

        // Send us back to the home page.
        header("location: bookmarks.php?folder=1");
       } else{
        // If we made it here, the password's not correct. Display an error message.
        $password_err = "The password you entered was not correct.";
       }
      }
     } else{
      // If we made it here, the username does not exist. Display an error message.
      $username_err = "The username you entered does not exist.";
     }
     // Close the statement.
     unset($stmt);
    }
   }
  }
 }
 unset($pdo);
?>

<!DOCTYPE html>
<html>
 <head>
  <link rel="stylesheet" type="text/css" href="./styles.css">
  <link rel="icon" href="favicon.svg">
 </head>
 
 <body>
  <h2 class="login-title">Log In - Nilamark</h2>
  <form class="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
   <div class="form-group login-input <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
    <label>Username:</label>
    <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
    <span class="help-block"><?php echo $username_err; ?></span>
   </div>    
   <div class="form-group login-input <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
    <label>Password:</label>
    <input type="password" name="password" class="form-control">
    <span class="help-block"><?php echo $password_err; ?></span>
   </div>
   <div class="form-group login-input">
    <label>Remember me:</label>
    <input type="checkbox" name="remember" id="remember" value="remembered" checked="true" />
   </div>
   <div class="form-group">
    <button type="submit" class="button-orange" id="login-button" value="Login">Log In</button>
   </div>
  </form>
  
  <?php require_once "./footer.php"; ?>
 </body>
</html>
