$(document).ready(function(){
	"use strict";

	// Clear the generated URL field and the code view window when an input or select field changes.
	$("input, select").change(function(){
		$("#url").val('');
	});

	// If the all forums checkbox is checked, all individual forums should be checked, and visa versa. Ignore excluded
	// and included forums as these should always retain their original disabled setting.
	$("#all_forums").click(function(){
		if ($("#all_forums").is(':checked')) {
			$("[id*=elt_]").each(function() {
				if (!exclude_forum($(this).attr('id'))) {
					$(this).prop("checked", true);
				}
			});
		}
		else {
			$("[id*=elt_]").each(function() {
				if (!exclude_forum($(this).attr('id'))) {
					$(this).prop("checked", false);
				}
			});
		}
	});

	// If any individual forum is unchecked, the all_forums checkbox should be unchecked. Exception: required or excluded forums.
	// If all individual forums are checked, the all_forums checkbox should be checked. Exception: required or excluded forums.
	$("[id*=elt_]").click(function() {
		var allChecked = true;	// Assume all forums are checked
		$("[id*=elt_]").each(function() {
			$("#all_forums").prop('checked', false);
			if ((!ignore_forum($(this).attr('id'))) && !$(this).is(':checked')) {
				allChecked = false;	// Flag if any forum is unchecked
			}
		});
		if (allChecked) {
			($("#all_forums").prop('checked', true));
		}
	});

	// If bookmarked topics only is selected, disable the forum controls, otherwise enable them. All forums checkbox also needs
	// to be enabled or disabled.
		$("#bookmarks, #firstpostonly1, #firstpostonly2, #all").click(function() {
		var disabled = $("#bookmarks").is(':checked');
		$("[id*=elt_]").each(function() {
			if (!ignore_forum($(this).attr('id'))) {
				$(this).prop('disabled', disabled);
			}
		});
		$("#all_forums").prop('disabled', disabled);
	});

	function exclude_forum(forumId) {
		// Returns true if the forum representing forumId should be excluded. Pattern is elt_1_2 where 1 is the forum_id
		// and 2 is the parent forum_id.
		var start = forumId.indexOf('_');
		var end = forumId.lastIndexOf('_');
		return excludedForumsArray.indexOf(forumId.substring(start+1,end)) !== -1;
	}

	function ignore_forum(forumId) {
		// Returns true the forum representing forumId should be ignored. Pattern is elt_1_2 where 1 is the forum_id
		// and 2 is the parent forum_id.
		var start = forumId.indexOf('_');
		var end = forumId.lastIndexOf('_');
		return ignoredForumsArray.indexOf(forumId.substring(start+1,end)) !== -1;
	}

});
