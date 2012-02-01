fMath Wysiwyg Bridge

From incarnated.net

-- SUMMARY --

This module is acts as a bridge between the fMath module and the Wysiwyg API
module.

-- REQUIREMENTS --

* Wysiwyg API


-- INSTALLATION --
Adapted from http://www.fmath.info/plugins/drupal/doc.jsp

Steps:
1. Install Drupal + WYSIWYG module (is not the scope of this presentation):
	Download Drupal 7 package and install on your server.
	Download the WYSIWYG module, unzip.
	Copy the folder WYSIWYG to sites/all/modules/.
	Download CKEditor from ckeditor.com/download, unzip.
	Copy CKEditor to sites/all/libraries/ckeditor/.
	
	Enable WYSIWYG module in Drupal
	Enable CKEditor in the WYSIWYG profiles for the formats you want it for (e.g. Filtered and Full HTML)
	Verify the CKEditor is working when you add/edit content in Drupal;
	
2. Install fMath plugin:	
	Download the plugin for CKEditor from download area
	http://www.fmath.info/editor/download.jsp
	and unzip the package;
	Copy the folder fmath_formula to sites/all/libraries/ckeditor/plugins folder;

3. Install the fMath WYSIWIG plugin:
	Add this module to Drupal and activate it.
	Enable the fMath button in the WYSIWYG profiles page for the wanted formats.
	
	Now, the button on toolbar should be displayed. When you click the editor, it should show up.
	You will not be able to save the image to the page until you complete the next steps.
	
4. Install the server side script to generate the images:
	Go to www.fmath.info/editor/ and download editorPhpServerSide-vx.x-x.zip
	Unzip the file.
	Create on your server a folder "capture"; Must be available from www.yourserver/capture
	Copy the file imageCapture.php to this folder; It must have the permission to execute;
	Create a folder img in capture folder (capture/img).
	Edit the file imageCapture.php and change www.yourserver.com with your server url
	
	Edit the file sites/all/libraries/ckeditor/plugins/fmath_formula/dialogs/configMathMLEditor.xml
	to add or edit the property "urlGenerateImage" to 
	http://yourserver/capture/imageCapture.php
	
5. Test
	Clear the cache of browser (temporary files);
	Create an equation and save;


-- USAGE --

* Please consult the documentation of fMath for further information.  This is
  just an integration module.


