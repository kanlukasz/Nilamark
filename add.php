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
    <link rel="icon" href="favicon.svg">
  </head>
  <body>
    <?php include 'header.php'; ?>
    
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
    <label for="url">URL:</label>
    <input type="text" id="url" name="url" placeholder="URL...">

    <label for="title">Title:</label>
    <input type="text" id="title" name="title" placeholder="### - Title...">

    <label for="folder">Folder:</label>
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
        $url = $_POST['url'];
        $title = $_POST['title'];
        $folder = $_POST['folder'];
        
            if (empty($url) | empty($title) | empty($folder)) {
                echo "Missing parameters.";
            } else {
                $sql1 = $pdo->prepare("INSERT INTO nm_bookmarks (url, title, added, lastmodified) VALUES (:url, :title, :timestamp1, :timestamp2)");
                $sql1->bindParam(":url", $url, PDO::PARAM_STR);
                $sql1->bindParam(":title", $title, PDO::PARAM_STR);
                $sql1->bindParam(":timestamp1", time(), PDO::PARAM_STR);
                $sql1->bindParam(":timestamp2", time(), PDO::PARAM_STR);
                $sql1->execute();
                $new_id = $pdo->lastInsertId();
                
                $sql2 = $pdo->prepare("INSERT INTO nm_tree VALUES ('bookmark', :id, :parent_folder, 0)");
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
