<?php
ob_start();
session_start();
require_once ("fbCredentials.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title></title>
		<link href="css/bootstrap.min.css" rel="stylesheet">
		<link href="css/styles.css" rel="stylesheet">
		<link rel="stylesheet" href="css/forkit.css">
		<link rel="stylesheet" href="css/foundation.css" />
		<link rel="stylesheet" href="css/foundation-icons.css" />
		<script src="js/modernizr.js"></script>
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
					<!--SuprizedMe full screen slider -->
					<div id="slider" style="display:none">
						<div id="backalbum">
							<button id="backtoalbum" style="margin-top:-50px" class="button small">
								Back to Albums
							</button>
							<button id="btnDownload" style="margin-top:-50px" class="button success small">
								Download Album
							</button>
						</div>
						<!--Thumbnail Navigation-->
						<div id="prevthumb"></div>
						<div id="nextthumb"></div>

						<!--Arrow Navigation-->
						<a style="margin-top: 50%;margin-left: -5%;" id="prevslide" class="load-item"></a>
						<a style="margin-top: 50%;margin-right: -5%;" id="nextslide" class="load-item"></a>

						<div id="thumb-tray" class="load-item">
							<div id="thumb-back"></div>
							<div id="thumb-forward"></div>
						</div>

						<!--Time Bar-->
						<div id="progress-back" class="load-item">
							<div id="progress-bar"></div>
						</div>

						<!--Control Bar-->
						<div id="controls-wrapper" class="load-item">
							<div id="controls">

								<a id="play-button"><img id="pauseplay" src="img/pause.png"/></a>

								<!--Slide counter-->
								<div id="slidecounter">
									<span class="slidenumber"></span> / <span class="totalslides"></span>
								</div>

								<!--Slide captions displayed here-->
								<div id="slidecaption"></div>

								<!--Thumb Tray button-->
								<a id="tray-button"><img id="tray-arrow" src="img/button-tray-up.png"/></a>

								<!--Navigation-->
								<ul id="slide-list"></ul>

							</div>
						</div>
					</div>

					<!-- Side Bar -->

					<div class="large-4 small-12 columns connect container" style="display:none">

						<img id="ProfilePic" src="http://placehold.it/500x500&text=Logo">

						<div class="hide-for-small panel">
							<h3 id="UserName">Header</h3>

						</div>
					</div>

					<!-- End Side Bar -->

					<!-- Thumbnails -->

					<div class="large-8 columns connect container" style="display: none;">
						<div id="albums" class="row">

						</div>

						<!-- End Thumbnails -->
						<button id="download_album_all" class="button small">
							Download all
						</button>
						<button id="download_album_select" class="button small">
							Download selected
						</button>
					</div>
					<center>
						<button class="button large" id='fblogin'>
							Login with Facebook
						</button>
					</center>
				</div>
			</div>

		</div>

		<!--Model window for Download -->
		<a href="#myModal" role="button" id="openmodel" class="btn" data-toggle="modal" style="display:none"> </a>
		<div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none" >
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					×
				</button>
				<h4 id="myModalLabel">Preparing your files to be download</h4>
			</div>
			<div class="modal-body">
				<!--                 Progress bar    -->
				<div class="progress progress-striped active" id="downloadprogress">
					<div class="bar" style="width: 100%;"></div>
				</div>
			</div>
			<div class="modal-footer" id="downloadlink" style="display:none">
				<button class="btn" data-dismiss="modal" aria-hidden="true" id='modelclose'>
					Close
				</button>
				<!--Download Button -->
				<a href="" id="hrefDownload" class="btn btn-primary" onclick="$('#modelclose').click();">Click Here to Download</a>
				<div></div>
			</div>
		</div>
		<!-- Load External js Lib-->
		<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>

		<script src="js/bootstrap.min.js"></script>
		<div id="fb-root"></div>
		<script>var appId =  '<?php echo $AppId; ?>';
		</script>
		<script src="js/foundation.min.js"></script>
		<script>
			$(document).foundation();
		</script>
		<link rel="stylesheet" href="css/supersized.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="theme/supersized.shutter.css" type="text/css" media="screen" />
		<script type="text/javascript" src="js/jquery.easing.min.js"></script>
		<script type="text/javascript" src="js/supersized.3.2.7.min.js"></script>
		<script type="text/javascript" src="theme/supersized.shutter.min.js"></script>
		<script src="js/scripts.js"></script>

	</body>
</html>
