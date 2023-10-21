<?php
 // Global database/session initialization.
 require_once "./config.php";
 
 if(!isset($_SESSION['loggedin'])){
  header("Location: ./login.php");
  exit;
 }
 
 if(!empty(trim($_GET["folder"]))) {$folder_id = trim($_GET["folder"]);}
 if(!empty(trim($_GET["bookmark"]))) {$bookmark_id = trim($_GET["bookmark"]);}
?>
<html>
  <head>
    <title>Deleting... - Nilamark</title>    
    <link rel="stylesheet" href="styles.css">
  </head>
  <body>
    <?php include 'header.php'; ?>
    
    <?php    
        if(isset($folder_id)) {
            // Get parent of the folder we're deleting.
            $sql0 = $pdo->prepare("SELECT parent_folder FROM nm_folders INNER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :id");
            $sql0->bindParam(":id", $folder_id, PDO::PARAM_STR);
            $sql0->execute();
            $old_folder = $sql0->fetch();
            
            // Delete the folder.
            $sql1 = $pdo->prepare("DELETE FROM nm_folders WHERE id = :id");
            $sql1->bindParam(":id", $folder_id, PDO::PARAM_STR);
            $sql1->execute();
            $sql2 = $pdo->prepare("DELETE FROM nm_tree WHERE id = :id AND type = 'folder'");
            $sql2->bindParam(":id", $folder_id, PDO::PARAM_STR);
            $sql2->execute();
            
            redirect("./bookmarks.php?folder=" . $old_folder['parent_folder']);
        }
        if(isset($bookmark_id)) {
            // Get parent folder of the bookmark.
            $sql0 = $pdo->prepare("SELECT parent_folder FROM nm_bookmarks INNER JOIN nm_tree ON nm_bookmarks.id = nm_tree.id WHERE type = 'bookmark' AND nm_bookmarks.id = :id");
            $sql0->bindParam(":id", $bookmark_id, PDO::PARAM_STR);
            $sql0->execute();
            $old_folder = $sql0->fetch();
            
            // Delete the bookmark.
            $sql1 = $pdo->prepare("DELETE FROM nm_bookmarks WHERE id = :id");
            $sql1->bindParam(":id", $bookmark_id, PDO::PARAM_STR);
            $sql1->execute();
            $sql2 = $pdo->prepare("DELETE FROM nm_tree WHERE id = :id AND type = 'bookmark'");
            $sql2->bindParam(":id", $bookmark_id, PDO::PARAM_STR);
            $sql2->execute();
            
            redirect("./bookmarks.php?folder=" . $old_folder['parent_folder']);
        }    
    ?>

  </body>
</html>
