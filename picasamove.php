<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title></title>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/styles.css" rel="stylesheet">
		<link rel="stylesheet" href="css/foundation.css" />
		<link rel="stylesheet" href="css/foundation-icons.css" />
		<script src="js/modernizr.js"></script>
		<!-- Load External js Lib-->
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
	</head>
	<body>
		<div class="row">
			<div class="large-12 columns">

				<!-- Navigation -->

				<div class="row">
					<div class="large-12 columns">

						<nav class="top-bar" data-topbar>
							<ul class="title-area">
								<!-- Title Area -->
								<li class="name">
									<h1><a href="#"> Facebook Album </a></h1>
								</li>
							</ul>
						</nav>
						<!-- End Top Bar -->
					</div>
				</div>

				<!-- End Navigation -->

				<div class="row" style="margin-top: 50px;">
					<center>

						<?php
						
						/**
						 * @see Zend_Loader
						 */
						require_once 'Zend/Loader.php';

						/**
						 * @see Zend_Gdata
						 */
						Zend_Loader::loadClass('Zend_Gdata');

						/**
						 * @see Zend_Gdata_AuthSub
						 */
						Zend_Loader::loadClass('Zend_Gdata_AuthSub');

						/**
						 * @see Zend_Gdata_ClientLogin
						 */
						Zend_Loader::loadClass('Zend_Gdata_ClientLogin');

						/**
						 * @see Zend_Gdata_HttpClient
						 */
						Zend_Loader::loadClass('Zend_Gdata_HttpClient');

						/**
						 * @see Zend_Gdata_Photos
						 */
						Zend_Loader::loadClass('Zend_Gdata_Photos');

						/**
						 * @var string Location of AuthSub key file.  include_path is used to find this
						 */
						$_authSubKeyFile = null;
						// Example value for secure use: 'mykey.pem'

						/**
						 * @var string Passphrase for AuthSub key file.
						 */
						$_authSubKeyFilePassphrase = null;

						/**
						 *To remove non empty Directory
						 * @param type $dir : Directory Name
						 */

						function rrmdir($dir) {
							if (is_dir($dir)) {
								$objects = scandir($dir);
								foreach ($objects as $object) {
									if ($object != "." && $object != "..") {
										if (filetype($dir . "/" . $object) == "dir")
											rrmdir($dir . "/" . $object);
										else
											unlink($dir . "/" . $object);
									}
								}
								reset($objects);
								rmdir($dir);
							}
						}

						
						/**
						 * Returns the full URL of the current page, based upon env variables
						 *
						 * Env variables used:
						 * $_SERVER['HTTPS'] = (on|off|)
						 * $_SERVER['HTTP_HOST'] = value of the Host: header
						 * $_SERVER['SERVER_PORT'] = port number (only used if not http/80,https/443)
						 * $_SERVER['REQUEST_URI'] = the URI after the method of the HTTP request
						 *
						 * @return string Current URL
						 */
						function getCurrentUrl() {
							global $_SERVER;

							/**
							 * Filter php_self to avoid a security vulnerability.
							 */
							$php_request_uri = htmlentities(substr($_SERVER['REQUEST_URI'], 0, strcspn($_SERVER['REQUEST_URI'], "\n\r")), ENT_QUOTES);

							if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
								$protocol = 'https://';
							} else {
								$protocol = 'http://';
							}
							$host = $_SERVER['HTTP_HOST'];
							if ($_SERVER['SERVER_PORT'] != '' && (($protocol == 'http://' && $_SERVER['SERVER_PORT'] != '80') || ($protocol == 'https://' && $_SERVER['SERVER_PORT'] != '443'))) {
								$port = ':' . $_SERVER['SERVER_PORT'];
							} else {
								$port = '';
							}
							return $protocol . $host . $port . $php_request_uri;
						}

						/**
						 * Returns the AuthSub URL which the user must visit to authenticate requests
						 * from this application.
						 *
						 * Uses getCurrentUrl() to get the next URL which the user will be redirected
						 * to after successfully authenticating with the Google service.
						 *
						 * @return string AuthSub URL
						 */
						function getAuthSubUrl() {
							global $_authSubKeyFile;
							$next = getCurrentUrl();
							$scope = 'http://picasaweb.google.com/data';
							$session = true;
							if ($_authSubKeyFile != null) {
								$secure = true;
							} else {
								$secure = false;
							}
							return Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
						}

						/**
						 * Outputs a request to the user to login to their Google account, including
						 * a link to the AuthSub URL.
						 *
						 * Uses getAuthSubUrl() to get the URL which the user must visit to authenticate
						 *
						 * @return void
						 */
						function requestUserLogin($linkText) {
							$authSubUrl = getAuthSubUrl();

							echo "<a href=\"{$authSubUrl}\"><img src='img/google-signin.png' />{$linkText}</a>";
						}

						/**
						 * Returns a HTTP client object with the appropriate headers for communicating
						 * with Google using AuthSub authentication.
						 *
						 * Uses the $_SESSION['sessionToken'] to store the AuthSub session token after
						 * it is obtained.  The single use token supplied in the URL when redirected
						 * after the user succesfully authenticated to Google is retrieved from the
						 * $_GET['token'] variable.
						 *
						 * @return Zend_Http_Client
						 */
						function getAuthSubHttpClient() {
							global $_SESSION, $_GET, $_authSubKeyFile, $_authSubKeyFilePassphrase;
							$client = new Zend_Gdata_HttpClient();
							if ($_authSubKeyFile != null) {
								// set the AuthSub key
								$client -> setAuthSubPrivateKeyFile($_authSubKeyFile, $_authSubKeyFilePassphrase, true);
							}
							if (!isset($_SESSION['sessionToken']) && isset($_GET['token'])) {
								$_SESSION['sessionToken'] = Zend_Gdata_AuthSub::getAuthSubSessionToken($_GET['token'], $client);
							}
							$client -> setAuthSubToken($_SESSION['sessionToken']);
							return $client;
						}

						/**
						 *Download File Form URL
						 * @param type $url : File Url to Download
						 * @param type $dir : Directory Path to store
						 */
						function getfile($url, $dir) {
							ini_set('max_execution_time', 300);
							file_put_contents($dir . substr($url, strrpos($url, '/'), strlen($url)), file_get_contents($url));
						}

						function movePhotos($dir) {
							//$dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

							if ($handle = opendir($dir)) {
								while (false !== ($filename = readdir($handle))) {
									$ext = substr($filename, strrpos($filename, '.') + 1);
									if (in_array($ext, array("jpg", "jpeg", "png", "gif"))) {
										addPhotoToAlbum($dir . '/' . $filename, $filename, $filename, "FB-Album", $dir);
									}
								}
							}
							closedir($handle);
							rrmdir($dir);
						}

						/**
						 * Processes loading of this sample code through a web browser.  Uses AuthSub
						 * authentication and outputs a list of a user's calendars if succesfully
						 * authenticated.
						 *
						 * @return void
						 */
						function processPageLoad() {
							global $_SESSION, $_GET;
							if (!isset($_SESSION['sessionToken']) && !isset($_GET['token'])) {
								print "<img src='img/Google-icon.png' /><br/>";
								requestUserLogin('');
							} else {
								$client = getAuthSubHttpClient();
								//fb_Initialize();
								if (isset($_GET['albumid'])) {
									//fb_makeAlbumDirectory();
									createAlbum($client, $_GET['albumid'], $_GET['albumid']);

									$dir = $_GET['albumid'];
									movePhotos($dir);
									header("location:index.php");
								} else if (isset($_GET['albumids'])) {
									$albumids = explode(",", $_GET['albumids']);
									foreach ($albumids as $album) {
										createAlbum($client, $album, $album);
										$dir = $album;
										movePhotos($dir);
										header("location:index.php");
									}
								}
								//createAlbum($client, "mehul12", "mehul");
								//addPhotoToAlbum("laanz.png","laanz","laanz","odesk","mehul12");
							}
						}

						/**
						 * Returns a HTTP client object with the appropriate headers for communicating
						 * with Google using the ClientLogin credentials supplied.
						 *
						 * @param  string $user The username, in e-mail address format, to authenticate
						 * @param  string $pass The password for the user specified
						 * @return Zend_Http_Client
						 */
						function getClientLoginHttpClient($user, $pass) {
							$service = Zend_Gdata_Photos::AUTH_SERVICE_NAME;

							$client = Zend_Gdata_ClientLogin::getHttpClient($user, $pass, $service);
							return $client;
						}

						/**
						 * Create a new instance of the service, redirecting the user
						 * to the AuthSub server if necessary.
						 */
						//$service = new Zend_Gdata_Photos(getAuthSubHttpClient(), "FBAlbum-Movers-1.0");

						/**
						 *  Create album with its title and summury in picasa
						 *
						 * @param  string $title The title of album
						 * @param  string $summury The description of the album
						 */
						function createAlbum($client, $title, $summury) {
							$service = new Zend_Gdata_Photos(getAuthSubHttpClient(), "FBAlbum-Movers-1.0");
							$entry = new Zend_Gdata_Photos_AlbumEntry();
							$entry -> setTitle($service -> newTitle($title));
							$entry -> setSummary($service -> newSummary($summury));

							$createdEntry = $service -> insertAlbumEntry($entry);
							//print_r($createdEntry);
						}

						/**
						 *  Create album with its title and summury in picasa
						 *
						 * @param  string $title The title of album
						 * @param  string $summury The description of the album
						 */
						function addPhotoToAlbum($file, $photoname, $photocaption, $phototags, $albumName) {
							$gp = new Zend_Gdata_Photos(getAuthSubHttpClient(), "FBAlbum-Movers-1.0");
							$username = "default";
							$filename = $file;
							$photoName = $photoname;
							$photoCaption = $photocaption;
							$photoTags = $phototags;

							// We use the albumId of 'default' to indicate that we'd like to upload
							// this photo into the 'drop box'.  This drop box album is automatically
							// created if it does not already exist.
							//$albumId = $albumid;

							$fd = $gp -> newMediaFileSource($filename);
							$fd -> setContentType("image/jpeg");

							// Create a PhotoEntry
							$photoEntry = $gp -> newPhotoEntry();

							$photoEntry -> setMediaSource($fd);
							$photoEntry -> setTitle($gp -> newTitle($photoName));
							$photoEntry -> setSummary($gp -> newSummary($photoCaption));

							// add some tags
							$keywords = new Zend_Gdata_Media_Extension_MediaKeywords();
							$keywords -> setText($photoTags);
							$photoEntry -> mediaGroup = new Zend_Gdata_Media_Extension_MediaGroup();
							$photoEntry -> mediaGroup -> keywords = $keywords;

							// We use the AlbumQuery class to generate the URL for the album
							$albumQuery = $gp -> newAlbumQuery();

							$albumQuery -> setUser($username);
							$albumQuery -> setAlbumName($albumName);

							// We insert the photo, and the server returns the entry representing
							// that photo after it is uploaded
							$insertedEntry = $gp -> insertPhotoEntry($photoEntry, $albumQuery -> getQueryUrl());

						}

						/**
						 * Main logic for running this sample code via the command line or,
						 * for AuthSub functionality only, via a web browser.  The output of
						 * many of the functions is in HTML format for demonstration purposes,
						 * so you may wish to pipe the output to Tidy when running from the
						 * command-line for clearer results.
						 *
						 * Run without any arguments to get usage information
						 */
						if (isset($argc) && $argc >= 2) {
							switch ($argv[1]) {
								case 'outputCalendar' :
									if ($argc == 4) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										outputCalendar($client);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} " . "<username> <password>\n";
									}
									break;
								case 'outputCalendarMagicCookie' :
									if ($argc == 4) {
										outputCalendarMagicCookie($argv[2], $argv[3]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} " . "<username> <magicCookie>\n";
									}
									break;
								case 'outputCalendarByDateRange' :
									if ($argc == 6) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										outputCalendarByDateRange($client, $argv[4], $argv[5]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} " . "<username> <password> <startDate> <endDate>\n";
									}
									break;
								case 'outputCalendarByFullTextQuery' :
									if ($argc == 5) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										outputCalendarByFullTextQuery($client, $argv[4]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} " . "<username> <password> <fullTextQuery>\n";
									}
									break;
								case 'outputCalendarList' :
									if ($argc == 4) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										outputCalendarList($client);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} " . "<username> <password>\n";
									}
									break;
								case 'updateEvent' :
									if ($argc == 6) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										updateEvent($client, $argv[4], $argv[5]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<eventId> <newTitle>\n";
									}
									break;
								case 'setReminder' :
									if ($argc == 6) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										setReminder($client, $argv[4], $argv[5]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<eventId> <minutes>\n";
									}
									break;
								case 'addExtendedProperty' :
									if ($argc == 7) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										addExtendedProperty($client, $argv[4], $argv[5], $argv[6]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<eventId> <name> <value>\n";
									}
									break;
								case 'deleteEventById' :
									if ($argc == 5) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										deleteEventById($client, $argv[4]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<eventId>\n";
									}
									break;
								case 'deleteEventByUrl' :
									if ($argc == 5) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										deleteEventByUrl($client, $argv[4]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<eventUrl>\n";
									}
									break;
								case 'createEvent' :
									if ($argc == 12) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										$id = createEvent($client, $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9], $argv[10], $argv[11]);
										print "Event created with ID: $id\n";
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<title> <description> <where> " . "<startDate> <startTime> <endDate> <endTime> <tzOffset>\n";
										echo "EXAMPLE: php {$argv[0]} {$argv[1]} <username> <password> " . "'Tennis with Beth' 'Meet for a quick lesson' 'On the courts' " . "'2008-01-01' '10:00' '2008-01-01' '11:00' '-08'\n";
									}
									break;
								case 'createQuickAddEvent' :
									if ($argc == 5) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										$id = createQuickAddEvent($client, $argv[4]);
										print "Event created with ID: $id\n";
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<quickAddText>\n";
										echo "EXAMPLE: php {$argv[0]} {$argv[1]} <username> <password> " . "'Dinner at the beach on Thursday 8 PM'\n";
									}
									break;
								case 'createWebContentEvent' :
									if ($argc == 12) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										$id = createWebContentEvent($client, $argv[4], $argv[5], $argv[6], $argv[7], $argv[8], $argv[9], $argv[10], $argv[11]);
										print "Event created with ID: $id\n";
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<title> <startDate> <endDate> <icon> <url> <height> <width> <type>\n\n";
										echo "This creates a web content event on 2007/06/09.\n";
										echo "EXAMPLE: php {$argv[0]} {$argv[1]} <username> <password> " . "'World Cup 2006' '2007-06-09' '2007-06-10' " . "'http://www.google.com/calendar/images/google-holiday.gif' " . "'http://www.google.com/logos/worldcup06.gif' " . "'120' '276' 'image/gif'\n";
									}
									break;
								case 'createRecurringEvent' :
									if ($argc == 7) {
										$client = getClientLoginHttpClient($argv[2], $argv[3]);
										createRecurringEvent($client, $argv[4], $argv[5], $argv[6]);
									} else {
										echo "Usage: php {$argv[0]} {$argv[1]} <username> <password> " . "<title> <description> <where>\n\n";
										echo "This creates an all-day event which occurs first on 2007/05/01" . "and repeats weekly on Tuesdays until 2007/09/04\n";
										echo "EXAMPLE: php {$argv[0]} {$argv[1]} <username> <password> " . "'Tennis with Beth' 'Meet for a quick lesson' 'On the courts'\n";
									}
									break;
							}
						} else if (!isset($_SERVER["HTTP_HOST"])) {
							// running from command line, but action left unspecified
							echo "Usage: php {$argv[0]} <action> [<username>] [<password>] " . "[<arg1> <arg2> ...]\n\n";
							echo "Possible action values include:\n" . "outputCalendar\n" . "outputCalendarMagicCookie\n" . "outputCalendarByDateRange\n" . "outputCalendarByFullTextQuery\n" . "outputCalendarList\n" . "updateEvent\n" . "deleteEventById\n" . "deleteEventByUrl\n" . "createEvent\n" . "createQuickAddEvent\n" . "createWebContentEvent\n" . "createRecurringEvent\n" . "setReminder\n" . "addExtendedProperty\n";
						} else {
							// running through web server - demonstrate AuthSub
							processPageLoad();
						}
						?>
					</center>
				</div>
			</div>

		</div>
	</body>
	<script src="js/foundation.min.js"></script>
	<script>
		$(document).foundation();
	</script>
</html>

