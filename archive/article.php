<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  article.php                                              ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/navigator.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	// include blocks
	include_once("./blocks/block_articles_categories.php");
	include_once("./blocks/block_articles_breadcrumb.php");
	include_once("./blocks/block_articles_details.php");
	include_once("./blocks/block_articles_related.php");
	include_once("./blocks/block_articles_category.php");
	include_once("./blocks/block_articles_latest.php");
	include_once("./blocks/block_articles_top_rated.php");
	include_once("./blocks/block_articles_top_viewed.php");
	include_once("./blocks/block_articles_hot.php");
	include_once("./blocks/block_articles_content.php");
	include_once("./blocks/block_articles_search.php");
	include_once("./blocks/block_custom.php");
	include_once("./blocks/block_banners.php");
	include_once("./blocks/block_poll.php");

	
	include_once("./includes/shopping_cart.php");
	$tax_rates = get_tax_rates();
	

	$current_page = "article.php";
	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) {
		$category_id = $search_category_id;
	}

	$article_id = get_param("article_id");
	if (strlen($article_id) && !strlen($category_id)) {
		// at least one category should be availiable
		$sql  = " SELECT aac.category_id ";
		$sql .= " FROM (";
		if (isset($site_id)) {
			$sql .= "(";
		}
		if (strlen($user_id)) {
			$sql .= "(";
		}
		$sql .= $table_prefix . "articles_assigned aac ";
		$sql .= " LEFT JOIN " . $table_prefix . "articles_categories ac ON ac.category_id=aac.category_id)";
		if (isset($site_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_sites acs ON acs.category_id=ac.category_id)";
		}
		if (strlen($user_id)) {
			$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_types ut ON ut.category_id=ac.category_id)";
		}
		$sql .= " WHERE aac.article_id=" . $db->tosql($article_id, INTEGER);		
		if (isset($site_id)) {
			$sql .= " AND (ac.sites_all=1 OR acs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
		} else {
			$sql .= " AND ac.sites_all=1 ";					
		}		
		if (strlen($user_id)) {
			$sql .= " AND ( ac.user_types_all=1 OR ut.user_type_id=". $db->tosql($user_type_id , INTEGER) . " )";
		} else {
			$sql .= " AND ac.user_types_all=1 ";
		}
		
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
		} else {
			header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
			exit;
		}
	}

	$page_friendly_url = "";
	$page_friendly_params = array("article_id");
	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	if ($friendly_urls) {
		// retrieve info about friendly url
		$sql  = " SELECT friendly_url FROM " . $table_prefix . "articles WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$page_friendly_url = $db->f("friendly_url");
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
	}

	// retrieve info about current category
	$sql  = " SELECT ac.category_name,ac.short_description,ac.full_description, ac.category_path, ac.parent_category_id, ";
	$sql .= " ac.articles_details_template, ac.articles_order_column,ac.articles_order_direction, ac.article_details_fields, ";
	$sql .= " ac.article_list_fields, ac.image_small, ac.image_small_alt, ac.image_large, ac.image_large_alt, ";
	$sql .= " ac.is_rss, ac.rss_on_breadcrumb ";
	$sql .= " FROM ";
	if (isset($site_id)) {
		$sql .= "(";
	}
	if (strlen($user_id)) {
		$sql .= "(";
	}				
	$sql .=  $table_prefix . "articles_categories ac ";
	if (isset($site_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_sites acs ON acs.category_id=ac.category_id)";
	}
	if (strlen($user_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_types ut ON ut.category_id=ac.category_id)";
	}
	$sql .= " WHERE ac.category_id = " . $db->tosql($category_id, INTEGER, true, false);	
	if (isset($site_id)) {
		$sql .= " AND (ac.sites_all=1 OR acs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
	} else {
		$sql .= " AND ac.sites_all=1 ";					
	}	
	if (strlen($user_id)) {
		$sql .= " AND ( ac.user_types_all=1 OR ut.user_type_id=". $db->tosql($user_type_id , INTEGER) . " )";
	} else {
		$sql .= " AND ac.user_types_all=1 ";
	}
	
	$db->query($sql);
	if ($db->next_record()) {
		$current_category = get_translation($db->f("category_name"));
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		$image_small = $db->f("image_small");
		$image_small_alt = $db->f("image_small_alt");
		$image_large = $db->f("image_large");
		$image_large_alt = $db->f("image_large_alt");
		$parent_category_id = $db->f("parent_category_id");
		$category_path = $db->f("category_path");
		if ($db->f("is_rss") && $db->f("rss_on_breadcrumb")){
			$rss_on_breadcrumb = true;
		} else {
			$rss_on_breadcrumb = false;
		}
		if ($parent_category_id == 0) {
			$top_id = $category_id;
			$top_name = $current_category;
			$details_template = $db->f("articles_details_template");
			$articles_order_column = $db->f("articles_order_column");
			$articles_order_direction = $db->f("articles_order_direction");
			$list_fields = $db->f("article_list_fields");
			$details_fields = $db->f("article_details_fields");
		} else {
			$categories_ids = explode(",", $category_path);
			$top_id = $categories_ids[1];
			$sql  = " SELECT category_name, articles_details_template, articles_order_column,articles_order_direction, article_list_fields, article_details_fields ";
			$sql .= " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$top_name = get_translation($db->f("category_name"));
				$details_template = $db->f("articles_details_template");
				$articles_order_column = $db->f("articles_order_column");
				$articles_order_direction = $db->f("articles_order_direction");
				$list_fields = $db->f("article_list_fields");
				$details_fields = $db->f("article_details_fields");
			}
		}

	} else {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}

	$page_name = "a_details_" . $top_id;
	$page_settings = va_page_settings($page_name, 0);
	$desc_image = get_setting_value($page_settings, "a_cat_desc_image_" . $top_id, 3);
	$desc_type = get_setting_value($page_settings, "a_cat_desc_type_" . $top_id, 2);
	$category_image = ""; $category_image_alt = "";
	if ($desc_image == 3) {
		$category_image = $image_large;
		$category_image_alt = $image_large_alt;
	} elseif ($desc_image == 2) {
		$category_image = $image_small;
		$category_image_alt = $image_small_alt;
	}
	$category_description = "";
	if ($desc_type == 2) {
		$category_description = $full_description;
	} elseif ($desc_type == 1) {
		$category_description = $short_description;
	}

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main","article.html");
	$t->set_var("current_href", "article.php");

	include_once("./header.php");

	$html_title = ""; $meta_keywords = ""; $meta_description = "";
	if (is_array($page_settings)) {
		foreach ($page_settings as $setting_name => $setting_value)
		{
			if (preg_match("/^a_details_(\d+)$/", $setting_name, $matches)) {
				articles_details($setting_value, $article_id, $category_id, $details_fields, $details_template);
			} elseif (preg_match("/^a_related_(\d+)$/", $setting_name, $matches)) {
				articles_related($setting_value, $article_id, $list_fields);
			} elseif (preg_match("/^a_item_related_(\d+)$/", $setting_name, $matches)) {
				include_once("./blocks/block_products_related.php");
				related_products($setting_value, "article_items_related", $page_friendly_url, $page_friendly_params);
			} elseif (preg_match("/^a_cat_item_related_(\d+)$/", $setting_name, $matches)) {
				include_once("./blocks/block_products_related.php");
				related_products($setting_value, "article_category_items_related", $page_friendly_url, $page_friendly_params);
			} elseif (preg_match("/^a_cats_(\d+)$/", $setting_name, $matches)) {
				articles_categories($setting_value, $matches[1], $top_name, "a_cats", $category_id);
			} elseif (preg_match("/^a_subcats_(\d+)$/", $setting_name, $matches)) {
				articles_categories($setting_value, $matches[1], $top_name, "a_subcats", $category_id);
			} elseif (preg_match("/^a_breadcrumb_(\d+)$/", $setting_name, $matches)) {
				articles_breadcrumb($setting_value, $matches[1], $rss_on_breadcrumb);
			} elseif (preg_match("/^a_cat_desc_(\d+)$/", $setting_name, $matches)) {
				articles_category($setting_value, $category_id, $current_category, $category_description, $category_image, $category_image_alt);
			} elseif (preg_match("/^a_latest_(\d+)$/", $setting_name, $matches)) {
				articles_latest($setting_value, $matches[1], $top_name);
			} elseif (preg_match("/^a_top_rated_(\d+)$/", $setting_name, $matches)) {
				articles_top_rated($setting_value, $matches[1], $top_name);
			} elseif (preg_match("/^a_top_viewed_(\d+)$/", $setting_name, $matches)) {
				articles_top_viewed($setting_value, $matches[1], $top_name);
			} elseif (preg_match("/^a_hot_(\d+)$/", $setting_name, $matches)) {
				articles_hot($setting_value, $matches[1], $top_name, $list_fields, $articles_order_column, $articles_order_direction, $category_id, $page_friendly_url, $page_friendly_params);
			} elseif (preg_match("/^a_content_(\d+)$/", $setting_name, $matches)) {
				articles_content($setting_value, $matches[1], $category_id, $current_category, $articles_order_column, $articles_order_direction);
			} elseif (preg_match("/^a_search_(\d+)$/", $setting_name, $matches)) {
				articles_search($setting_value, $matches[1], $top_name, $category_id);
			} elseif (preg_match("/^navigation_block_(\d+)$/", $setting_name, $matches)) {
				include_once("./blocks/block_navigation.php");
				navigation_menu($setting_value, $matches[1]);
			} elseif ($setting_name == "cart_block") {
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