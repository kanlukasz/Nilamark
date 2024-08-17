<?php
 // Global database/session initialization.
 require_once "./config.php";
 require_once "./nilacon.php";

class Search{
    public function product($pdo) {
        if($_POST['searchQuery'] == null) {
            return;
        }

	$limit = '100';
	$page = 1;
	if($_POST['page'] > 1) {
	  $start = (($_POST['page'] - 1) * $limit);
	  $page = $_POST['page'];
	} else {
	  $start = 0;
	}

        $sql_query_getall = "SELECT nm_bookmarks.id, title, url, parent_folder FROM nm_bookmarks INNER JOIN nm_tree ON nm_bookmarks.id = nm_tree.id WHERE type = 'bookmark' ORDER BY nm_bookmarks.id ASC";
        $sql_statement_getall = $pdo->prepare($sql_query_getall);
        $sql_statement_getall->execute();

        $result_all = $sql_statement_getall->fetchAll();

        foreach ($result_all as $key => $result) {
            if (preg_match("/^(0|[1-9][0-9]*)(0|[1-9][0-9]*)(0|[1-9][0-9]*) - .*$/", $result['title'])) {
                $result_all[$key]['title'] = substr($result['title'], 6);
            }
        }

        function sort_compare($a, $b) {
          $value_a = similar_text(strtolower($_POST['searchQuery']), strtolower($a['title']), $percent_a);
          $value_b = similar_text(strtolower($_POST['searchQuery']), strtolower($b['title']), $percent_b);
          // Sort by value first, then by percentage.
          // Future idea: use Levenshtein distance as the second or third step.
          if($value_a < $value_b)return 1;
          else if($value_a > $value_b)return -1;
          else if($percent_a < $percent_b)return 1;
          else if($percent_a > $percent_b)return -1;
          else return 0;
        }
        usort($result_all, "sort_compare");

        echo '<div class="result-set"><h2>Search results:</h2>';

        for ($looprow = 0; $looprow <= 9; $looprow ++) {
         $row = $result_all[$looprow];
         $favicon_url = get_favicon($row["url"]);
            echo "<div class='result'>
                    <img src='". $favicon_url ."' class='entry-icon'>
                    <a class='result-link' href='" . $row["url"] . "'>" . htmlspecialchars($row["title"]) . "</a>
                    <a href='./bookmarks.php?folder=" . $row["parent_folder"] . "'><span class='folder-button'><img src='./assets/folder.svg'/></span></a>
                    <a href='./edit-bookmark.php?bookmark=" . $row["id"] . "'><span class='edit-button'><img src='./assets/edit.svg'/></span></a>
                  </div>";
          }

        /*

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

	echo $resultHTML; */
}
}

$search = new Search($pdo);

if(!empty($_POST['search']) && $_POST['search'] == 'search') {
	$search->product($pdo);
}
?>
