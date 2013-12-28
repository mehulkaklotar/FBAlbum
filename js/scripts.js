var albumids = new Array();
// to store all albums IDs
var albumidsselect = new Array();
// to store all selected albums IDs

var fbAuthResp;

$(document).ready(function() {
	$('#supersized-loader').hide();
	$('#supersized').hide();

	// Download all selected albums
	$('#download_album_select').click(function(event) {
		event.preventDefault();
		//Album ids to array for download
		var i = 0;
		$('.checkboxSelect:checked').each(function() {

			albumidsselect[i] = $(this).val();
			i++;

		});
		downloadAllAlbums(albumidsselect);
	});

	// Move all selected albums
	$('#move_album_select').click(function(event) {
		event.preventDefault();
		//Album ids to array for download
		var i = 0;
		$('.checkboxSelect:checked').each(function() {

			albumidsselect[i] = $(this).val();
			i++;

		});
		moveAllAlbums(albumidsselect);
	});

	//Logout
	$('#logout').click(function() {
		FB.logout(function(response) {
			// user is now logged out
			window.location.reload();
			$("#fblogin").show();
			$('#supersized-loader').hide();
		});
	});

});

//Authanticate User with app
$("#fblogin").click(function() {

	$('#supersized-loader').show();
	FB.login(function(response) {
		if (response.authResponse) {
			fbAuthResp = response;
			//Set Accesstoken of user in session
			$.ajax({
				url : 'fb.php',
				type : 'post',
				data : {
					'accesstoken' : response.authResponse.accessToken
				},
				success : function(data) {

				}
			});
			//Get User Name
			FB.api('/me?fields=name', function(respo) {
				$("#UserName").html(respo.name);
				$("#title").html(respo.name + "'s Albums");
				$("#fblogin").hide();
				$('#ProfilePic').attr('src', 'http://graph.facebook.com/' + respo.id + '/picture?width=500&height=500');
				//Get All ablums of user
				FB.api('/me/albums', showAlbums);
			});

		} else {
			//User close auth window
			$('#supersized-loader').hide();
			alert('User cancelled login or did not fully authorize.');
		}
	}, {
		scope : 'email,user_photos,friends_photos'
	});

});

/**
 * Process response of /me/albums and display it
 */
function showAlbums(response) {
	$('#galleryLoading').hide();
	$('.container').show();
	$('#supersized-loader').hide();
	$.each(response.data, function(key, value) {

		//Album ids to array for download
		albumids[key] = value.id;

		//create html structure
		var strHtml = '' + '<div id="album_' + key + '" class="large-4 small-6 columns"> ' + '<a href="#" class="album_link_' + key + '"><img height="1000" width="1000" class="imgcover" id="album_cover_' + key + '" /></a>' + '<img id="loading_' + key + '" src="../img/ajax-loader.gif" /><div class="panel"><input class="checkboxSelect" id="checkbox_' + key + '" type="checkbox" value="' + value.id + '"><a for="checkbox_' + key + '" href="#" class="album_link_' + key + '"><h5>' + value.name + '</h5></a><label class="subheader">' + value.count + ' photos</label><ul class="button-group"><li><a title="Download" id="download_album_' + key + '" class="button success tiny step fi-download size-36"></a></li><li><a title="Move to Picasa" id="move_album_' + key + '" class="button success tiny">Move</a></li></ul>' + '</div></div>';

		$('#albums').append(strHtml);
		FB.api('/' + value.cover_photo + '', function(response) {
			if (!response.picture) {
				$('#album_' + key).hide();
			} else {
				$('#loading_' + key).hide();
				$('#album_cover_' + key).attr("src", response.picture);
			}
		});

		//Show albums photos in gallery
		$('.album_link_' + key).click(function(event) {
			event.preventDefault();
			show_albums_photos(value.id);
		});

		//Download album & zip creation
		$('#download_album_' + key).click(function(event) {
			event.preventDefault();
			downloadAlbum(value.id);
		});

		//Move the albums to google plus/picasa
		$('#move_album_' + key).click(function(event) {
			event.preventDefault();
			moveAlbum(value.id);
		});

	});

	//Download all albums & zip creation
	$('#download_album_all').click(function(event) {
		event.preventDefault();
		downloadAllAlbums(albumids);
	});

	//Move all albums
	$('#move_album_all').click(function(event) {
		event.preventDefault();
		moveAllAlbums(albumids);
	});

}

/**
 * To start downalod all images and zip in to file
 */
function downloadAlbum(albumId) {
	$("#downloadlink").hide();
	$("#downloadprogress").show();
	$("#openmodel").click();
	//location.href="fb.php?albumid="+ albumId;
	$.ajax({
		url : 'fb.php?albumid=' + albumId,
		type : 'get',
		success : function(data) {
			//show download button
			$("#downloadprogress").hide();
			$("#downloadlink").show();
			$("#hrefDownload").attr('href', albumId + '.zip');
		},
		error : function(data) {
			//Handle error
			$('#modelclose').click();
			alert('Error Occure on server,Please Try again')
		}
	});
}

/**
 * To start move images to google picasa
 */
function moveAlbum(albumId) {
	$("#downloadlink").hide();
	$("#downloadprogress").show();
	$("#openmodel").click();
	//location.href = "fb.php?albumid=" + albumId +"&move=true";
	$.ajax({
		url : 'fb.php?albumid=' + albumId + '&move=true',
		type : 'get',
		success : function(data) {
			//progress bar close
			$('#modelclose').click();
			location.href = "picasamove.php?albumid=" + albumId;
		},
		error : function(data) {
			//Handle error
			$('#modelclose').click();
			alert('Error Occure on server,Please Try again')
		}
	});
}

/**
 * To start move images to google picasa
 */
function moveAllAlbums(albumIds) {
	$("#downloadlink").hide();
	$("#downloadprogress").show();
	$("#openmodel").click();
	//location.href = "fb.php?albumids=" + albumIds +"&move=true";
	$.ajax({
		url : 'fb.php?albumids=' + albumIds + '&move=true',
		type : 'get',
		success : function(data) {
			//progress bar close
			$('#modelclose').click();
			location.href = "picasamove.php?albumids=" + albumIds;
		},
		error : function(data) {
			//Handle error
			$('#modelclose').click();
			alert('Error Occure on server,Please Try again')
		}
	});
}

/**
 * To start downalod all albums and zip
 */
function downloadAllAlbums(albumIds) {
	var uid;
	$("#downloadlink").hide();
	$("#downloadprogress").show();
	$("#openmodel").click();
	//location.href="fb.php?albumids="+ albumIds;
	$.ajax({
		url : 'fb.php?albumids=' + albumIds,
		type : 'get',
		success : function(data) {
			//get userid from facebook api
			FB.api('/me', function(response) {
				//show download button
				$("#downloadprogress").hide();
				$("#downloadlink").show();
				$("#hrefDownload").attr('href', response.id + '.zip');
			});

		},
		error : function(data) {
			//Handle error
			$('#modelclose').click();
			alert('Error Occure on server,Please Try again')
		}
	});
}

//get all photos for an album and hide the album view

var lastAlbumId;
function show_albums_photos(album_id) {

	lastAlbumId = album_id;
	$('#loading_gallery').show();
	$('.connect').hide();
	$('.top-bar').hide();

	$('#supersized-loader').show();
	$('#supersized').show();
	if ($('#album_' + album_id).length > 0) {
		$('#album_' + album_id).show();
	} else {
		FB.api('/' + album_id + '/photos', function(response) {
			var arrPhotos = [];
			// console.log(response.data);
			$.each(response.data, function(key, value) {
				arrPhotos.push({
					image : value.source,
					title : (value.name != undefined) ? value.name : '',
					thumb : value.picture,
					url : value.link
				})
			});
			$('#loading_gallery').hide();
			jQuery(function($) {
				$.supersized({
					slide_interval : 8000, // Length between transitions
					transition : 1, // 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left
					transition_speed : 700, // Speed of transition
					// Components
					slide_links : 'blank', // Individual links for each slide (Options: false, 'num', 'name', 'blank')
					slides : arrPhotos

				});
			});
		});
		$('#slider').show();

	}
}

//back to album from full screen slideshow
$("#backtoalbum").click(function() {
	$('#supersized-loader').hide();
	$('#supersized').hide();
	$('#slider').hide();
	$("#thumb-list").remove();
	$("#supersized").html('');
	$('.connect').show();
	$('.top-bar').show();
});

//Download Button in  Slideshow
$("#btnDownload").click(function() {
	downloadAlbum(lastAlbumId);
});

//Move Button in  Slideshow
$("#btnMove").click(function() {
	moveAlbum(lastAlbumId);
});
