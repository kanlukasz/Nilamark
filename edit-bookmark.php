<?php
 // Global database/session initialization.
 require_once "./config.php";
 
 if(!isset($_SESSION['loggedin'])){
  header("Location: ./login.php");
  exit;
 }
 
 $current_bookmark_id = trim($_GET["bookmark"]);
?>
<html>
  <head>
    <title>Home - Nilamark</title>    
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="favicon.svg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <?php include 'header.php'; ?>
    
    <?php
        $sql = $pdo->prepare("SELECT title,url,parent_folder FROM nm_bookmarks INNER JOIN nm_tree ON nm_bookmarks.id = nm_tree.id WHERE type = 'bookmark' AND nm_bookmarks.id = :bookmark ORDER BY title");
        $sql->bindParam(":bookmark", $current_bookmark_id, PDO::PARAM_STR);
        $sql->execute();
        $current_bookmark = $sql->fetch();
    ?>
    
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    
    <label for="id">Bookmark ID:</label>
    <input type="text" id="id" name="id" placeholder="ID..." value="<?php echo $current_bookmark_id; ?>" readonly>
    
    <label for="name">Bookmark Name:</label>
    <input type="text" id="name" name="name" placeholder="### - Name..." value="<?php echo htmlspecialchars($current_bookmark['title']); ?>">
    
    <label for="url">Bookmark URL:</label>
    <input type="text" id="url" name="url" placeholder="### - Name..." value="<?php echo htmlspecialchars($current_bookmark['url']); ?>">

    <label for="parent">Folder:</label>
    <select id="parent" name="parent">
        <?php
            $sql = $pdo->prepare("SELECT id,title FROM nm_folders ORDER BY id DESC");
            $sql->execute();
            $folder_list = $sql->fetchAll();
            
            foreach($folder_list as &$folder) {
                echo "<option value='" . $folder['id'] . "'> (#" . $folder['id'] . ") " . $folder['title'] . "</option>";
            }
        ?>
    </select>
    
    <script>
        document.getElementById('parent').value=<?php echo $current_bookmark['parent_folder']; ?>;
    </script>
    
    <button type="submit" id="submitForm">
     <img src="./assets/save.svg" /><span>Save</span>
    </button>
  </form>
  
  <hr/><br/><br/><hr style="width: 50%;" />
  
  <a href="./delete.php?bookmark=<?php echo $current_bookmark_id; ?>"><button type="submit" id="delete">
      <img src="./assets/delete.svg" /><span>Delete</span>
  </button></a>
  
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // collect value of input field
        $name = $_POST['name'];
        $url = $_POST['url'];
        $parent = $_POST['parent'];
        $id = $_POST['id'];
        
            if (empty($name) | empty($folder)) {
                echo "Missing parameters.";
            } else {
                $sql1 = $pdo->prepare("UPDATE nm_bookmarks SET title = :name, url = :url WHERE id = :id");
                $sql1->bindParam(":name", $name, PDO::PARAM_STR);
                $sql1->bindParam(":url", $url, PDO::PARAM_STR);
                $sql1->bindParam(":id", $id, PDO::PARAM_STR);
                $sql1->execute();
                $new_id = $pdo->lastInsertId();
                
                $sql2 = $pdo->prepare("UPDATE nm_tree SET parent_folder = :parent_folder WHERE id = :id AND TYPE = 'bookmark'");
                $sql2->bindParam(":parent_folder", $parent, PDO::PARAM_STR);
                $sql2->bindParam(":id", $id, PDO::PARAM_STR);
                $sql2->execute();
                
                redirect("./bookmarks.php?folder=" . $parent);
            }
        }
    ?>

  <?php require_once "./footer.php"; ?>

  </body>
</html>
