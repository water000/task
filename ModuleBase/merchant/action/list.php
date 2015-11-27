<?php

if(isset($_FILES['file'])){
	var_dump($_FILES);
}


?>
<html>
<head>
	<title>test chrome paste image</title>
<style>
	DIV#editable {
		width: 400px;
		height: 300px;
		border: 1px dashed blue;
	}
</style>
<script type="text/javascript" src="/static/js/jquery-1.3.1.min.js"></script>
<script type="text/javascript">

function upload_file(f){
	var c = new FormData;
    c.append("via", "xhr2");
    c.append("file", f);

    var oReq = new XMLHttpRequest();
    oReq.open("POST", "/merchant/list");
    oReq.send(c);
}
window.onload=function() {
	chrome.storage.local.get('chosenFile', function(items) {
		var accepts = [{
		    mimeTypes: ['image/*']
		  }];
		 chrome.fileSystem.chooseEntry({type: 'openFile', accepts: accepts, suggestedName:"C:/Users/tiger/AppData/Local/Temp/msohtmlclip1/01/clip_image008.jpg"}, function(theEntry) {
		    if (!theEntry) {
		      alert('No file selected.');
		      return;
		    }
		  });
	});
	
	function paste_img(e) {
		if ( e.clipboardData ) {
		// google-chrome 
			ele = e.clipboardData.items
			for (var i = 0; i < ele.length; ++i) {
				if ( ele[i].kind == 'file' && ele[i].type.indexOf('image/') !== -1 ) {
					alert("upload file" + i);
					upload_file(ele[i].getAsFile());
				}else{
					ele[i].getAsString(function(s){alert(s);});
				}
			}
		} else {
			alert('non-chrome');
		}
	}
	document.getElementById('editable').onpaste=function(e){paste_img(e||event);return false;};
}

</script>
</head>
<body >
	<h2>test image paste in browser</h2>
	<div id="editable" contenteditable="true" >
		<p>this is an editable div area</p>
		<p>paste the image into here.</p>
	</div>
</body>
</html>