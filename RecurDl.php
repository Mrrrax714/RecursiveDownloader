<?php

function ext2($f)
{
    $mime = mime_content_type($f);
    $mimeTypes = [
        "image/jpeg" => "jpg",
        "text/html" => "html",
        "image/png" => "png",
        "image/gif" => "gif",
        "image/bmp" => "bmp",
        "image/webp" => "webp",
        "application/pdf" => "pdf",
        // Add more MIME types and extensions as needed
    ];

    // Check if the MIME type exists in the mapping
    if (array_key_exists($mime, $mimeTypes)) {
        return $mimeTypes[$mime];
    } else {
        return "txt"; // Return null if the MIME type is not found
    }
}
/*

// Example usage:
$mime = 'image/jpeg';
$extension = getExtensionFromMimeType($mime);
echo "Extension for MIME type $mime is: $extension";

// Example URL
$url = "https://example.com/path/to/file/document.pdf";*/
function ext($url)
{
    $path_parts["extension"] = "txt";
    echo "$url\n";
    // Parse the URL
    $path_parts = pathinfo(parse_url($url, PHP_URL_PATH),PATHINFO_EXTENSION);

    // Get the extension
    $extension = $path_parts;

    // Output the extension
    return "$extension";
}

// URL to fetch
$url = "https://example.com";
function get($url)
{
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set SSL related options
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify the peer's SSL certificate
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Check the existence of a common name and also verify that it matches the hostname provided

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo "cURL error: " . curl_error($ch);
    }

    // Close cURL session
    curl_close($ch);

    // Output the response
    return $response;
}

function downloadRecursive($url, $depth = 0)
{
    // Limit recursion depth to avoid infinite loops
    if ($depth > 3) {
        return;
    }

    // Fetch the content of the URL
    $content = get($url);
    // Extract links from the content
    preg_match_all('/<img\s+src=["\']([^"\']+)["\']/i', $content, $matches);
    $links = $matches[1];

    // Download each linked URL recursively
    foreach ($links as $link) {
        // Construct absolute URL if necessary
        if (strpos($link, "http") !== 0) {
            $link = rtrim($url, "/") . "/" . ltrim($link, "/");
        }
        echo "\n$link\n";
        // Get the filename from the URL
        $filename = "dls/" . basename($link);
        $filename = "dls/" . md5($link);

        $link = str_replace(["*", ".asp"], ["/", ""], $link);
        // Download the file
        file_put_contents($filename, get($link));

        $f =
            "dls/" . ext2($filename) . "/" . md5($link) . "." . ext2($filename);
        @mkdir(dirname($f), 0777, true);
        rename($filename, $f);
        // Recursively download links
        if (!isset($_SESSION[$link])) {
            downloadRecursive($link, $depth + 1);
            $_SESSION[$link] = $link;
        }
    }

    // Extract links from the content
    preg_match_all('/<a\s+href=["\']([^"\']+)["\']/i', $content, $matches);
    $links = $matches[1];

    // Download each linked URL recursively
    foreach ($links as $link) {
        // Construct absolute URL if necessary
        if (strpos($link, "http") !== 0) {
            $link = rtrim($url, "/") . "/" . ltrim($link, "/");
        }

        // Get the filename from the URL
        $filename = "dls/" . md5($link) . "." . ext($link);
echo "\n$link\n";
        // Download the file
        file_put_contents($filename, get($link));

        $f = "dls/" . md5($link) . "." . ext2($filename);

        $f =
            "dls/" . ext2($filename) . "/" . md5($link) . "." . ext2($filename);
        @mkdir(dirname($f), 0777, true);
        rename($filename, $f);
        // Recursively download links

        if (!isset($_SESSION[$link])) {
            downloadRecursive($link, $depth + 1);
            $_SESSION[$link] = $link;
        }
    }
}

// Start downloading from a given URL
/*
//$startUrl ="https://www.hotzxgirl.com/ona-artist-nude-Free-Sex-Photos-and-Porn-Images-at-SEX-FUN/sex1.fun*wp-content*uploads*sites*20*2022*03*56-11.jpg.asp#nothing";
$startUrl ="https://www.twpornstars.com/onaartist?sort=date&page=7";
//$startUrl ="https://www.freeones.com/";
//$startUrl ="https://www.porndig.com/leaked/feed/2216";*/
//$startUrl ="https://zaramedya.net/ona+artist+porn"; //https://sdep.fr/ona+artist";
$startUrl="https://www.twpornstars.com/onaartist?sort=date&page=7";
$startUrl="https://xxxpornpics.net/who-doesnt-love-tits-and-yoga-pants-36f-JCZXpaDcnJ";//https://pholder.com/sexy-selfie?page=18";//https://sexynudes.tv/post/which-view-do-you-like-better-front-or-back-f-6ft-25082599";//https://www.hotzxgirl.com/ona-artist-nude-Free-Sex-Photos-and-Porn-Images-at-SEX-FUN/sex1.fun*wp-content*uploads*sites*20*2022*03*56-11.jpg.asp";
downloadRecursive($startUrl);
