<?php
require_once 'Zend/Loader.php';
Zend_Loader::loadClass('Zend_Gdata_Photos');
Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
Zend_Loader::loadClass('Zend_Gdata_AuthSub');

session_start();

function getAuthSubHttpClient()
{
    if (!isset($_SESSION['sessionToken']) && !isset($_GET['token']) ){
        echo '<a href="' . getAuthSubUrl() . '">Login!</a>';
        exit;
    } else if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
        $_SESSION['sessionToken'] =
            Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token']);
    }
    $client = Zend_Gdata_AuthSub::getHttpClient($_SESSION['sessionToken']);
    return $client;
}

// update the second argument to be CompanyName-ProductName-Version
$gp = new Zend_Gdata_Photos(getAuthSubHttpClient(), "FBAlbum-Movers-1.0");

// In version 1.5+, you can enable a debug logging mode to see the
// underlying HTTP requests being made, as long as you're not using
// a proxy server
// $gp->enableRequestDebugLogging('/tmp/gp_requests.log');



$entry = new Zend_Gdata_Photos_AlbumEntry();
$entry->setTitle($gp->newTitle("Helooooo"));
$entry->setSummary($gp->newSummary("This is an album."));

$createdEntry = $gp->insertAlbumEntry($entry);




$username = "mehul.kaklotar";
$filename = "laanz.png";
$photoName = "My Test Photo";
$photoCaption = "The first photo I uploaded to Picasa Web Albums via PHP.";
$photoTags = "beach, sunshine";

// We use the albumId of 'default' to indicate that we'd like to upload
// this photo into the 'drop box'.  This drop box album is automatically 
// created if it does not already exist.
$albumId = "default";

$fd = $gp->newMediaFileSource($filename);
$fd->setContentType("image/jpeg");

// Create a PhotoEntry
$photoEntry = $gp->newPhotoEntry();

$photoEntry->setMediaSource($fd);
$photoEntry->setTitle($gp->newTitle($photoName));
$photoEntry->setSummary($gp->newSummary($photoCaption));

// add some tags
$keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
$keywords->setText($photoTags);
$photoEntry->mediaGroup = new Zend_Gdata_Media_Extension_MediaGroup();
$photoEntry->mediaGroup->keywords = $keywords;

// We use the AlbumQuery class to generate the URL for the album
$albumQuery = $gp->newAlbumQuery();

$albumQuery->setUser($username);
$albumQuery->setAlbumId($albumId);

// We insert the photo, and the server returns the entry representing
// that photo after it is uploaded
$insertedEntry = $gp->insertPhotoEntry($photoEntry, $albumQuery->getQueryUrl()); 




$serviceName = Zend_Gdata_Photos::AUTH_SERVICE_NAME;
$client = Zend_Gdata_ClientLogin::getHttpClient("mehul.kaklotar", "gj5gk2579", $serviceName);

// update the second argument to be CompanyName-ProductName-Version
$gp = new Zend_Gdata_Photos($client, "FBAlbum-Movers-1.0");

try {
    $userFeed = $gp->getUserFeed("default");
    foreach ($userFeed as $userEntry) {
        echo $userEntry->title->text . "<br />\n";
    }
} catch (Zend_Gdata_App_HttpException $e) {
    echo "Error: " . $e->getMessage() . "<br />\n";
    if ($e->getResponse() != null) {
        echo "Body: <br />\n" . $e->getResponse()->getBody() . 
             "<br />\n"; 
    }
    // In new versions of Zend Framework, you also have the option
    // to print out the request that was made.  As the request
    // includes Auth credentials, it's not advised to print out
    // this data unless doing debugging
    // echo "Request: <br />\n" . $e->getRequest() . "<br />\n";
} catch (Zend_Gdata_App_Exception $e) {
    echo "Error: " . $e->getMessage() . "<br />\n"; 
}
?>