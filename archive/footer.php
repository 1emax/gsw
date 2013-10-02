<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  footer.php                                               ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	$friendly_urls = get_setting_value($settings, "friendly_urls", 0);
	$friendly_extension = get_setting_value($settings, "friendly_extension", "");

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");
	
	$t->templates_path = "./js";
	$t->set_file("footer_js", "footer.js");
	$t->parse("footer_js", false);

	$t->templates_path = $settings["templates_dir"];
	$t->set_file("footer", "footer.html");
	$t->set_var("site_url", $settings["site_url"]);

	$t->set_var("index_href", get_custom_friendly_url("index.php"));
	$t->set_var("products_href", get_custom_friendly_url("products.php"));
	$t->set_var("basket_href", get_custom_friendly_url("basket.php"));
	$t->set_var("user_profile_href", get_custom_friendly_url("user_profile.php"));
	$t->set_var("admin_href", "admin.php");
	$sql  = " SELECT p.page_type, p.page_title, p.page_code, p.page_url, p.friendly_url ";
	$sql .= " FROM ";
	if (isset($site_id)) {
		$sql .= "(";
	}
	if (strlen($user_id)) {
		$sql .= "(";
	}
	$sql .= $table_prefix . "pages p ";
	if (isset($site_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "pages_sites ps ON ps.page_id=p.page_id)";
	}
	if (strlen($user_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "pages_user_types ut ON ut.page_id=p.page_id)";
	}
	$sql .= " WHERE p.is_showing = 1 AND p.link_in_footer = 1 ";
	if (isset($site_id)) {
		$sql .=  " AND (p.sites_all=1 OR ps.site_id=". $db->tosql($site_id, INTEGER, true, false) . ") ";
	} else {
		$sql .=  " AND p.sites_all=1 ";					
	}		
	if (strlen($user_id)) {
		$sql .=  " AND ( p.user_types_all=1 OR ut.user_type_id=". $db->tosql($user_type_id , INTEGER) . " )";
	} else {
		$sql .=  " AND p.user_types_all=1 ";
	}
	if ($db_type == "access" || $db_type == "db2"  || $db_type == "postgre") {
		$sql .= " GROUP BY p.page_id, p.page_type, p.page_title, p.page_code, p.page_url, p.friendly_url, p.page_order ";
	} else {
		$sql .= " GROUP BY p.page_id ";
	}
	$sql .= " ORDER BY p.page_order ";
	$db->query($sql);
	if ($db->next_record()) {
		$t->set_var("page_separator", "");
		do {
			$page_url = $db->f("page_url");
			$friendly_url = $db->f("friendly_url");
			$page_title = get_translation($db->f("page_title"));
			if ($friendly_urls && strlen($friendly_url)) {
				$page_url = $friendly_url . $friendly_extension;
			} else if (!strlen($page_url)) {
				$page_url = get_custom_friendly_url("page.php") . "?page=" . urlencode($db->f("page_code"));
			}
			$popup_js = ($db->f("page_type") == 2) ? "return openPopup('" . $page_url . "', 600, 450);" : "";
			$t->set_var("popup_js", $popup_js);
			$t->set_var("custom_page_title", htmlspecialchars($page_title));
			$t->set_var("custom_page_href", $page_url);
			$t->sparse("custom_pages");
			$t->sparse("page_separator", false);
		} while($db->next_record());
	} else {
		$t->set_var("custom_pages", "");
	}
	if ($settings["html_below_footer"]) {
		$html_below_footer = get_translation($settings["html_below_footer"]);
		if (get_setting_value($settings, "php_in_footer_body", 0)) {
			eval_php_code($html_below_footer);
		}
		$t->set_block("footer_html", $html_below_footer);
		$t->parse("footer_html", false);

		if ($t->block_exists("footer_block")) {
			$t->parse("footer_block", false);
		}
	} else {
		$t->set_var("footer_block", "");
	}

	$google_analytics = get_setting_value($settings, "google_analytics", 0);
	$google_tracking_code = get_setting_value($settings, "google_tracking_code", "");
	if ($google_analytics && $google_tracking_code) {
		if ($is_ssl) {
			$google_analytics_js = "https://ssl.google-analytics.com/urchin.js";
		} else {
			$google_analytics_js = "http://www.google-analytics.com/urchin.js";
		}
		$t->set_var("google_analytics_js", $google_analytics_js);
		$t->set_var("google_tracking_code", $google_tracking_code);
		$t->sparse("google_analytics", false);
	}

	if (isset($debug_mode) && $debug_mode) {
		$t->set_var("debug_buffer", $debug_buffer);
	}
	$t->parse("footer");
	$t->set_var("footer", get_currency_message($t->get_var("footer"), $currency));

?>