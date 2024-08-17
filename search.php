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
    <link rel="icon" href="favicon.svg">
    <link rel="manifest" href="pwa-manifest.json">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <?php include 'header.php'; ?>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
     function delay(fn, ms) {
      let timer = 0
      return function(...args) {
       clearTimeout(timer)
       timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
     }

     $(document).ready(function(){
      $('#search').keyup(delay(function(){
       var searchQuery = $('#search').val();
       searchData(1, searchQuery);
       console.log("searched");
      }, 250));
     });

     function searchData(page, searchQuery = '') {
      $.ajax({
       url:"load-search.php",
       method:"POST",
       data:{search:'search', page:page, searchQuery:searchQuery}, success:function(data) {
        $('#searchResult').html(data);
       }
      });
     }

     $('#searchSection').on('click', '.page-link', function(){
      var page = $(this).data('page_number');
      var searchQuery = $('#search').val();
      searchData(page, searchQuery);
     });
    </script>

     <div class="card-body" id="searchSection">
       <div class="form-group">
        <input type="text" name="search" id="search" class="form-control" placeholder="Type your search keyword here" />
       </div>
      <div class="table-responsive" id="searchResult"></div>
    </div>



    <?php require_once "./footer.php"; ?>

  </body>
</html>
