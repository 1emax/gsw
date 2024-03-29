<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  article_print.php                                        ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/
                           

	include_once ("./includes/common.php");

	$user_id = get_session("session_user_id");		
	$user_info = get_session("session_user_info");
	$user_type_id = get_setting_value($user_info, "user_type_id", "");
	
	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "article_print.html");

	$t->set_var("PRINT_PAGE_MSG", PRINT_PAGE_MSG);

	$t->set_var("CHARSET", CHARSET);
	$t->set_var("MORE_MSG",          MORE_MSG);
	$t->set_var("READ_MORE_MSG",     READ_MORE_MSG);
	$t->set_var("CLICK_HERE_MSG",    CLICK_HERE_MSG);
	$t->set_var("LINK_URL_MSG",      LINK_URL_MSG);
	$t->set_var("DOWNLOAD_URL_MSG",  DOWNLOAD_URL_MSG);
	$t->set_var("NOTES_MSG",         NOTES_MSG);
	$t->set_var("KEYWORDS_MSG",      KEYWORDS_MSG);

	$currency = get_currency();
	$article_id = get_param("article_id");
	if (strlen($article_id) && @!strlen($category_id)) {
		$sql  = " SELECT category_id FROM " . $table_prefix . "articles_assigned ";
		$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
		}
	}

	$sql  = " SELECT ac.parent_category_id, ac.category_path, ac.article_details_fields ";
	$sql .= " FROM ";
	if (isset($site_id)) {
		$sql .= "(";
	}
	if (strlen($user_id)) {
		$sql .= "(";
	}		
	$sql .=  $table_prefix . "articles_categories ac";	
	if (isset($site_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_sites acs ON acs.category_id=ac.category_id)";
	}
	if (strlen($user_id)) {
		$sql .= " LEFT JOIN " . $table_prefix . "articles_categories_types ut ON ut.category_id=ac.category_id)";
	}
	$sql .= " WHERE ac.category_id = " . $db->tosql($category_id, INTEGER);
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
		$parent_category_id = $db->f("parent_category_id");
		$category_path = $db->f("category_path");
		if ($parent_category_id == 0) {
			$top_id = $category_id;
			$details_fields = $db->f("article_details_fields");
		} else {
			$categories_ids = explode(",", $category_path);
			$top_id = $categories_ids[1];
			$sql  = " SELECT article_details_fields ";
			$sql .= " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($top_id, INTEGER);
			$db->query($sql);
			if ($db->next_record()) {
				$details_fields = $db->f("article_details_fields");
			}
		}
	} else {
		header ("Location: " . get_custom_friendly_url("user_login.php") . "?type_error=2");
		exit;
	}

	$details_fields = ",," . $details_fields . ",,";

	$article_fields = array(
		"author_name", "author_email", "author_url", "link_url", "download_url", 
		"short_description", "full_description", "keywords", "notes"
	);

	$sql  = " SELECT a.article_id, a.article_title, a.article_date, a.date_end, ";
	$sql .= " a.author_name, a.author_email, a.author_url, a.link_url, a.download_url, ";
	$sql .= " a.short_description, a.is_html, a.full_description, a.image_small, a.image_large, ";
	$sql .= " a.total_votes, a.total_points, a.allowed_rate, ";
	$sql .= " a.keywords, a.notes ";
	$sql .= " FROM " . $table_prefix . "articles a, " . $table_prefix . "articles_statuses st ";
	$sql .= " WHERE a.status_id=st.status_id";
	$sql .= " AND a.article_id= " . $db->tosql($article_id, INTEGER);
	$sql .= " AND st.allowed_view=1 ";
	$db->query($sql);
	if ($db->next_record())
	{
		$article_id = $db->f("article_id");
		$article_title = get_translation($db->f("article_title"));
		$short_description = get_translation($db->f("short_description"));
		$full_description = get_translation($db->f("full_description"));
		$allowed_rate = $db->f("allowed_rate");

		if (!$full_description) { $full_description = $short_description; }
		if (strlen($short_description)) {
			$meta_description = $short_description;
		} else if (strlen($full_description)) {
			$meta_description = $full_description;
		} else {
			$meta_description = $article_title;
		}
		$t->set_var("meta_description", get_meta_desc($meta_description));

		$t->set_var("article_id", $article_id);
		$t->set_var("article_name", $article_title);
		$t->set_var("article_title", $article_title);

		// get fields values
		$article_date_string = ""; $date_end_string = "";
		if (strpos($details_fields, ",article_date,")) {
			$article_date = $db->f("article_date", DATETIME);
			$article_date_string  = va_date($datetime_show_format, $article_date);
			$t->set_var("article_date", $article_date_string);
			$t->global_parse("article_date_block", false, false, true);
		} else {
			$t->set_var("article_date_block", "");
		}
		if (strpos($details_fields, ",date_end,")) {
			$date_end = $db->f("date_end", DATETIME);
			$date_end_string = va_date($datetime_show_format, $date_end);
			$t->set_var("date_end", $date_end_string);
			$t->global_parse("date_end_block", false, false, true);
		} else {
			$t->set_var("date_end_block", "");
		}
		if (strlen($article_date_string) || strlen($date_end_string)) {
			$t->global_parse("date_block", false, false, true);
		}

		for ($i = 0; $i < sizeof($article_fields); $i++) {
			$field_name = $article_fields[$i];
			$fields[$field_name] = get_translation($db->f($field_name));
			if (strlen($fields[$field_name]) && strpos($details_fields, "," . $field_name . ",")) {
				$t->set_var($field_name, $fields[$field_name]);
				$t->global_parse($field_name . "_block", false, false, true);
			} else {
				$fields[$field_name] = "";
				$t->set_var($field_name, "");
				$t->set_var($field_name . "_block", "");
			}
		}

		if (strlen($fields["author_name"]) || strlen($fields["author_email"]) || strlen($fields["author_url"])) {
			$t->global_parse("author_block", false, false, true);
		} else {
			$t->set_var("author_block", false);
		}

		if (strpos($details_fields, ",full_description,")) {
			if ($db->f("is_html") != 1) {
				$full_description = nl2br(htmlspecialchars($full_description));
			}
			$t->set_var("full_description", $full_description);
		} else {
			$t->set_var("full_description", "");
		}

		$image_small = $db->f("image_small");
		if (strpos($details_fields, ",image_small,") && strlen($image_small)) {
			$image_size = preg_match("/^http\:\/\//", $image_small) ? "" : @GetImageSize($image_small);
			$t->set_var("alt", htmlspecialchars($article_title));
			$t->set_var("src", htmlspecialchars($image_small));
			if (is_array($image_size)) {
				$t->set_var("width", "width=\"" . $image_size[0] . "\"");
				$t->set_var("height", "height=\"" . $image_size[1] . "\"");
			} else {
				$t->set_var("width", "");
				$t->set_var("height", "");
			}
			$t->parse("image_small_block", false);
		} else {
			$t->set_var("image_small_block", "");
		}

		$image_large = $db->f("image_large");
		if (strpos($details_fields, ",image_large,") && strlen($image_large)) {
			$image_size = preg_match("/^http\:\/\//", $image_large) ? "" : @GetImageSize($image_large);
			$t->set_var("alt", htmlspecialchars($article_title));
			$t->set_var("src", htmlspecialchars($image_large));
			if (is_array($image_size)) {
				$t->set_var("width", "width=\"" . $image_size[0] . "\"");
				$t->set_var("height", "height=\"" . $image_size[1] . "\"");
			} else {
				$t->set_var("width", "");
				$t->set_var("height", "");
			}
			$t->parse("image_large_block", false);
		} else {
			$t->set_var("image_large_block", "");
		}
			
		$t->parse("item");
		$t->set_var("no_item", "");

	}
	else
	{
		$t->set_var("item", "");
		$t->set_var("NO_ARTICLE_MSG", NO_ARTICLE_MSG);
		$t->parse("no_item", false);
	}

	$t->pparse("main", false);	
?>