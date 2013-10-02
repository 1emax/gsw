<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  forum_topic.php                                          ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/forum_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/record.php");
	include_once("./includes/icons_functions.php");

	// include blocks
	include_once("./blocks/block_custom.php");
	include_once("./blocks/block_banners.php");

	$display_forums = get_setting_value($settings, "display_forums", 0);
	if ($display_forums == 1) {
		// user need to be logged in before viewing forum 
		check_user_session();
	}
	$user_id = get_session("session_user_id");
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");
	
	$page_settings = va_page_settings("forum_topic", 0);
	$currency = get_currency();
	$thread_id = get_param("thread_id");

	$page_friendly_url = ""; 
	$page_friendly_params = array("thread_id");

	$forum_image = ""; $forum_description = "";
	$desc_image = get_setting_value($page_settings, "forum_description_image", 3);
	$desc_type = get_setting_value($page_settings, "forum_description_type", 2);

	// additional connection to get forum details
	$db2 = new VA_SQL();
	$db2->DBType      = $db_type;
	$db2->DBDatabase  = $db_name;
	$db2->DBHost      = $db_host;
	$db2->DBPort      = $db_port;
	$db2->DBUser      = $db_user;
	$db2->DBPassword  = $db_password;
	$db2->DBPersistent= $db_persistent;	
	
	// retrieve info about current forum
	// if forum is hidden, thread is hidden too	
	$sql  = " SELECT fl.forum_id, fl.forum_name, fl.short_description, fl.full_description, ";
	$sql .= " fl.small_image, fl.large_image, f.topic, f.friendly_url, ";
	$sql .= " fl.allowed_view, fl.allowed_view_topics, fl.allowed_view_topic, fl.allowed_post_topics, fl.allowed_post_replies, ";
	$sql .= " fl.view_forum_types_all, fl.view_topics_types_all, fl.view_topic_types_all, ";
	$sql .= " fl.post_topics_types_all, fl.post_replies_types_all, fl.allowed_attachments, fl.attachments_types_all ";
	if (isset($site_id)) {
		$sql .= " FROM ((((" . $table_prefix . "forum_list fl ";
	} else {
		$sql .= " FROM (((" . $table_prefix . "forum_list fl ";	
	}
	$sql .= " INNER JOIN " . $table_prefix . "forum f ON  f.forum_id=fl.forum_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "forum_view_topic view_topic ON view_topic.forum_id=fl.forum_id)";
	$sql .= " LEFT JOIN " . $table_prefix . "forum_categories fc ON fc.category_id=fl.category_id)";	
	if (isset($site_id)) {			
		$sql .= " LEFT JOIN " . $table_prefix . "forum_categories_sites fcs ON fcs.category_id=fl.category_id)";
		$sql .= " WHERE (fc.sites_all=1 OR fcs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
	} else {
		$sql .= " WHERE fc.sites_all=1 ";					
	}
	$sql .= " AND f.thread_id=" . $db->tosql($thread_id, INTEGER);		

	if (strlen($user_id)) {
		$sql .= " AND ( fl.allowed_view_topic = 1 OR ( fl.allowed_view_topic = 2  AND ( view_topic.user_type_id=". $db->tosql($user_type_id , INTEGER) . " OR fl.view_topic_types_all=1) ) )";
	} else {
		$sql .= " AND fl.allowed_view_topic =1 ";
	}
	

	$db->query($sql);
	if ($db->next_record()) {
		$forum_id = $db->f("forum_id");
		$forum_name = $db->f("forum_name");
		$page_friendly_url = $db->f("friendly_url");
		friendly_url_redirect($page_friendly_url, $page_friendly_params);
		
		// check forum view privileges
		$allowed_view = 0; 
		if ($db->f("allowed_view")==1) {
			$allowed_view = 1;
		} elseif (strlen($user_id) && $db->f("allowed_view")==2) {
			if ($db->f("view_forum_types_all")) {
				$allowed_view = 2; 	
			} else {
				$sql  = " SELECT user_type_id FROM " . $table_prefix . "forum_view_types ";
				$sql .= " WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
				$sql .= " AND user_type_id=" . $db->tosql($user_type_id, INTEGER);
				$db2->query($sql);
				if ($db2->next_record())
					$allowed_view = 2;					
			}
		}
				
		// check topics view privileges
		$allowed_view_topics = 0; 
		if ($db->f("allowed_view_topics")==1) {
			$allowed_view_topics = 1;
		} elseif (strlen($user_id) && $db->f("allowed_view_topics")==2) {
			if ($db->f("view_topics_types_all")) {
				$allowed_view_topics = 2; 	
			} else {
				$sql  = " SELECT user_type_id FROM " . $table_prefix . "forum_view_topics ";
				$sql .= " WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
				$sql .= " AND user_type_id=" . $db->tosql($user_type_id, INTEGER);
				$db2->query($sql);
				if ($db2->next_record())
					$allowed_view_topics = 2;					
			}
		}
		
		// check topic view privileges
		$allowed_view_topic = 0; 
		if ($db->f("allowed_view_topic")==1) {
			$allowed_view_topic = 1;
		} elseif (strlen($user_id) && $db->f("allowed_view_topic")==2) {
			if ($db->f("view_topic_types_all")) {
				$allowed_view_topic = 1; 	
			} else {
				$sql  = " SELECT user_type_id FROM " . $table_prefix . "forum_view_topic ";
				$sql .= " WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
				$sql .= " AND user_type_id=" . $db->tosql($user_type_id, INTEGER);
				$db2->query($sql);
				if ($db2->next_record())
					$allowed_view_topic = 1;					
			}
		}

	
		// check topics post privileges
		$allowed_post_topics = 0;		
		if ($db->f("allowed_post_topics")==1) {
			$allowed_post_topics = 1;
		} elseif (strlen($user_id) && $db->f("allowed_post_topics")==2) {
			if ($db->f("post_topics_types_all")) {
				$allowed_post_topics = 2; 	
			} else {
				$sql  = " SELECT user_type_id FROM " . $table_prefix . "forum_post_topics ";
				$sql .= " WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
				$sql .= " AND user_type_id=" . $db->tosql($user_type_id, INTEGER);
				$db2->query($sql);
				if ($db2->next_record())
					$allowed_post_topics = 2;					
			}
		}
		
		// check replies post privileges
		$allowed_post_replies = 0;		
		if ($db->f("allowed_post_replies")==1) {
			$allowed_post_replies = 1;
		} elseif (strlen($user_id) && $db->f("allowed_post_replies")==2) {
			if ($db->f("post_replies_types_all")) {
				$allowed_post_replies = 2; 	
			} else {
				$sql  = " SELECT user_type_id FROM " . $table_prefix . "forum_post_replies ";
				$sql .= " WHERE forum_id=" . $db->tosql($forum_id, INTEGER);
				$sql .= " AND user_type_id=" . $db->tosql($user_type_id, INTEGER);
				$db2->query($sql);
				if ($db2->next_record())
					$allowed_post_replies = 2;					
			}
		}

		if ($desc_image == 3) {
			$forum_image = $db->f("large_image");
		} elseif ($desc_image == 2) {
			$forum_image = $db->f("small_image");
		}
		if ($desc_type == 2) {
			$forum_description = get_translation($db->f("full_description"));
		} elseif ($desc_type == 1) {
			$forum_description = get_translation($db->f("short_description"));
		}

	} else {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}

	if (!$allowed_view || !$allowed_view_topics || !$allowed_view_topic) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	} elseif ($allowed_view == 2 || $allowed_view_topics == 2 || $allowed_view_topic == 2) {
		// user need to be logged in before viewing forum
		check_user_session();
	}

	// prepare icons to replace in the text
	prepare_icons($icons, $icons_codes, $icons_tags);

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main","forum_topic.html");

	$html_title = ""; $meta_description = ""; $meta_keywords = ""; 
	$t->set_var("current_href", get_custom_friendly_url("forum_topic.php"));
	include_once("./header.php");
	if (is_array($page_settings)) {
		foreach($page_settings as $setting_name => $setting_value)
		{
			if ($setting_name == "top_products_block") {
				include_once("./blocks/block_top_products.php");
				top_products($setting_value);
			} elseif ($setting_name == "products_top_sellers") {
				include_once("./blocks/block_products_top_sellers.php");
				products_top_sellers($setting_value);
			} elseif ($setting_name == "search_block") {
				include_once("./blocks/block_search.php");
				search_form($setting_value);
			} elseif ($setting_name == "products_recently_viewed") {
				include_once("./blocks/block_products_recently.php");
				products_recently_viewed($setting_value);
			} elseif ($setting_name == "products_latest") {
				include_once("./blocks/block_products_latest.php");
				products_latest($setting_value);
			} elseif ($setting_name == "products_top_viewed") {
				include_once("./blocks/block_products_top_viewed.php");
				products_top_viewed($setting_value);
			} elseif ($setting_name == "login_block") {
				login_form($setting_value);
			} elseif ($setting_name == "cart_block") {
				include_once("./blocks/block_cart.php");
				small_cart($setting_value);
			} elseif ($setting_name == "subscribe_block") {
				include_once("./blocks/block_subscribe.php");
				subscribe_form($setting_value);
			} elseif ($setting_name == "sms_test_block") {
				include_once("./blocks/block_sms_test.php");
				sms_test_form($setting_value);
			} elseif ($setting_name == "poll_block") {
				include_once("./blocks/block_poll.php");
				poll_form($setting_value);
			} elseif ($setting_name == "language_block") {
				include_once("./blocks/block_language.php");
				language_form($setting_value, $page_settings["language_selection"], "", $page_friendly_url, $page_friendly_params);
			} elseif ($setting_name == "currency_block") {
				include_once("./blocks/block_currency.php");
				currency_form($setting_value);
			} elseif ($setting_name == "layouts_block") {
				include_once("./blocks/block_layouts.php");
				layouts($setting_value, "", $page_friendly_url, $page_friendly_params);
			} elseif ($setting_name == "site_search_form") {
				include_once("./blocks/block_site_search_form.php");
				site_search_form($setting_value);
			} elseif ($setting_name == "forum_search_block") {
				include_once("./blocks/block_forum_search.php");
				forum_search($setting_value);
			} elseif ($setting_name == "forum_latest") {
				include_once("./blocks/block_forum_latest.php");
				forum_latest($setting_value);
			} elseif ($setting_name == "forum_top_viewed") {
				include_once("./blocks/block_forum_top_viewed.php");
				forum_top_viewed($setting_value);
			} elseif ($setting_name == "forum_breadcrumb") {
				include_once("./blocks/block_forum_breadcrumb.php");
				forum_breadcrumb($setting_value);
			} elseif ($setting_name == "forum_description") {
				include_once("./blocks/block_forum_description.php");
				forum_description($setting_value, $forum_id, $forum_name, $forum_description, $forum_image);
			} elseif ($setting_name == "forum_view_topic") {
				include_once("./blocks/block_forum_topic.php");
				forum_show_topic($setting_value, $forum_id, $allowed_post_replies, $page_friendly_url, $page_friendly_params);
			} elseif (preg_match("/^navigation_block_(\d+)$/", $setting_name, $matches)) {
				include_once("./blocks/block_navigation.php");
				navigation_menu($setting_value, $matches[1]);
			} elseif (preg_match("/^custom_block_/", $setting_name)) {
				custom_block($setting_value, substr($setting_name, 13));
			} elseif (preg_match("/^banners_group_/", $setting_name)) {
				banners_group($setting_value, substr($setting_name, 14));
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

	$t->set_var("html_title", $html_title);
	$t->set_var("meta_keywords", $meta_keywords);
	$t->set_var("meta_description", get_meta_desc($meta_description));
	$t->pparse("main");
	
?>