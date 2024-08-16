<?php
 // Global database/session initialization.
 require_once "./config.php";
 
 if(!isset($_SESSION['loggedin'])){
    header("Location: ./login.php");
    exit;
 }
 
 $current_folder_id = trim($_GET["parent"]);
?>
<html>
  <head>
    <title>Home - Nilamark</title>    
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <?php include 'header.php'; ?>
    
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" placeholder="### - Name...">

    <label for="folder">Parent Folder:</label>
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
    
    <script>
        document.getElementById('folder').value=<?php echo $current_folder_id; ?>;
    </script>
    
    <button type="submit" id="submitForm">
     <img src="./assets/save.svg" /><span>Save</span>
    </button>
  </form>
  
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // collect value of input field
        $name = $_POST['name'];
        $folder = $_POST['folder'];
        
            if (empty($name) | empty($folder)) {
                echo "Missing parameters.";
            } else {
                $sql1 = $pdo->prepare("INSERT INTO nm_folders (title) VALUES (:name)");
                $sql1->bindParam(":name", $name, PDO::PARAM_STR);
                $sql1->execute();
                $new_id = $pdo->lastInsertId();
                
                $sql2 = $pdo->prepare("INSERT INTO nm_tree VALUES ('folder', :id, :parent_folder, 1)");
                $sql2->bindParam(":id", $new_id, PDO::PARAM_STR);
                $sql2->bindParam(":parent_folder", $folder, PDO::PARAM_STR);
                $sql2->execute();
                
                redirect("./bookmarks.php?folder=" . $folder);
            }
        }
    ?>

  <?php require_once "./footer.php"; ?>

  </body>
</html>
