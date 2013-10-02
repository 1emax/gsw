<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  products.php                                             ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	$type = "list";
	include_once("./includes/common.php");
	include_once("./messages/" . $language_code . "/cart_messages.php");
	include_once("./messages/" . $language_code . "/reviews_messages.php");
	include_once("./messages/" . $language_code . "/download_messages.php");
	include_once("./includes/sorter.php");
	include_once("./includes/navigator.php");
	include_once("./includes/items_properties.php");
	include_once("./includes/shopping_cart.php");
	include_once("./includes/filter_functions.php");

	// include blocks
	include_once("./blocks/block_custom.php");
	include_once("./blocks/block_banners.php");

	$display_products = get_setting_value($settings, "display_products", 0);
	if ($display_products == 1) {
		// user need to be logged in before viewing products
		check_user_session();
	}
	
	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");

	$current_page = get_custom_friendly_url("products.php");
	$confirm_add = get_setting_value($settings, "confirm_add", 1);
	$page_settings = va_page_settings("products_list", 0);
	$tax_rates = get_tax_rates();
	$category_id = get_param("category_id");
	$search_category_id = get_param("search_category_id");
	if (strlen($search_category_id)) { $category_id = $search_category_id; }
	elseif (!strlen($category_id)) { $category_id = "0"; }
	$manf = get_param("manf");

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main","products.html");
	$t->set_var("current_href", get_custom_friendly_url("products.php"));
	$t->set_var("confirm_add", $confirm_add);

	include_once("./header.php");

	$list_template = ""; $html_title = ""; $current_category = ""; $meta_description = ""; $meta_keywords = ""; 
	$page_friendly_url = ""; $page_friendly_params = array();
	$show_sub_products = false; $category_path = "";

	// retrieve info about current category

	$sql  = " SELECT * FROM ";
	if (isset($site_id)) {
		$sql .= "(";
	}
	if (strlen($user_id)) {
		$sql .= "(";
	}
	$sql .= $table_prefix . "categories c";
	if (isset($site_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "categories_sites cs ON cs.category_id=c.category_id)";
	}
	if (strlen($user_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "categories_user_types ut ON ut.category_id=c.category_id)";
	}
	if (isset($site_id)) {
		$sql .= " WHERE (c.sites_all=1 OR cs.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
	} else {
		$sql .= " WHERE c.sites_all=1 ";					
	}
	if (strlen($user_id)) {
		$sql .= " AND (c.user_types_all=1 OR ut.user_type_id=". $db->tosql($user_type_id , INTEGER) . " )";
	} else {
		$sql .= " AND c.user_types_all=1 ";
	}
	$sql .= " AND c.category_id=" . $db->tosql($category_id, INTEGER);
	
	$db->query($sql);
	if ($db->next_record()) {
		$current_category = get_translation($db->f("category_name"));
		$page_friendly_url = $db->f("friendly_url");
		if ($page_friendly_url) {
			$page_friendly_params[] = "category_id";
			friendly_url_redirect($page_friendly_url, $page_friendly_params);
		}
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		
		$show_sub_products = $db->f("show_sub_products");
		$category_path = $db->f("category_path") . $category_id . ",";

		$list_template = $db->f("list_template");
        $cur_title = get_translation($db->f("page_title"));
		$html_title = get_translation($db->f("meta_title"));
		$meta_description = get_translation($db->f("meta_description"));
		$meta_keywords = get_translation($db->f("meta_keywords"));
		$total_views = $db->f("total_views");

		if (!strlen($html_title)) {
			$html_title = $current_category;
		}
		if (!strlen($meta_description)) {
			if (strlen($short_description)) {
				$meta_description = $short_description;
			} elseif (strlen($full_description)) {
				$meta_description = $full_description;
			} else {
				$meta_description = PRODUCTS_TITLE;
			}		
		}
		
		if (isset ($_GET['page']))
		{
			if ($_GET['page']>1)
			{           
				$full_description='';
			}
		}
		
		
		// update total views for categories
		$products_cats_viewed = get_session("session_products_cats_viewed");
		if (!isset($products_cats_viewed[$category_id])) {
			$sql  = " UPDATE " . $table_prefix . "categories SET total_views=" . $db->tosql(($total_views + 1), INTEGER);
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
			$db->query($sql);

			$products_cats_viewed[$category_id] = true;
			set_session("session_products_cats_viewed", $products_cats_viewed);
		}
	} elseif (strlen($manf)) {
		$sql = "SELECT manufacturer_name, friendly_url FROM " . $table_prefix . "manufacturers WHERE manufacturer_id=" . $db->tosql($manf, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$manufacturer_name = $db->f("manufacturer_name");
			$manf_friendly_url = $db->f("friendly_url");
			if (!$page_friendly_url && $manf_friendly_url) {
				$page_friendly_url = $manf_friendly_url;
				$page_friendly_params[] = "manf";
				friendly_url_redirect($page_friendly_url, $page_friendly_params);
			}

			$current_category  = $manufacturer_name;
			$list_template     = "block_products_list.html";
			$html_title        = $manufacturer_name;
		}
	} elseif ($category_id) {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	} else {
		$current_category = PRODUCTS_TITLE;
		$list_template    = "block_products_list.html";
		$html_title       = get_translation(get_setting_value($settings, "products_title", PRODUCTS_TITLE));
		$meta_keywords = get_translation(get_setting_value($settings, "products_keywords"));
		$meta_description = get_translation(get_setting_value($settings, "products_description"));
	}

	if (is_array($page_settings)) {
		foreach($page_settings as $setting_name => $setting_value)
		{
			if ($setting_name == "top_products_block") {
				include_once("./blocks/block_products_top_rated.php");
				top_products($setting_value);
			} elseif ($setting_name == "offers_block") {
				include_once("./blocks/block_offers.php");
				offers($setting_value, $page_friendly_url, $page_friendly_params);
			} elseif ($setting_name == "products_top_sellers") {
				include_once("./blocks/block_products_top_sellers.php");
				products_top_sellers($setting_value);
			} elseif ($setting_name == "products_latest") {
				include_once("./blocks/block_products_latest.php");
				products_latest($setting_value);
			} elseif ($setting_name == "products_top_viewed") {
				include_once("./blocks/block_products_top_viewed.php");
				products_top_viewed($setting_value);
			} elseif ($setting_name == "search_block") {
				include_once("./blocks/block_search.php");
				search_form($setting_value);
			} elseif ($setting_name == "products_recently_viewed") {
				include_once("./blocks/block_products_recently.php");
				products_recently_viewed($setting_value);
			} elseif ($setting_name == "products_recommended") {
				include_once("./blocks/block_products_recommended.php");
				products_recommended($setting_value);
			} elseif ($setting_name == "login_block") {
				login_form($setting_value);
			} elseif ($setting_name == "cart_block") {
				include_once("./blocks/block_cart.php");
				small_cart($setting_value);
			} elseif ($setting_name == "coupon_form") {
				include_once("./blocks/block_coupon_form.php");
				coupon_form($setting_value);
			} elseif ($setting_name == "products_breadcrumb") {
				include_once("./blocks/block_products_breadcrumb.php");
				products_breadcrumb($setting_value);
			} elseif ($setting_name == "categories_block") {
				include_once("./blocks/block_categories_list.php");
				categories($setting_value, "categories");
			} elseif ($setting_name == "subcategories_block") {
				include_once("./blocks/block_categories_list.php");
				categories($setting_value, "subcategories");
			} elseif ($setting_name == "manufacturers_block") {
				include_once("./blocks/block_manufacturers.php");
				manufacturers($setting_value);
			} elseif ($setting_name == "manufacturer_info_block") {
				include_once("./blocks/block_manufacturer_info.php");
				manufacturer_info($setting_value);
			} elseif ($setting_name == "merchants_block") {
				include_once("./blocks/block_merchants.php");
				merchants($setting_value);
			} elseif ($setting_name == "category_description_block") {
				include_once("./blocks/block_category_description.php");
				category_description($setting_value);
			} elseif ($setting_name == "products_block") {
				include_once("./blocks/block_products_list.php");
				products_list($setting_value, $list_template, $current_category, $page_friendly_url, $page_friendly_params, $show_sub_products, $category_path);
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
			} elseif (preg_match("/^filter_/", $setting_name)) {
				include_once("./blocks/block_filter.php");
				filter_block($setting_value, substr($setting_name, 7), $page_friendly_url, $page_friendly_params, $show_sub_products, $category_path);
			} elseif (preg_match("/^custom_block_/", $setting_name)) {
				custom_block($setting_value, substr($setting_name, 13));
			} elseif (preg_match("/^banners_group_/", $setting_name)) {
				banners_group($setting_value, substr($setting_name, 14));
			} elseif (preg_match("/^navigation_block_(\d+)$/", $setting_name, $matches)) {
				include_once("./blocks/block_navigation.php");
				navigation_menu($setting_value, $matches[1]);
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

	prepare_saved_types();

	include_once("./footer.php");
    if (isset ($_GET['page']))
    {

        if ($_GET['page']>1)
        {           
            $html_title='Страница '.$_GET['page']." &mdash; ".$html_title;
            $meta_description='';
			$full_description='';
        }
    }
	$t->set_var("current_category", $current_category);
	
            $t->set_var("html_title", $html_title);
        
	$t->set_var("meta_keywords", $meta_keywords);
	$t->set_var("meta_description", get_meta_desc($meta_description));
	$t->pparse("main");

?>