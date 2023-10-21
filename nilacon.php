<?php

    function get_favicon($url = null) {
        // error_log("Looking for " . $url);
        
        // Get the domain of the URL.
        $domain = parse_url($url, PHP_URL_HOST);
        $protocol = parse_url($url, PHP_URL_SCHEME);
        
        // Check if we already have the favicon.
        $favicon = glob('favicon-storage/' . $domain . '*');
        
        // Return the cached favicon.
        if(!empty($favicon)) {
            return $favicon[0];
        }
        
        // Try to get the favicon ourselves.
        require_once "./favicon-downloader.php";
        $favicon = new FaviconDownloader($url);
        if($favicon->icoExists) {
            $filename = 'favicon-storage/' . $domain . '.' . $favicon->icoType;
            file_put_contents($filename, $favicon->icoData);
            return $filename;
        }
        
        // If we didn't find it, check the Google API.
        $favicon_url = 'https://t1.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=' . $protocol . '://' . $domain . '&size=256';
        
        // Perform the download.
        $ch = curl_init($favicon_url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Don't check SSL certificate to allow autosigned certificate
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects (302, 301)
        curl_setopt($ch, CURLOPT_MAXREDIRS, 20);         // Follow up to 20 redirects
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $content = curl_exec($ch);
        $info['curl_errno'] = curl_errno($ch);
        $info['curl_error'] = curl_error($ch);
        $info['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info['effective_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $info['redirect_count'] = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
        $info['content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        // If it's not generic, save it.
        if ( !(md5($content) == 'b8a0bf372c762e966cc99ede8682bc71') ) {
            $filename = 'favicon-storage/' . $domain . '.png';
            file_put_contents($filename, $content);
            return $filename;
        }
        
        // If we still don't have it, symlink to a default image.
        $filePath = 'favicon-storage/' . $domain . '.png';
        symlink('../assets/default.png', $filePath);
        return $filePath;
}
?>
