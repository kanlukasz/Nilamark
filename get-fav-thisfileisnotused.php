<?php
/*

PHP Grab Favicon
================

> This `PHP Favicon Grabber` use a given url, save a copy (if wished) and return the image path.

How it Works
------------

1. Check if the favicon already exists local or no save is wished, if so return path & filename
2. Else load URL and try to match the favicon location with regex
3. If we have a match the favicon link will be made absolute
4. If we have no favicon we try to get one in domain root
5. If there is still no favicon we randomly try google, faviconkit & favicongrabber API
6. If favicon should be saved try to load the favicon URL
7. If wished save the Favicon for the next time and return the path & filename

How to Use
----------

```PHP
$url = 'example.com';

$grap_favicon = array(
'URL' => $url,   // URL of the Page we like to get the Favicon from
'SAVE'=> true,   // Save Favicon copy local (true) or return only favicon url (false)
'DIR' => './',   // Local Dir the copy of the Favicon should be saved
'TRY' => true,   // Try to get the Favicon frome the page (true) or only use the APIs (false)
'DEV' => null,   // Give all Debug-Messages ('debug') or only make the work (null)
);

echo '<img src="'.grap_favicon($grap_favicon).'">';
```

Todo
----
Optional split the download dir into several sub-dirs (MD5 segment of filename e.g. /af/cd/example.com.png) if there are a lot of favicons.

Infos about Favicon
-------------------
https://github.com/audreyr/favicon-cheat-sheet

###### Copyright 2019-2020 Igor Gaffling

*/ 

/*
$testURLs = array(
  'http://aws.amazon.com',
  'http://www.apple.com',
  'http://www.dribbble.com',
  'http://www.github.com',
  'http://www.intercom.com',
  'http://www.indiehackers.com',
  'http://www.medium.com',
  'http://www.mailchimp.com',
  'http://www.netflix.com',
  'http://www.producthunt.com',
  'http://www.reddit.com',
  'http://www.slack.com',
  'http://www.soundcloud.com',
  'http://www.stackoverflow.com',
  'http://www.techcrunch.com',
  'http://www.trello.com',
  'http://www.vimeo.com',
  'https://www.whatsapp.com/',
  'https://www.gaffling.com/',
);

foreach ($testURLs as $url) {
  $grap_favicon = array(
    'URL' => $url,   // URL of the Page we like to get the Favicon from
    'SAVE'=> true,   // Save Favicon copy local (true) or return only favicon url (false)
    'DIR' => './',   // Local Dir the copy of the Favicon should be saved
    'TRY' => true,   // Try to get the Favicon frome the page (true) or only use the APIs (false)
    'DEV' => null,   // Give all Debug-Messages ('debug') or only make the work (null)
  );
  $favicons[] = grap_favicon($grap_favicon);
}
foreach ($favicons as $favicon) {
  echo '<img title="'.$favicon.'" style="width:32px;padding-right:32px;" src="'.$favicon.'">';
}
echo '<br><br><tt>Runtime: '.round((microtime(true)-$_SERVER["REQUEST_TIME_FLOAT"]),2).' Sec.';

*/

function get_favicon( $options=array() ) {
  
  // avoid script runtime timeout
  $max_execution_time = ini_get("max_execution_time");
  set_time_limit(0); // 0 = no timelimit

  // Ini Vars
  $url       = (isset($options['URL']))?$options['URL']:'gaffling.com';
  $save      = (isset($options['SAVE']))?$options['SAVE']:true;
  $directory = (isset($options['DIR']))?$options['DIR']:'./';
  $trySelf   = (isset($options['TRY']))?$options['TRY']:true;
  $DEBUG     = (isset($options['DEV']))?$options['DEV']:null;

  // URL to lower case
	$url = strtolower($url);

	// Get the Domain from the URL
  $domain = parse_url($url, PHP_URL_HOST);

  // Check Domain
  $domainParts = explode('.', $domain);
  if(count($domainParts) == 3 and $domainParts[0]!='www') {
    // With Subdomain (if not www)
    $domain = $domainParts[0].'.'.
              $domainParts[count($domainParts)-2].'.'.$domainParts[count($domainParts)-1];
  } else if (count($domainParts) >= 2) {
    // Without Subdomain
		$domain = $domainParts[count($domainParts)-2].'.'.$domainParts[count($domainParts)-1];
	} else {
	  // Without http(s)
	  $domain = $url;
	}

	// FOR DEBUG ONLY
	if($DEBUG=='debug')print('<b style="color:red;">Domain</b> #'.@$domain.'#<br>');

	// Make Path & Filename
	$filePath = preg_replace('#\/\/#', '/', $directory.'/'.$domain.'.png');
    if (!file_exists($filePath)) {
        $filePath = preg_replace('#\/\/#', '/', $directory.'/'.$domain.'.svg');
    }
    if (!file_exists($filePath)) {
        $filePath = preg_replace('#\/\/#', '/', $directory.'/'.$domain.'.jpg');
    }
    if (!file_exists($filePath)) {
        $filePath = preg_replace('#\/\/#', '/', $directory.'/'.$domain.'.ico');
    }
    if (!file_exists($filePath)) {
        $filePath = preg_replace('#\/\/#', '/', $directory.'/'.$domain.'.webp');
    } if (!file_exists($filePath)) {
        $filePath = preg_replace('#\/\/#', '/', $directory.'/'.$domain.'.png');
    }
	// change save path & filename of icons ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ 

	// If Favicon not already exists local
  if ( !file_exists($filePath) or @filesize($filePath)==0 ) {

    // If $trySelf == TRUE ONLY USE APIs
    if ( isset($trySelf) and $trySelf == TRUE ) {

      // Load Page
      $html = load($url, $DEBUG);

      // Find Favicon with RegEx
      $regExPattern = '/((<link[^>]+rel=.(icon|shortcut icon|alternate icon)[^>]+>))/i';
      if ( @preg_match($regExPattern, $html, $matchTag) ) {
        $regExPattern = '/href=(\'|\")(.*?)\1/i';
        if ( isset($matchTag[1]) and @preg_match($regExPattern, $matchTag[1], $matchUrl)) {
          if ( isset($matchUrl[2]) ) {
            
            // Build Favicon Link
            $favicon = rel2abs(trim($matchUrl[2]), 'http://'.$domain.'/');
            
          	// FOR DEBUG ONLY
          	if($DEBUG=='debug')print('<b style="color:red;">Match</b> #'.@$favicon.'#<br>');

          }
        }
      }
      
      // If there is no Match: Try if there is a Favicon in the Root of the Domain
    	if ( empty($favicon) ) { 
      	$favicon = 'http://'.$domain.'/favicon.ico';

      	// Try to Load Favicon
        if ( !@getimagesize($favicon) ) {
          unset($favicon);
        }
    	}

    } // END If $trySelf == TRUE ONLY USE APIs
        
    // If nothing works: Get the Favicon from API
    if ( !isset($favicon) or empty($favicon) ) {
      // error_log('Favicon for ' . $url . 'not found, checking APIs...');

      /* SSL cert is expired
      // Faviconkit API
      if ($random == 1 or empty($favicon)) {
        $favicon = 'https://api.faviconkit.com/'.$domain.'/16';
      }
      */

      // Favicongrabber API
      if (!isset($favicon) or empty($favicon)) {
        $echo = json_decode(load('https://favicongrabber.com/api/grab/'.$domain,FALSE),TRUE);
        
        // Get Favicon URL from Array out of json data (@ if something went wrong)
        $favicon = @$echo['icons']['0']['src'];
      }
      
      if (isset($favicon) && !empty($favicon)) {
          error_log("Favicongrabber API found! Checking...");
      $content = load($favicon, $DEBUG);
      if ( empty($content) || stripos($content,'<html>' !== false) || stripos($content,'DOCTYPE' !== false) || stripos($content,'not found' !== false) ) {
          error_log("Favicongrabber was empty.");
          unset($content);
          unset($favicon);
      }}
      
      // Google API
      if (!isset($favicon) or empty($favicon)) {
        $favicon = 'https://www.google.com/s2/favicons?domain='.$domain.'&size=256';
        
        if(isset($favicon) && !empty($favicon)) {
          error_log("Google API found! Checking...");
        $content = load($favicon, $DEBUG);
        if (md5($content) == 'b8a0bf372c762e966cc99ede8682bc71') {
            error_log("It was default.");
            unset($favicon);
        }
      }
      }

    }

    // Write Favicon local
    if (!(!isset($favicon) or empty($favicon))) {
        

    // If Favicon should be saved
    if ( isset($save) and $save == TRUE ) {
      
      // Load Favicon
      
      // error_log($url . md5($content));

      // If Google API doesn't know and deliver a default Favicon (World)
      if ( empty($content) || stripos($content,'<html>') !== false || stripos($content,'DOCTYPE') !== false || stripos($content,'not found') !== false ) {
        symlink('../assets/default.png', $filePath);
      } else {
        // Write
        if ( stripos($content,'svg') !== false ) {
            $fileext = '.svg';
        } else if (stripos($content,'png')) {
            $fileext = '.png';
        } else if (stripos($content,'jpg')) {
            $fileext = '.jpg';
        } else if (stripos($content,'ico')) {
            $fileext = '.ico';
        } else if (stripos($content,'webp')) {
            $fileext = '.ico';
        } else {
            $fileext = '.png';
        }
        $filePath = preg_replace('#\/\/#', '/', $directory.'/'.$domain.$fileext);
        $fh = @fopen($filePath, 'wb');
        fwrite($fh, $content);
        fclose($fh);
    }



      // FOR DEBUG ONLY
    	if($DEBUG=='debug')print('<b style="color:red;">Write-File</b> #'.@$filePath.'#<br>');

    } else {
      
      // Don't save Favicon local, only return Favicon URL
      $filePath = $favicon;
    }}

	} // END If Favicon not already exists local

	// FOR DEBUG ONLY
	if ($DEBUG=='debug') {

    // Load the Favicon from local file
	  if ( !function_exists('file_get_contents') ) {
      $fh = @fopen($filePath, 'r');
      while (!feof($fh)) {
        $content .= fread($fh, 128); // Because filesize() will not work on URLS?
      }
      fclose($fh);
    } else {
      $content = file_get_contents($filePath);
    }
	  print('<b style="color:red;">Image</b> <img style="width:32px;" 
	         src="data:image/png;base64,'.base64_encode($content).'"><hr size="1">');
  }
	
  // reset script runtime timeout
  set_time_limit($max_execution_time); // set it back to the old value

  // Return Favicon Url
  return $filePath;

} // END MAIN Function

/* HELPER load use curl or file_get_contents (both with user_agent) and fopen/fread as fallback */
function load($url, $DEBUG) {
  if ( function_exists('curl_version') ) {
    // error_log('Sending cURL request to ' . $url);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'FaviconBot/1.0 (+http://'.$_SERVER['SERVER_NAME'].'/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $content = curl_exec($ch);
    if ( $DEBUG=='debug' ) { // FOR DEBUG ONLY
      $http_code = curl_getinfo($ch);
      print('<b style="color:red;">cURL</b> #'.$http_code['http_code'].'#<br>');
    }
    curl_close($ch);
    unset($ch);
  } else {
  	$context = array ( 'http' => array (
        'user_agent' => 'FaviconBot/1.0 (+http://'.$_SERVER['SERVER_NAME'].'/)'),
    );
    $context = stream_context_create($context);
	  if ( !function_exists('file_get_contents') ) {
      $fh = fopen($url, 'r', FALSE, $context);
      $content = '';
      while (!feof($fh)) {
        $content .= fread($fh, 128); // Because filesize() will not work on URLS?
      }
      fclose($fh);
    } else {
      $content = file_get_contents($url, NULL, $context);
    }
  }
  return $content;
}

/* HELPER: Change URL from relative to absolute */
function rel2abs( $rel, $base ) {
	extract( parse_url( $base ) );
	if ( strpos( $rel,"//" ) === 0 ) return $scheme . ':' . $rel;
	if ( parse_url( $rel, PHP_URL_SCHEME ) != '' ) return $rel;
	if ( $rel[0] == '#' or $rel[0] == '?' ) return $base . $rel;
	$path = preg_replace( '#/[^/]*$#', '', $path);
	if ( $rel[0] ==  '/' ) $path = '';
	$abs = $host . $path . "/" . $rel;
	$abs = preg_replace( "/(\/\.?\/)/", "/", $abs);
	$abs = preg_replace( "/\/(?!\.\.)[^\/]+\/\.\.\//", "/", $abs);
	return $scheme . '://' . $abs;
}
 
