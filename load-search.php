<?php
 // Global database/session initialization.
 require_once "./config.php";
 require_once "./nilacon.php";

class Search{
    public function product($pdo) {
	$limit = '100';
	$page = 1;
	if($_POST['page'] > 1) {
	  $start = (($_POST['page'] - 1) * $limit);
	  $page = $_POST['page'];
	} else {
	  $start = 0;
	}

	$sqlQuery = "SELECT * FROM nm_bookmarks INNER JOIN nm_tree ON nm_bookmarks.id = nm_tree.id";
	if($_POST['searchQuery'] != ''){
	  $sqlQuery .= ' WHERE LOWER(title) LIKE LOWER("%'.str_replace(' ', '%', $_POST['searchQuery']).'%") ';
	}
	$sqlQuery .= ' ORDER BY nm_bookmarks.id ASC';

	$filter_query = $sqlQuery . ' LIMIT '.$start.', '.$limit.'';

	//$statement = $this->conn->prepare($sqlQuery);
        $statement = $pdo->prepare($sqlQuery);
	$statement->execute();

	//$result = $statement->get_result();
        $result = $statement->fetchAll();
	$totalSearchResults = count($result);

	//$statement = $this->conn->prepare($filter_query);
        $statement = $pdo->prepare($filter_query);
	$statement->execute();

	//$result = $statement->get_result();
        $result = $statement->fetchAll();
	$total_filter_data = count($result);

	$resultHTML = '
		<div class="result-set">
                 <h2>Search results: '.$totalSearchResults.'</h2>';

	if($totalSearchResults > 0) {	  
          foreach ($result as $row) {
            $favicon_url = get_favicon($row["url"]);
            $resultHTML .= "
                <div class='result'>
                    <img src='". $favicon_url ."' class='entry-icon'>
                    <a class='result-link' href='" . $row["url"] . "'>" . htmlspecialchars($row["title"]) . "</a>
                    <a href='./bookmarks.php?folder=" . $row["parent_folder"] . "'><span class='folder-button'><img src='./assets/folder.svg'/></span></a>
                    <a href='./edit-bookmark.php?bookmark=" . $row["id"] . "'><span class='edit-button'><img src='./assets/edit.svg'/></span></a>
                </div>";
          }
	} else {
	  $resultHTML .= '
	  <tr>
		<td colspan="2" align="center">No Record Found</td>
	  </tr>';
	}

        /*
	$resultHTML .= '
	</div>
	<br />
	<div align="center">
	  <ul class="pagination">';

	$totalLinks = ceil($totalSearchResults/$limit);
	$previousLink = '';
	$nextLink = '';
	$pageLink = '';	

	if($totalLinks > 4){
	  if($page < 5){
		for($count = 1; $count <= 5; $count++){
		  $pageData[] = $count;
		}
		$pageData[] = '...';
		$pageData[] = $totalLinks;
	  } else {
		$endLimit = $totalLinks - 5;
		if($page > $endLimit){
		  $pageData[] = 1;
		  $pageData[] = '...';
		  for($count = $endLimit; $count <= $totalLinks; $count++)
		  {
			$pageData[] = $count;
		  }
		} else {
		  $pageData[] = 1;
		  $pageData[] = '...';
		  for($count = $page - 1; $count <= $page + 1; $count++)
		  {
			$pageData[] = $count;
		  }
		  $pageData[] = '...';
		  $pageData[] = $totalLinks;
		}
	  }
	} else if($totalLinks > 0){
	  for($count = 1; $count <= $totalLinks; $count++) {
		$pageData[] = $count;
	  }
	} else {
          $pageData[] = 0;
        }

	for($count = 0; $count < count($pageData); $count++){
	  if($page == $pageData[$count]){
		$pageLink .= '
		<li class="page-item active">
		  <a class="page-link" href="#">'.$pageData[$count].' <span class="sr-only">(current)</span></a>
		</li>';

		$previousData = $pageData[$count] - 1;
		if($previousData > 0){
		  $previousLink = '<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page_number="'.$previousData.'">Previous</a></li>';
		} else {
		  $previousLink = '
		  <li class="page-item disabled">
			<a class="page-link" href="#">Previous</a>
		  </li>';
		}
		$nextData = $pageData[$count] + 1;
		if($nextData > $totalLinks){
		  $nextLink = '
		  <li class="page-item disabled">
			<a class="page-link" href="#">Next</a>
		  </li>';
		} else {
		  $nextLink = '<li class="page-item"><a class="page-link" href="javascript:void(0)" data-page_number="'.$nextData.'">Next</a></li>';
		}
	  } else {
		if($pageData[$count] == '...'){
		  $pageLink .= '
		  <li class="page-item disabled">
			  <a class="page-link" href="#">...</a>
		  </li>';
		} else {
		  $pageLink .= '
		  <li class="page-item"><a class="page-link" href="javascript:void(0)" data-page_number="'.$pageData[$count].'">'.$pageData[$count].'</a></li>';
		}
	  }
	}

	$resultHTML .= $previousLink . $pageLink . $nextLink;
	$resultHTML .= '</ul></div>';
        */
	echo $resultHTML;
}
}

$search = new Search($pdo);

if(!empty($_POST['search']) && $_POST['search'] == 'search') {
	$search->product($pdo);
}
?>
