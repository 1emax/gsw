<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  support.php                                              ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/support_messages.php");

	// include blocks
	include_once("./blocks/block_articles_categories.php");
	include_once("./blocks/block_articles_breadcrumb.php");
	include_once("./blocks/block_articles_list.php");
	include_once("./blocks/block_articles_latest.php");
	include_once("./blocks/block_articles_top_rated.php");
	include_once("./blocks/block_articles_top_viewed.php");
	include_once("./blocks/block_articles_hot.php");
	include_once("./blocks/block_articles_content.php");
	include_once("./blocks/block_articles_search.php");
	include_once("./blocks/block_custom.php");
	include_once("./blocks/block_banners.php");
	include_once("./blocks/block_poll.php");

	$site_url = get_setting_value($settings, "site_url", "");
	$secure_url = get_setting_value($settings, "secure_url", "");
	$secure_redirect = get_setting_value($settings, "secure_redirect", 0);
	$secure_user_ticket = get_setting_value($settings, "secure_user_ticket", 0);
	if ($secure_user_ticket) {
		$support_url = $secure_url . "support.php";
	} else {
		$support_url = $site_url . "support.php";
	}
	if (!$is_ssl && $secure_user_ticket && $secure_redirect && preg_match("/^https/i", $secure_url)) {
		// move to SSL if secure option enabled
		header("Location: " . $support_url);
		exit;
	}

	$page_name = "support_new";
	$current_page = "support.php";
	$page_settings = va_page_settings($page_name, 0);

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "support.html");
	$t->set_var("current_href", "support.php");

	include_once("./header.php");

	if (is_array($page_settings)) {
		foreach ($page_settings as $setting_name => $setting_value)
		{
			if ($setting_name == "cart_block") {
				include_once("./blocks/block_cart.php");
				small_cart($setting_value);
			} elseif ($setting_name == "login_block") {
				login_form($setting_value);
			} elseif ($setting_name == "subscribe_block") {
				include_once("./blocks/block_subscribe.php");
				subscribe_form($setting_value);
			} elseif ($setting_name == "sms_test_block") {
				include_once("./blocks/block_sms_test.php");
				sms_test_form($setting_value);
			} elseif ($setting_name == "poll_block") {
				poll_form($setting_value);
			} elseif ($setting_name == "userhome_breadcrumb") {
				include_once("./blocks/block_userhome_breadcrumb.php");
				userhome_breadcrumb($setting_value);
			} elseif ($setting_name == "support_block") {
				include_once("./blocks/block_support.php");
				support_block($setting_value);
			} elseif ($setting_name == "language_block") {
				include_once("./blocks/block_language.php");
				language_form($setting_value, $page_settings["language_selection"]);
			} elseif ($setting_name == "currency_block") {
				include_once("./blocks/block_currency.php");
				currency_form($setting_value);
			} elseif ($setting_name == "layouts_block") {
				include_once("./blocks/block_layouts.php");
				layouts($setting_value);
			} elseif ($setting_name == "site_search_form") {
				include_once("./blocks/block_site_search_form.php");
				site_search_form($setting_value);
			} elseif (preg_match("/^navigation_block_(\d+)$/", $setting_name, $matches)) {
				include_once("./blocks/block_navigation.php");
				navigation_menu($setting_value, $matches[1]);
			} elseif (preg_match("/^custom_block_/", $setting_name)) {
				custom_block($setting_value, substr($setting_name, 13));
			} elseif (preg_match("/^banners_group_/", $setting_name)) {
				banners_group($setting_value, substr($setting_name, 14));
			} elseif (preg_match("/^a_latest_(\d+)$/", $setting_name, $matches)) {
				articles_latest($setting_value, $matches[1], "");
			} elseif (preg_match("/^a_top_rated_(\d+)$/", $setting_name, $matches)) {
				articles_top_rated($setting_value, $matches[1], "");
			} elseif (preg_match("/^a_top_viewed_(\d+)$/", $setting_name, $matches)) {
				articles_top_viewed($setting_value, $matches[1], "");
			} elseif (preg_match("/^a_hot_(\d+)$/", $setting_name, $matches)) {
				articles_hot($setting_value, $matches[1], "", "", "");
			} elseif (preg_match("/^a_cats_(\d+)$/", $setting_name, $matches)) {
				articles_categories($setting_value, $matches[1], "", "a_cats");
			} elseif (preg_match("/^a_subcats_(\d+)$/", $setting_name, $matches)) {
				articles_categories($setting_value, $matches[1], "", "a_subcats");
			} elseif (preg_match("/^a_search_(\d+)$/", $setting_name, $matches)) {
				articles_search($setting_value, $matches[1], "");
			} 
		}
	}

	if (!get_setting_value($page_settings, "left_column_hide", 0)) {
		$t->set_var("left_column_width", get_setting_value($page_settings, "left_column_width", "20%"));
		$t->parse("left_column", false);
	}
	if (!get_setting_value($page_settings, "middle_column_hide", 0)) {
		$t->set_var("middle_column_width", get_setting_value($page_settings, "middle_column_width", "60%"));
		$t->parse("middle_column", false);
	}
	if (!get_setting_value($page_settings, "right_column_hide", 0)) {
		$t->set_var("right_column_width", get_setting_value($page_settings, "right_column_width", "20%"));
		$t->parse("right_column", false);
	}

	include_once("./footer.php");

	$t->pparse("main");

?>