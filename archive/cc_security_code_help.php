<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  cc_security_code_help.php                                ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "cc_security_code_help.html");
	$t->set_var("CHARSET", CHARSET);
	$t->set_var("CC_SECURITY_CODE_TITLE",    CC_SECURITY_CODE_TITLE);
	$t->set_var("CC_SECURITY_CODE_1_DESC",   CC_SECURITY_CODE_1_DESC);
	$t->set_var("CC_SECURITY_CODE_2_DESC",   CC_SECURITY_CODE_2_DESC);
	$t->set_var("CLOSE_WINDOW_MSG", CLOSE_WINDOW_MSG);

	$t->pparse("main");

?>