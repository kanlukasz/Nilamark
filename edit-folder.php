<?php
 // Global database/session initialization.
 require_once "./config.php";
 
 if(!isset($_SESSION['loggedin'])){
  header("Location: ./login.php");
  exit;
 }
 
 $current_folder_id = trim($_GET["folder"]);
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
        $sql = $pdo->prepare("SELECT title,parent_folder FROM nm_folders INNER JOIN nm_tree ON nm_folders.id = nm_tree.id WHERE type = 'folder' AND nm_folders.id = :folder ORDER BY title");
        $sql->bindParam(":folder", $current_folder_id, PDO::PARAM_STR);
        $sql->execute();
        $current_folder = $sql->fetch();
    ?>
    
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
        <label for="folder">Edit Folder:</label>
        <select id="folder" name="folder">
            <?php
                $sql = $pdo->prepare("SELECT id,title FROM nm_folders ORDER BY id DESC");
                $sql->execute();
                $folder_list = $sql->fetchAll();
            
                foreach($folder_list as &$folder) {
                    echo "<option value='" . $folder['id'] . "'> (#" . $folder['id'] . ") " . $folder['title'] . "</option>";
                }
            ?>
        </select>
    
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" placeholder="### - Name..." value="<?php echo htmlspecialchars($current_folder['title']); ?>">

    <label for="parent">Parent Folder:</label>
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
        document.getElementById('folder').value=<?php echo $current_folder_id; ?>;
        document.getElementById('parent').value=<?php echo $current_folder['parent_folder']; ?>;
    </script>
    
    <button type="submit" id="submitForm">
     <img src="./assets/save.svg" /><span>Save</span>
    </button>
  </form>
  
  <hr/><br/><br/><hr style="width: 50%;" />
  
  <a href="./delete.php?folder=<?php echo $current_folder_id; ?>"><button type="submit" id="delete">
      <img src="./assets/delete.svg" /><span>Delete</span>
  </button></a>
  
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // collect value of input field
        $name = $_POST['name'];
        $parent = $_POST['parent'];
        $folder = $_POST['folder'];
        
            if (empty($name) | empty($folder)) {
                echo "Missing parameters.";
            } else {
                $sql1 = $pdo->prepare("UPDATE nm_folders SET title = :name WHERE id = :id");
                $sql1->bindParam(":name", $name, PDO::PARAM_STR);
                $sql1->bindParam(":id", $folder, PDO::PARAM_STR);
                $sql1->execute();
                $new_id = $pdo->lastInsertId();
                
                $sql2 = $pdo->prepare("UPDATE nm_tree SET parent_folder = :parent_folder WHERE id = :id AND TYPE = 'folder'");
                $sql2->bindParam(":parent_folder", $parent, PDO::PARAM_STR);
                $sql2->bindParam(":id", $folder, PDO::PARAM_STR);
                $sql2->execute();
                
                redirect("./bookmarks.php?folder=" . $parent);
            }
        }
    ?>

  <?php require_once "./footer.php"; ?>

  </body>
</html>
