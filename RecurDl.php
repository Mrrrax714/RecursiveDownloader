<?php
require_once "vendor/autoload.php";
$faker = Faker\Factory::create();


function enlargeURL($img){
//de-Naked-Topless-295x295.jpg
// $img = preg_replace('/\[x](\d+)[x](\d+)[_2x_]\.*/', '', $img);
// $img = preg_replace('/[x]\d[x]\d[_2x_]\.*/', '', $img);
// $img = preg_replace('/\/\[x]+\d+[x]+\d+\D+\d\.*/', '', $img);
$img = preg_replace('/\-\d+x\d+\./', '.', $img);
$img = preg_replace('/\_\d+./', '_1280.', $img);
return $img;
}


function rel2abs($rel, $base)
{
    /* return if already absolute URL */
    if (parse_url($rel, PHP_URL_SCHEME) != '') return $rel;

    /* queries and anchors */
    if ($rel[0]=='#' || $rel[0]=='?') return $base.$rel;

    /* parse base URL and convert to local variables:
       $scheme, $host, $path */
    extract(parse_url($base));

    /* remove non-directory element from path */
    $path = preg_replace('#/[^/]*$#', '', $path);

    /* destroy path if relative url points to root */
    if ($rel[0] == '/') $path = '';

    /* dirty absolute URL // with port number if exists */
    if (parse_url($base, PHP_URL_PORT) != ''){
        $abs = "$host:".parse_url($base, PHP_URL_PORT)."$path/$rel";
    }else{
        $abs = "$host$path/$rel";
    }
    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme.'://'.$abs;
}


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
    $path_parts = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

    // Get the extension
    $extension = $path_parts;

    // Output the extension
    return "$extension";
}

// URL to fetch
$url = "https://example.com";
function get($url)
{
    global $faker;
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set SSL related options
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify the peer's SSL certificate
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Check the existence of a common name and also verify that it matches the hostname provided
    curl_setopt($ch, CURLOPT_USERAGENT, $faker->userAgent());
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
$link=rel2abs($link, $url);
        // Construct absolute URL if necessary
        if (strpos($link, "http") !== 0) {
            $link = rtrim($url, "/") . "/" . ltrim(rtrim($link, "/"), "/");
        }
$link=enlargeURL($link);
        // Get the filename from the URL
        $filename = "dls/" . basename($link);
        $filename = "dls/" . md5($link);

        $link = str_replace(["*", ".asp"], ["/", ""], $link);
        if (!isset($_SESSION[$link])) {
            echo "\n$link\n";
            // Download the file
            file_put_contents($filename, get($link));

            $f =
                "dls/" .
                ext2($filename) .
                "/" .
                md5($link) .
                "." .
                ext2($filename);
            @mkdir(dirname($f), 0777, true);
            rename($filename, $f);
            $_SESSION[$link] = $link;
        }
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
$link=rel2abs($link, $url);
        // Construct absolute URL if necessary
        if (strpos($link, "http") !== 0) {
            $link = rtrim($url, "/") . "/" . ltrim(rtrim($link, "/"), "/");
        }
$link = str_replace(["*", ".asp"], ["/", ""], $link);
$link=enlargeURL($link);
        if (!isset($_SESSION[$link])) {
            // Get the filename from the URL
            $filename = "dls/" . md5($link) . "." . ext($link);
            echo "\n$link\n";
            // Download the file
            file_put_contents($filename, get($link));

            $f = "dls/" . md5($link) . "." . ext2($filename);
            if (
                in_array(ext2($filename), ["jpg", "jpeg", "webp", "gif", "png"])
            ) {
                $f =
                    "dls/" .
                    ext2($filename) .
                    "/" .
                    md5($link) .
                    "." .
                    ext2($filename);
                @mkdir(dirname($f), 0777, true);
                rename($filename, $f);
                // Recursively download links
            } else {
                unlink($filename);
            }
            $_SESSION[$link] = $link;
        }

        if (!isset($_SESSION[$link])) {
            downloadRecursive($link, $depth + 1);
            $_SESSION[$link] = $link;
        }
    }
}

// Start downloading from a given URL

downloadRecursive($argv[1]);
