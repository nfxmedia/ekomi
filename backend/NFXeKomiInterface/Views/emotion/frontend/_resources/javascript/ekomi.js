/*
 *   Used for comments paging feature
 */
var page = 1;
//current page
var filter = 0;
// filter for 1 star, 2 stars etc; 0 = no filter
 
jQuery(document).ready(function($) {

	$('a[id^="hstar"]').click(function(e) {
		page = 1;
		rating = $(this).attr('id');
		filter = rating.charAt(rating.length - 1);
		recreateNav();
		e.preventDefault();
	});

	//first time load page 1 (page = 1) for the whole list (filter = 0)
	recreateNav();
});

function recreateNav() {
	//display / hide clear filter div
	if (filter == 0) {
		$(".rating-filter").hide();
	} else {
		$(".rating-filter span span").text(filter);
		$(".rating-filter").show();
	}

	//display the paged list
	$('.comment_block').each(function(index) {
		$(this).removeClass("last");
		if (((filter == 0) && ($(this).hasClass("page" + page))) || (($(this).hasClass("filter" + filter)) && ($(this).hasClass("pagef" + page)))) {
			$(this).css("display", "block");
		} else {
			$(this).css("display", "none");
		}
	});
	$('.comment_block').filter(function(index) {
		return $(this).css('display') == 'block';
	}).last().addClass("last");

	//display the navigation area
	var pages = $('#pagef' + filter).val();
	//total number of pages
	$(".comment_block_nav .right_container #pages").html("Seite <b>" + page + "</b> von <b>" + pages + "</b>");

	if (page < pages) {
		$(".comment_block_nav .right_container .comment_next").html('<a href="#" class="comment_next" onclick="getPage(' + (page + 1) + ');return false;">&nbsp;</a>');
		$(".comment_block_nav .right_container .comment_next_dis").html('');
	} else {
		$(".comment_block_nav .right_container .comment_next").html('');
		$(".comment_block_nav .right_container .comment_next_dis").html('<a href="#" class="comment_next_dis" onclick=";return false;">&nbsp;</a>');
	}
	var comment_paging = "";
	for (var i = 1; i <= pages; i++) {
		var step = ((page == 1) || (page == pages)) ? 4 : 3;
		if (((i < page + step) && (i > page - step)) || (i == 1) || (i == pages)) {
			if ((i > 1) || (page == 1)) {
				comment_paging += '&nbsp;';
			}
			if (i == page) {
				comment_paging += '<u>' + i + '</u>';
			} else {
				comment_paging += '<a href="#" onclick="getPage(' + i + ');return false;">' + i + '</a>';
			}
			if ((i < pages) || (page == pages)) {
				comment_paging += '&nbsp;';
			}
		} else {
			if ((i == page + step) || (i == page - step)) {
				comment_paging += '...';
			}
		}
	}
	$(".comment_block_nav .right_container .comment_paging").html(comment_paging);
	if (page > 1) {
		$(".comment_block_nav .right_container .comment_back").html('<a href="#" class="comment_back" onclick="getPage(' + (page - 1) + ');return false;">&nbsp;</a>');
		$(".comment_block_nav .right_container .comment_back_dis").html('');
	} else {
		$(".comment_block_nav .right_container .comment_back").html('');
		$(".comment_block_nav .right_container .comment_back_dis").html('<a href="#" class="comment_back_dis" onclick="return false;">&nbsp;</a>');
	}

}

function getPage(pageno) {
	page = pageno;
	recreateNav();
};