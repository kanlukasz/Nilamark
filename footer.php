<?php
 echo "<hr/><div class=\"footer\">";
 if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
  echo "User: " . $_SESSION["username"] . ". ";
 }
 echo "Session: " . session_id() . ".<br/>Powered by <a href='https://gitlab.com/nerdonthestreet/Nilamark' target='_blank' class='footer-link'>Nilamark</a>.</div>";
 
 if (!is_writable(session_save_path())) {
  echo 'Session path "'.session_save_path().'" is not writable for PHP!'; 
 }
?>
