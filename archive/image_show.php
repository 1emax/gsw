<?php
/*
  ****************************************************************************
  ***                                                                      ***
  ***      ViArt Shop 3.5                                                  ***
  ***      File:  image_show.php                                           ***
  ***      Built: Fri Jun 20 19:43:16 2008                                 ***
  ***                                                 ***
  ***                                                                      ***
  ****************************************************************************
*/


	include_once("./includes/common.php");
	include_once("./includes/image_functions.php");

	$item_id = get_param("item_id");
	$image_id = get_param("image_id");
	$ad_id = get_param("ad_id");
	$ad_image_id = get_param("ad_image_id");
	$article_id = get_param("article_id");
	$category_id = get_param("category_id");
	$ad_category_id = get_param("ad_category_id");
	$art_cat_id = get_param("art_cat_id");
	$type = get_param("type");
	$vc = get_param("vc");

	$watermark = false; $watermark_type = "";
	$sql = ""; $image_path = "";
	if ($item_id) {
		$restrict_products_images = get_setting_value($settings, "restrict_products_images", 0);
		if (strtolower($type) == "tiny") {
			$field_name = "tiny_image";
			$watermark = get_setting_value($settings, "watermark_tiny_image", 0);
		} elseif (strtolower($type) == "small") {
			$field_name = "small_image";
			$watermark = get_setting_value($settings, "watermark_small_image", 0);
		} elseif (strtolower($type) == "big" || strtolower($type) == "large") {
			$field_name = "big_image";
			$watermark = get_setting_value($settings, "watermark_big_image", 0);
		} elseif (strtolower($type) == "super") {
			$field_name = "super_image";
			$watermark = get_setting_value($settings, "watermark_super_image", 0);
		}
		if ($watermark) {
			$watermark_type = "product";
		}
		if ($field_name && ($watermark || $restrict_products_images)) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "items ";
			$sql .= " WHERE item_id=" . $db->tosql($item_id, INTEGER);
		}
	} elseif ($image_id) {
		$restrict_products_images = get_setting_value($settings, "restrict_products_images", 0);
		if (strtolower($type) == "small") {
			$field_name = "image_small";
			$watermark = get_setting_value($settings, "watermark_small_image", 0);
		} elseif (strtolower($type) == "big" || strtolower($type) == "large") {
			$field_name = "image_large";
			$watermark = get_setting_value($settings, "watermark_big_image", 0);
		} elseif (strtolower($type) == "super") {
			$field_name = "image_super";
			$watermark = get_setting_value($settings, "watermark_super_image", 0);
		}
		if ($watermark) {
			$watermark_type = "product";
		}
		if ($field_name && ($watermark || $restrict_products_images)) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "items_images ";
			$sql .= " WHERE image_id=" . $db->tosql($image_id, INTEGER);
		}
	} elseif ($ad_id) {
		$restrict_ads_images = get_setting_value($settings, "restrict_ads_images", 0);
		$field_name = (strtolower($type) == "small") ? "image_small" : "image_large";
		if ($field_name && $restrict_ads_images) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "ads_items ";
			$sql .= " WHERE item_id=" . $db->tosql($ad_id, INTEGER);
		}
	} elseif ($ad_image_id) {
		$restrict_ads_images = get_setting_value($settings, "restrict_ads_images", 0);
		$field_name = (strtolower($type) == "small") ? "image_small" : "image_large";
		if ($field_name && $restrict_ads_images) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "ads_images ";
			$sql .= " WHERE image_id=" . $db->tosql($ad_image_id, INTEGER);
		}
	} elseif ($category_id) { 
		$restrict_categories_images = get_setting_value($settings, "restrict_categories_images", 0);
		$field_name = (strtolower($type) == "large") ? "image_large" : "image";
		if ($field_name && $restrict_categories_images) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "categories ";
			$sql .= " WHERE category_id=" . $db->tosql($category_id, INTEGER);
		}
	} elseif ($ad_category_id) { 
		$restrict_categories_images = get_setting_value($settings, "restrict_categories_images", 0);
		$field_name = (strtolower($type) == "small") ? "image_small" : "image_large";
		if ($field_name && $restrict_categories_images) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "ads_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($ad_category_id, INTEGER);
		}
	} elseif ($art_cat_id) { 
		$restrict_categories_images = get_setting_value($settings, "restrict_categories_images", 0);
		$field_name = (strtolower($type) == "small") ? "image_small" : "image_large";
		if ($field_name && $restrict_categories_images) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "articles_categories ";
			$sql .= " WHERE category_id=" . $db->tosql($art_cat_id, INTEGER);
		}
	} elseif ($article_id) {
		$restrict_articles_images = get_setting_value($settings, "restrict_articles_images", 0);
		$field_name = (strtolower($type) == "small") ? "image_small" : "image_large";
		if ($field_name && $restrict_articles_images) {
			$sql  = " SELECT " . $field_name . " FROM " . $table_prefix . "articles ";
			$sql .= " WHERE article_id=" . $db->tosql($article_id, INTEGER);
		}
	}

	if (strlen($sql)) {
		$db->query($sql);
		if ($db->next_record()) {
			$image_path = $db->f($field_name);
			$image_vc = md5($image_path);
			if ($image_vc != $vc) {
				$image_path = "";
			}
		}
	}

	if (preg_match("/^http\:/", $image_path)) {
		header("HTTP/1.0 302 OK");
		header("Status: 302 OK");
		header("Location: " . $page_url);
		exit;
	} elseif (strlen($image_path)) {
		if ($watermark) {
			if ($watermark_type == "product") {
				$watermark_image = get_setting_value($settings, "watermark_image", "");
				$watermark_image_pos = get_setting_value($settings, "watermark_image_pos", "");
				$watermark_image_pct = get_setting_value($settings, "watermark_image_pct", "");
	  
				$watermark_text = get_setting_value($settings, "watermark_text", "");
				$watermark_text_size = get_setting_value($settings, "watermark_text_size", "");
				$watermark_text_color = get_setting_value($settings, "watermark_text_color", "");
				$watermark_text_angle = get_setting_value($settings, "watermark_text_angle", "");
				$watermark_text_pos = get_setting_value($settings, "watermark_text_pos", "");
				$watermark_text_pct = get_setting_value($settings, "watermark_text_pct", "");
			}
			image_watermark($image_path, $watermark_image, $watermark_image_pos, $watermark_image_pct, $watermark_text, $watermark_text_size, $watermark_text_color, $watermark_text_angle, $watermark_text_pos, $watermark_text_pct);
		} else {
	    $fp = @fopen($image_path, "rb");
	    if ($fp) {
				if(preg_match("/\.gif$/", $image_path)) {
					header("Content-Type: image/gif");
				} elseif(preg_match("/\.png$/", $image_path)) {
					header("Content-Type: image/png");
				} else {
					header("Content-Type: image/jpeg");
				}
			  fpassthru($fp);
			  exit;
	    }
		}
	} else {
		$image_id = imagecreatetruecolor(60, 60); 
		$bgc = imagecolorallocate($image_id, 255, 255, 255);
		$tc  = imagecolorallocate($image_id, 0, 0, 0);
		imagefilledrectangle($image_id, 0, 0, 60, 60, $bgc);
		imagerectangle ($image_id, 0 , 0, 59, 59, $tc);
		imagestring($image_id, 1, 10, 10, "Error", $tc);
		imagestring($image_id, 1, 10, 25, "Loading", $tc);
		imagestring($image_id, 1, 10, 40, "Image", $tc);

		header("Content-Type: image/jpeg");
		imagejpeg($image_id);

		//header("HTTP/1.0 404 Not Found");
		//header("Status: 404 Not Found");
		//exit;
	}

?>