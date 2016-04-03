function uncheck_subscribed_forums (checkbox) {
	
	isChecked = checkbox.checked;
	var elementName = new String();
	var x = document.getElementById('div_0').getElementsByTagName("input");

	for(i=0;i<x.length;i++) {
		thisObject = x[i];
		elementName = thisObject.id;
		if(elementName != null) {
			if(elementName.substr(0,4) == "elt_") {
				thisObject.checked = isChecked;
			}
		}
	}
	
	return true;
	
}

function check_or_uncheck_all_forums () {

	// Unchecks or checks the all forums checkbox
	var newsID = document.getElementById('phpbbservices_smartfeed');
	anyUnchecked = false;
	
	var x = document.getElementById('div_0');
	var y = x.getElementsByTagName("input");
	for(i=0;((i<y.length) && (anyUnchecked === false));i++) {
		thisObject = y[i];
		elementName = thisObject.name;
		if(elementName !== null) {
			if(elementName.substr(0,4) === "elt_") {
				if (thisObject.checked === false) {
					newsID.all_forums.checked = false;
					anyUnchecked = true;
				}
			}
		}
	}
	if (anyUnchecked === false) {
		newsID.all_forums.checked = true;
	}

	return;
}

function disable_or_enable_all_the_forums(disabledFlag) {
	
	// This function disables the checkboxes next to the forum names so it cannot be checked or enables it, based on the value of disabledFlag.  
	var elementName = new String();
	var newsID = document.getElementById('phpbbservices_smartfeed');
	
	var x = document.getElementById('div_0').getElementsByTagName('input');
	for(i=0;i<x.length;i++) {
		thisObject = x[i];
		elementName = thisObject.id;
		if(elementName !== null) {
			if(elementName.substr(0,4) === "elt_") {
				thisObject.disabled = disabledFlag;
			}
		}
	}
	
	newsID.all_forums.disabled = disabledFlag;
	return true;
}

function reset_url(element) {
	// Blank out the generated URL field
	var newsID = document.getElementById('phpbbservices_smartfeed');
	newsID.url.value = '';
}

function test_feed() {
	// Executed when the Test button is pressed. It opens the created URL in a new window/tab for testing
	var url = document.getElementById("url");
	if (url.value.length > 0) {
		window.open(url.value);
	}
	return;
}
