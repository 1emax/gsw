<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  user_select.php                                          ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once($root_folder_path . "includes/common.php");
	include_once($root_folder_path . "includes/sorter.php");
	include_once($root_folder_path . "includes/navigator.php");
	include_once($root_folder_path . "messages/" . $language_code . "/download_messages.php");

	check_user_session();

	$show_preview_image = get_setting_value($settings, "show_preview_image_client", 0);

	$t = new VA_Template($settings["templates_dir"]);
	$t->set_file("main", "user_select.html");
	$css_file = "";
	if (isset($settings["style_name"]) && $settings["style_name"]) {
		$css_file = "styles/" . $settings["style_name"];
		if (isset($settings["scheme_name"]) && $settings["scheme_name"]) {
			$css_file .= "_" . $settings["scheme_name"];
		}
		$css_file .= ".css";
	}
	$t->set_var("css_file", $css_file);

	$t->set_var("user_upload_href", "user_upload.php");
	$t->set_var("user_select_href", "user_select.php");

	$id = get_param("id");
	$operation = get_param("operation");
	$filetype = get_param("filetype");
	$control_name = get_param("control_name");
	$fid = get_param("fid");
	$control_name = get_param("control_name");
	$search_file = get_param("sf");


	if ($operation == "delete" && strlen($id)) {
		// get product settings
		$product_settings = array();
		$setting_type = "user_product_" . get_session("session_user_type_id");
		$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type=" . $db->tosql($setting_type, TEXT);
		$sql .= " AND (setting_name='show_small_image' OR setting_name='show_big_image') ";
		if (isset($site_id)) {
			$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			$sql .= " ORDER BY site_id ASC ";
		} else {
			$sql .= " AND site_id=1 ";
		}
		$db->query($sql);
		while($db->next_record()) {
			$product_settings[$db->f("setting_name")] = $db->f("setting_value");
		}
		$generate_small_image = get_setting_value($settings, "user_generate_small_image", 0);
		$generate_large_image = get_setting_value($settings, "user_generate_large_image", 0);
		$show_small_image = get_setting_value($product_settings, "show_small_image", 0);
		$show_big_image = get_setting_value($product_settings, "show_big_image", 0);

		$sql  = " SELECT file_id, file_name, file_path FROM " . $table_prefix . "users_files ";
		$sql .= " WHERE user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
		$sql .= " AND file_type=" . $db->tosql($filetype, TEXT);
		$sql .= " AND file_id=" . $db->tosql($id, INTEGER);
		$db->query($sql);
		if ($db->next_record()) {
			$file_path = $db->f("file_path");
			// delete from disk
			$file_deleted = @unlink($file_path);
			if ($file_deleted) {
				$db->query(" DELETE FROM " . $table_prefix . "users_files WHERE file_id=" . $db->tosql($id, INTEGER));
			}
			// check if there were generated smaller images
			if ($filetype == "product_large" || $filetype == "product_super") {
				if (!$show_small_image && $generate_small_image) {
					if ($filetype == "product_large") {
						$small_file_path = str_replace("images/products/large/", "images/products/small/", $file_path);
					} else {
						$small_file_path = str_replace("images/products/super/", "images/products/small/", $file_path);
					}
					$sql  = " SELECT file_id FROM " . $table_prefix . "users_files ";
					$sql .= " WHERE user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
					$sql .= " AND file_type=" . $db->tosql("product_small", TEXT);
					$sql .= " AND file_path=" . $db->tosql($small_file_path, TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$small_file_id = $db->f("file_id");
						$small_file_deleted = @unlink($small_file_path);
						if ($small_file_deleted) {
							$db->query(" DELETE FROM " . $table_prefix . "users_files WHERE file_id=" . $db->tosql($small_file_id, INTEGER));
						}
					}
				}
				if (!$show_big_image && $generate_large_image) {
					$large_file_path = str_replace("images/products/super/", "images/products/large/", $file_path);
					$sql  = " SELECT file_id FROM " . $table_prefix . "users_files ";
					$sql .= " WHERE user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
					$sql .= " AND file_type=" . $db->tosql("product_large", TEXT);
					$sql .= " AND file_path=" . $db->tosql($large_file_path, TEXT);
					$db->query($sql);
					if ($db->next_record()) {
						$large_file_id = $db->f("file_id");
						$large_file_deleted = @unlink($large_file_path);
						if ($large_file_deleted) {
							$db->query(" DELETE FROM " . $table_prefix . "users_files WHERE file_id=" . $db->tosql($large_file_id, INTEGER));
						}
					}
				}
			}
		}
	}

	$downloads_dir = "";
	if ($filetype == "downloads") {
		$download_info = array();
		$sql  = " SELECT setting_name,setting_value FROM " . $table_prefix . "global_settings ";
		$sql .= " WHERE setting_type='download_info'";
		if (isset($site_id)) {
			$sql .= " AND (site_id=1 OR site_id=" . $db->tosql($site_id, INTEGER, true, false) . ")";
			$sql .= " ORDER BY site_id ASC ";
		} else {
			$sql .= " AND site_id=1 ";
		}
		$db->query($sql);
		while ($db->next_record()) {
			$download_info[$db->f("setting_name")] = $db->f("setting_value");
		}
		$downloads_dir = get_setting_value($download_info, "downloads_users_dir", "./");
		if (!preg_match("/\/|\\$/", $downloads_dir)) { $downloads_dir .= "/"; }
	}

	$t->set_var("sf", $search_file);

	$s = new VA_Sorter($settings["templates_dir"], "sorter_img.html", "user_select.php");
	$s->set_parameters(false, true, true, true);
	$s->set_default_sorting("1", "asc");
	$s->set_sorter("Filename", "sorter_file_name", "1", "file_name");

	$t->set_var("filetype", $filetype);
	$t->set_var("control_name", $control_name);
	$t->set_var("fid", $fid);

	if ($filetype == "article_small") {
		$files_dir = "./images/articles/small/";
	} elseif ($filetype == "article_large") {
		$files_dir = "./images/articles/large/";
	} elseif ($filetype == "ad_small") {
		$files_dir = "./images/ads/small/";
	} elseif ($filetype == "ad_large") {
		$files_dir = "./images/ads/large/";
	} elseif ($filetype == "personal_image") {
		$files_dir = "./images/users/";
	} elseif ($filetype == "option_image") {
		$files_dir = "./images/options/";
	} elseif ($filetype == "product_small") {
		$files_dir = "./images/products/small/";
	} elseif ($filetype == "product_large") {
		$files_dir = "./images/products/large/";
	} elseif ($filetype == "product_super") {
		$files_dir = "./images/products/super/";
	} elseif ($filetype == "downloads") {
		$files_dir = $downloads_dir;
	} else {
		echo "Select file type first";
		exit;
	}

	$sql    = " SELECT COUNT(*) FROM " . $table_prefix . "users_files ";
	$where  = " WHERE user_id=" . $db->tosql(get_session("session_user_id"), INTEGER);
	$where .= " AND file_type=" . $db->tosql($filetype, TEXT);
	if (strlen($search_file)) {
		$where .= " AND file_path LIKE '%" . $db->tosql($search_file, TEXT, false, false) . "%' ";
	}
	$total_files = get_db_value($sql.$where);

	// set up variables for navigator
	$n = new VA_Navigator($settings["templates_dir"], "navigator.html", "user_select.php");
	$records_per_page = 10;
	$pages_number = 10;
	$page_number = $n->set_navigator("navigator", "page", MOVING, $pages_number, $records_per_page, $total_files, false);

	$sql  = " SELECT file_id, file_name, file_path FROM " . $table_prefix . "users_files ";
	$sql .= $where;
	$sql .= $s->order_by;

	$db->RecordsPerPage = $records_per_page;
	$db->PageNumber = $page_number;
	$db->query($sql);
	if ($db->next_record()) {
		$search_regexp = "";
		if (strlen($search_file)) {
			$search_regexp = preg_quote($search_file, "/");
		}

		$user_delete_file_url = "user_select.php?filetype=" . urlencode($filetype);
		if (strlen($search_file)) {
			$user_delete_file_url .= "&sf=" . urlencode($search_file);
		}
		if (strlen($control_name)) {
			$user_delete_file_url .= "&control_name=" . urlencode($control_name);
		}
		if (strlen($fid)) {
			$user_delete_file_url .= "&fid=" . urlencode($fid);
		}
		do {
			$file_id = $db->f("file_id");
			$file_path = $db->f("file_path");
			$file_name = basename($file_path);
			if ($search_regexp === "") {
				$file_name_html = $file_name;
			} else {
				$file_name_html = preg_replace ("/(" . $search_regexp . ")/i", "<font color=blue><b>\\1</b></font>", $file_name);
			}
			if ($filetype != "option_image") {
				$file_path_js = str_replace("'", "\\'", $file_path);
			} else {
				$file_path_js = str_replace("'", "\\'", $file_name);
			}
			$t->set_var("file_id", $file_id);
			$t->set_var("file_name", $file_name);
			$t->set_var("file_name_html", $file_name_html);
			$t->set_var("file_path", $file_path);
			$t->set_var("file_path_js", $file_path_js);
			$t->set_var("user_delete_file_url", $user_delete_file_url . "&operation=delete&id=" . $file_id);

			if ($show_preview_image == 1){
			  $t->parse("file_row", true);
			} else {
			  $t->parse("file_row_no_preview", true);
			}

		} while ($db->next_record());

		$t->set_var("no_files", "");
		$t->parse("files", false);
	}
	else
	{
		$t->parse("no_files", false);
		$t->set_var("files", "");
	}

	if (!$total_files && !strlen($search_file)) {
		$t->set_var("search_files", "");
	} else {
		$t->parse("search_files", false);
	}

	$t->pparse("main");

?>