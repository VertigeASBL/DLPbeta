<html>
<head>
	<title>Ajax File Manager</title>
	<script language="javascript" type="text/javascript" src="jscripts/tiny_mce/tiny_mce.js"></script>
	<script language="javascript" type="text/javascript">
		tinyMCE.init({
			mode : "exact",
			elements : "ajaxfilemanager",
			theme : "advanced",
			plugins : "table,advhr,advimage,advlink,flash,paste,fullscreen,noneditable,contextmenu",
			theme_advanced_buttons1_add_before : "newdocument,separator",
			theme_advanced_buttons1_add : "fontselect,fontsizeselect",
			theme_advanced_buttons2_add : "separator,forecolor,backcolor,liststyle",
			theme_advanced_buttons2_add_before: "cut,copy,paste,pastetext,pasteword,separator,",
			theme_advanced_buttons3_add_before : "tablecontrols,separator",
			theme_advanced_buttons3_add : "flash,advhr,separator,fullscreen",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			extended_valid_elements : "hr[class|width|size|noshade]",
			file_browser_callback : "ajaxfilemanager",
			paste_use_dialog : false,
			theme_advanced_resizing : true,
			theme_advanced_resize_horizontal : true,
			apply_source_formatting : true,
			force_br_newlines : true,
			force_p_newlines : false,	
			relative_urls : true,
			
			content_css : "css_vertiny.css"

		});

		function ajaxfilemanager(field_name, url, type, win) {
			var ajaxfilemanagerurl = "../../../../jscripts/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php";
			switch (type) {
				case "image":
					ajaxfilemanagerurl += "?type=img";
					break;
				case "media":
					ajaxfilemanagerurl += "?type=media";
					break;
				case "flash": //for older versions of tinymce
					ajaxfilemanagerurl += "?type=media";
					break;
				case "file":
					ajaxfilemanagerurl += "?type=files";
					break;
				default:
					return false;
			}
			var fileBrowserWindow = new Array();
			fileBrowserWindow["file"] = ajaxfilemanagerurl;
			fileBrowserWindow["title"] = "Ajax File Manager";
			fileBrowserWindow["width"] = "782";
			fileBrowserWindow["height"] = "440";
			fileBrowserWindow["close_previous"] = "no";
			tinyMCE.openWindow(fileBrowserWindow, {
			  window : win,
			  input : field_name,
			  resizable : "yes",
			  inline : "yes",
			  editor_id : tinyMCE.getWindowArg("editor_id")
			});
			
			return false;
		}
	</script>
</head>
<body>

<?php 
if (isset($_POST['save']) AND ($_POST['save'] == 'Submit'))
{
	
	$allowedTags='<p><strong><em><u><h1><h2><h3><h4><h5><h6><img>';
	$allowedTags.='<li><ol><ul><span><div><br><ins><del>';
	$allowedTags.='<font><table><tbody><tr><th><td>';
	
	$contenu_tiny = strip_tags(stripslashes($_POST['ajaxfilemanager']),$allowedTags);
//	$contenu_tiny = $_POST['ajaxfilemanager'];
	echo $contenu_tiny .'<br>' ;

}

?>


<form id="form1" name="form1" method="post" action="">

  <p>
    <textarea id="ajaxfilemanager" name="ajaxfilemanager" style="width: 800px; height: 450px"><?php 
  if (isset($contenu_tiny) AND $contenu_tiny != NULL ) {echo $contenu_tiny ; } ?></textarea>
    <br />
    <input type="submit" name="save" value="Submit" />
    <input type="reset" name="reset" value="Reset" />
</p>
</form>
</body>
</html>