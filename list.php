<?php
 // Global database/session initialization.
 require_once "./config.php";
 
 if(!isset($_SESSION['loggedin'])){
    header("Location: ./login.php");
    exit;
 }

?>
<html>
  <head>
    <title>Home - Nilamark</title>    
    <link rel="stylesheet" href="styles.css">
  </head>
  <body>
    <?php include 'header.php'; ?>
    <?php
        $sql = $pdo->prepare("SELECT id, title, url FROM nm_bookmarks");
        $sql->execute();
        $bookmarks_list = $sql->fetchAll();
        
        if (!empty($bookmarks_list)) {
            echo '<div id="bookmark-list">';
            // output data of each row
            foreach($bookmarks_list as &$row) {
                echo "id: " . $row["id"]. "<br/>Name: " . htmlspecialchars($row["title"]). "<br/>URL: " . $row["url"]. "<br/><br/><hr/><br/>";
                
                //echo "id: " . $row["id"]. " - Name: " . htmlspecialchars($row["title"]). " " . $row["url"]. "<br>";
                //echo "<div class='result'>";
                //echo "<a class='result-link' href='" . $row["url"] . "'>" . htmlspecialchars($row["title"]) . "</a>";
                //echo "<a href=./edit-bookmark.php?bookmark=" . $row["id"] . "><span class='edit-button'><img src='./assets/edit.svg'/></span></a></div>";
            }
            echo '</div>';
        } else {
            echo "0 bookmarks";
        }
        
    ?>
    
    <?php require_once "./footer.php"; ?>

  </body>
</html>
