<?php
 // Global database/session initialization.
 require_once "./config.php";
 require_once "./nilacon.php";
 
 if(!isset($_SESSION['loggedin'])){
    header("Location: ./login.php");
    exit;
 }
 
 $current_folder_id = trim($_GET["folder"]);
?>
<html>
  <head>
    <title>Browse - Nilamark</title>    
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="favicon.svg">
  </head>
  <body>
    <?php include 'header.php'; ?>
    <?php
        $sql = $pdo->prepare("SELECT nm_folders.id,title,parent_folder FROM nm_folders INNER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :folder ORDER BY title");
        $sql->bindParam(":folder", $current_folder_id, PDO::PARAM_STR);
        $sql->execute();
        $current_folder = $sql->fetch();
        $parent_folder_id = $current_folder['parent_folder'] ?? null;
        
        if (!$current_folder) {
            $sql = $pdo->prepare("SELECT id,title FROM nm_folders WHERE nm_folders.id = :folder ORDER BY title");
            $sql->bindParam(":folder", $current_folder_id, PDO::PARAM_STR);
            $sql->execute();
            $current_folder = $sql->fetch();
        }
        
        if (isset($parent_folder_id)) {
            if($parent_folder_id == 1) {
                $sql = $pdo->prepare("SELECT title FROM nm_folders WHERE nm_folders.id = :folder");
            } else {
                $sql = $pdo->prepare("SELECT title,parent_folder FROM nm_folders LEFT OUTER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :folder");
            }
            $sql->bindParam(":folder", $parent_folder_id, PDO::PARAM_STR);
            $sql->execute();
            $parent_folder = $sql->fetch();
            
            if (isset($parent_folder['parent_folder'])) {
                if($parent_folder['parent_folder'] == 1) {
                    $sql = $pdo->prepare("SELECT title FROM nm_folders WHERE nm_folders.id = :folder");
                } else {
                    $sql = $pdo->prepare("SELECT title,parent_folder FROM nm_folders LEFT OUTER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :folder");
                }
                $sql->bindParam(":folder", $parent_folder['parent_folder'], PDO::PARAM_STR);
                $sql->execute();
                $parent2_folder = $sql->fetch();
            
                if (isset($parent2_folder['parent_folder'])) {
                    if($parent2_folder['parent_folder'] == 1) {
                        $sql = $pdo->prepare("SELECT title FROM nm_folders WHERE nm_folders.id = :folder");
                    } else {
                        $sql = $pdo->prepare("SELECT title,parent_folder FROM nm_folders LEFT OUTER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :folder");
                    }
                    $sql->bindParam(":folder", $parent2_folder['parent_folder'], PDO::PARAM_STR);
                    $sql->execute();
                    $parent3_folder = $sql->fetch();
                    
                    if (isset($parent3_folder['parent_folder'])) {
                        if($parent3_folder['parent_folder'] == 1) {
                            $sql = $pdo->prepare("SELECT title FROM nm_folders WHERE nm_folders.id = :folder");
                        } else {
                            $sql = $pdo->prepare("SELECT title,parent_folder FROM nm_folders LEFT OUTER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :folder");
                        }
                        $sql->bindParam(":folder", $parent3_folder['parent_folder'], PDO::PARAM_STR);
                        $sql->execute();
                        $parent4_folder = $sql->fetch();
                        
                        if (isset($parent4_folder['parent_folder'])) {
                            if($parent4_folder['parent_folder'] == 1) {
                                $sql = $pdo->prepare("SELECT title FROM nm_folders WHERE nm_folders.id = :folder");
                            } else {
                                $sql = $pdo->prepare("SELECT title,parent_folder FROM nm_folders LEFT OUTER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :folder");
                            }
                            $sql->bindParam(":folder", $parent4_folder['parent_folder'], PDO::PARAM_STR);
                            $sql->execute();
                            $parent5_folder = $sql->fetch();
                        }
                    }
                }
            }
        }
    ?>
    <div id="breadcrumbs">
    <?php
        if (isset($parent5_folder)) {
            echo "<a href='./bookmarks.php?folder=" . $parent4_folder['parent_folder'] . "'>" . $parent5_folder['title'] . "</a><img src='./assets/navigate-next.svg' alt='>'/>";
        }
        if (isset($parent4_folder)) {
            echo "<a href='./bookmarks.php?folder=" . $parent3_folder['parent_folder'] . "'>" . $parent4_folder['title'] . "</a><img src='./assets/navigate-next.svg' alt='>'/>";
        }
        if (isset($parent3_folder)) {
            echo "<a href='./bookmarks.php?folder=" . $parent2_folder['parent_folder'] . "'>" . $parent3_folder['title'] . "</a><img src='./assets/navigate-next.svg' alt='>'/>";
        }
        if (isset($parent2_folder)) {
            echo "<a href='./bookmarks.php?folder=" . $parent_folder['parent_folder'] . "'>" . $parent2_folder['title'] . "</a><img src='./assets/navigate-next.svg' alt='>'/>";
        }
        if (isset($parent_folder)) {
            echo "<a href='./bookmarks.php?folder=" . $parent_folder_id . "'>" . $parent_folder['title'] . "</a><img src='./assets/navigate-next.svg' alt='>'/>";
        }
        echo "<a href='./bookmarks.php?folder=" . $current_folder['id'] . "'>" . $current_folder['title'] . "</a> <img src='./assets/navigate-next.svg' style='visibility: hidden;' />";
    ?>
    </div>
    
    <div class="result-set">
    <h2>Folders:</h2>
    <a href="./create-folder.php?parent=<?php echo $current_folder_id; ?>" class="add-button"><img src="./assets/add-circle.svg" /></a>

    <?php
        $sql = $pdo->prepare("SELECT nm_folders.id,title,parent_folder FROM nm_folders INNER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND parent_folder = :folder ORDER BY title");
        $sql->bindParam(":folder", $current_folder_id, PDO::PARAM_STR);
        $sql->execute();
        $folders_result = $sql->fetchAll();

        if (!empty($folders_result)) {
            // output data of each row
            foreach($folders_result as &$row) {
                echo "<div class='result'>";
                echo "<img src='./assets/folder.svg' class='entry-icon' />";
                echo "<a class='result-link' href='./bookmarks.php?folder=" . $row["id"] . "'>" . htmlspecialchars($row["title"]) . "</a>";
                echo "<a href=./edit-folder.php?folder=" . $row["id"] . "><span class='edit-button'><img src='./assets/edit.svg'/></span></a></div>";
            }
        } else {
            echo "0 folders";
        }
    ?>
    </div>

    <div class="result-set">
    <h2>Bookmarks:</h2>
    <a href="./add.php?parent=<?php echo $current_folder_id; ?>" class="add-button"><img src="./assets/add-circle.svg" /></a>

    <?php
        $sql = $pdo->prepare("SELECT nm_bookmarks.id,title, url FROM nm_bookmarks INNER JOIN nm_tree ON nm_bookmarks.id = nm_tree.id WHERE type = 'bookmark' AND parent_folder = :folder ORDER BY title");
        $sql->bindParam(":folder", $current_folder_id, PDO::PARAM_STR);
        $sql->execute();
        $sql->execute();
        $bookmarks_result = $sql->fetchAll();

        if (!empty($bookmarks_result)) {
            // output data of each row
            foreach($bookmarks_result as &$row) {
                echo "<div class='result'>";
                
                /*
                $fav = array(
                    'URL' => $row["url"],   // URL of the Page we like to get the Favicon from
                    'SAVE'=> true,   // Save Favicon copy local (true) or return only favicon url (false)
                    'DIR' => './favicon-storage',   // Local Dir the copy of the Favicon should be saved
                    'TRY' => false,   // Try to get the Favicon frome the page (true) or only use the APIs (false)
                    'DEV' => null,   // Give all Debug-Messages ('debug') or only make the work (null)
                );*/
                
                $favicon_url = get_favicon($row["url"]);
                
                echo "<img src='". $favicon_url ."' class='entry-icon'>";
                
                echo "<a class='result-link' href='" . $row["url"] . "'>" . htmlspecialchars($row["title"]) . "</a>";
                echo "<a href=./edit-bookmark.php?bookmark=" . $row["id"] . "><span class='edit-button'><img src='./assets/edit.svg'/></span></a></div>";
            }
        } else {
            echo "0 bookmarks";
        }
    ?>
    </div>
    
    <?php require_once "./footer.php"; ?>

  </body>
</html>
