<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  barcode_image.php                                        ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/barcode_functions.php");

	$text = isset($_REQUEST['text']) ? $_REQUEST['text'] : '123456789012';  				// parameter: bar code text
	$imgtype = isset($_REQUEST['imgtype']) ? $_REQUEST['imgtype'] : 'png'; 					// parameter: image type (png, gif, jpg)
	$codetype = isset($_REQUEST['codetype']) ? $_REQUEST['codetype'] : 'code128'; 	// parameter: code type (code128, ean13, code39, int25, upca)

	draw_barcode($text, $imgtype, $codetype);

?>