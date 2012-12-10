function init() {
	var inst;

	tinyMCEPopup.resizeToInnerSize();
	inst = tinyMCE.selectedInstance;

	// Give FF some time
	window.setTimeout('insertHelpIFrame();', 10);
}

function insertHelpIFrame() {
	var html = '<iframe width="100%" height="300" src="' + tinyMCE.themeURL + "/docs/" + tinyMCE.settings['docs_language'] + "/index.htm" + '"></iframe>';

	document.getElementById('iframecontainer').innerHTML = html;
}
