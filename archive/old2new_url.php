<?php
     
    function set_get_param($param_name, $param_value)
	{
		global $HTTP_GET_VARS;

		if (isset($_GET)) {
			$_GET[$param_name] = $param_value;
		} else {
			$HTTP_GET_VARS[$param_name] = $param_value;
		}
	}
    $UrlExist=FALSE;
    include_once("./includes/common.php");
    $site_name=$_SERVER["REDIRECT_URL"];
    $site_name=substr($site_name,1,strlen($site_name)-1);
    list($friendly_url1,$req) = explode(".",$site_name);
    $sql  ="(SELECT friendly_url FROM va_categories where friendly_url='".$friendly_url1."') UNION ";
    $sql.="(SELECT friendly_url FROM va_categories WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION (SELECT friendly_url FROM va_items WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION  (SELECT friendly_url FROM va_manufacturers WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION (SELECT friendly_url FROM va_users WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION  (SELECT friendly_url FROM va_articles_categories WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION (SELECT friendly_url FROM va_articles WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION  (SELECT friendly_url FROM va_forum_categories WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION (SELECT friendly_url FROM va_forum_list WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION  (SELECT friendly_url FROM va_forum WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION (SELECT friendly_url FROM va_ads_categories WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION  (SELECT friendly_url FROM va_ads_items WHERE friendly_url='".$friendly_url1."')";
    $sql.=" UNION (SELECT friendly_url FROM va_pages WHERE friendly_url='".$friendly_url1."')";
    $db->query($sql);
    if ($db->next_record()) {
      $UrlExist = $db->f("friendly_url");
    }
    //print_r($sql);die;
    $PageNumber = false;  
    if (!empty($_SERVER["QUERY_STRING"])&&(!$UrlExist))
        {            
            $request = $_SERVER['REQUEST_URI'];
            list($script_file, $request) = explode("?", $request);
            $request_parts = explode("&", $request);
            $request_variables = array();
            foreach($request_parts as $part){
                list($key, $value) = explode("=", $part);
               if(($key == 'page')&&($site_name!='page.php')){
                    $PageNumber = $value;
                    // set_get_param("page_number", $PageNumber);
                }
                else{
                    $request_variables[$key] = $value;
                }
            }
            $page_name = "";
            $friendly_url = "";
            $manf="";
            $filter_str="";            
            // check products categories
             	if ($site_name=='products.php') {
                	$sql  = " SELECT category_id,friendly_url FROM " . $table_prefix . "categories ";
                    foreach($request_variables as $key => $value){
                    if(!empty($value)){
                        if($key=='manf')
                            {
                                $manf=$value;
                            }
                        elseif($key=='category_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                        }
                    }
                }
                if ($filter_str)
                {
                    $sql.=" WHERE 1=1 ".$filter_str;
                }                
                $db->query($sql);

                if ($db->next_record()) {
                    $category_id = $db->f("category_id");                  
                    $page_name = "products.php";
                    if (!$manf){
                        $friendly_url = $db->f("friendly_url");
                        set_get_param("category_id", $category_id);
                    }
                    else
                    {
                      set_get_param("manf", $manf);
                    }
                }
            }
            // check product details page
            if ($site_name=='product_details.php') {
                $sql  = " SELECT item_id,friendly_url FROM " . $table_prefix . "items ";
                foreach($request_variables as $key => $value){
                    if(!empty($value)){
                      if($key=='item_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                            }
                        $filter_str .= ' AND ' . $key . "='" . $value."'";
                    }
                }
                if ($filter_str)
                {
                    $sql.=" WHERE 1=1 ".$filter_str;
                }
                $db->query($sql);
                if ($db->next_record()) {
                    $item_id = $db->f("item_id");
                    $friendly_url = $db->f("friendly_url");
                    set_get_param("item_id", $item_id);
                    $page_name = "product_details.php";
                }
	}

	
	

	// check user list page
	if ($site_name=='user_list.php') {
		$sql  = " SELECT user_id,friendly_url FROM " . $table_prefix . "users ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                    if($key=='user_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                    }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$user_id = $db->f("user_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("user", $user_id);
			$page_name = "user_list.php";
		}
	}

	// check articles categories
	if ($site_name=='articles.php') {
		$sql  = " SELECT category_id,friendly_url FROM " . $table_prefix . "articles_categories ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                    if($key=='category_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                   }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("category_id", $category_id);
			$page_name = "articles.php";
		}
	}

	// check article details page
	if ($site_name=='article.php') {
		$sql  = " SELECT article_id,friendly_url FROM " . $table_prefix . "articles ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                    if($key=='article_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$article_id = $db->f("article_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("article_id", $article_id);
			$page_name = "article.php";
		}
	}

	// check forum categories
	if ($site_name=='forums.php') {
		$sql  = " SELECT category_id,friendly_url FROM " . $table_prefix . "forum_categories ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                   if($key=='category_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                     }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("category_id", $category_id);
			$page_name = "forums.php";
		}
	}

	// check forum
	if ($site_name=='forum.php') {
		$sql  = " SELECT forum_id,friendly_url FROM " . $table_prefix . "forum_list ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                    if($key=='forum_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                     }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$forum_id = $db->f("forum_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("forum_id", $forum_id);
			$page_name = "forum.php";
		}
	}

	// check forum topic
	if ($site_name=='forum_topic.php') {
		$sql  = " SELECT thread_id,friendly_url FROM " . $table_prefix . "forum ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                    if($key=='thread_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                    }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$thread_id = $db->f("thread_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("thread_id", $thread_id);
			$page_name = "forum_topic.php";
		}
	}


	// check ads categories
	if ($site_name=='ads.php') {
		$sql  = " SELECT category_id FROM,friendly_url " . $table_prefix . "ads_categories ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                  if($key=='category_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("category_id", $category_id);
			$page_name = "ads.php";
		}
	}

	// check ads items
	if ($site_name=='ads_details.php') {
		$sql  = " SELECT item_id,friendly_url FROM " . $table_prefix . "ads_items ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                if($key='item_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$item_id = $db->f("item_id");
            $friendly_url = $db->f("friendly_url");
			set_get_param("item_id", $item_id);
			$page_name = "ads_details.php";
		}
	}

	// check custom page
	if ($site_name=='page.php') {
		$sql  = " SELECT page_code,friendly_url FROM " . $table_prefix . "pages ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                 if($key=='page')
                            {
                                $filter_str .= " AND page_code='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$page_code = $db->f("page_code");
            $friendly_url = $db->f("friendly_url");
			set_get_param("page", $page_code);
			$page_name = "page.php";
		}
	}

	// check manuals list
	if ($site_name=='manuals_articles.php') {
		$sql  = " SELECT manual_id FROM " . $table_prefix . "manuals_list ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                  if($key=='manual_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$manual_id = $db->f("manual_id");
			set_get_param("manual_id", $manual_id);
			$page_name = "manuals_articles.php";
		}
	}

	// check manual article
	if ($site_name=='manuals_article_details.php') {
		$sql  = " SELECT article_id FROM " . $table_prefix . "manuals_articles ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                   if($key=='article_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$article_id = $db->f("article_id");
			set_get_param("article_id", $article_id);
			$page_name = "manuals_article_details.php";
		}
	}

	// check manual categories
	if ($site_name=='manuals.php') {
		$sql  = " SELECT category_id FROM " . $table_prefix . "manuals_categories ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                 if($key=='category_id')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
			set_get_param("category_id", $category_id);
			$page_name = "manuals.php";
		}
	}

	if ($site_name=='script_name.php') {
		$sql  = " SELECT script_name FROM " . $table_prefix . "friendly_urls ";
		foreach($request_variables as $key => $value){
                if(!empty($value)){
                if($key=='script_name')
                            {
                                $filter_str .= ' AND ' . $key . "='" . $value."'";
                            }
                        else {
                                break;
                                }
                }
            }
        if ($filter_str)
        {
            $sql.=" WHERE 1=1 ".$filter_str;
        }       
		$db->query($sql);
		if ($db->next_record()) {
			$page_name = $db->f("script_name");
		}
	}

    if ($page_name) {
        if ($friendly_url)
            {
                header("HTTP/1.0 301 Moved Permanently");
            	header("Status: 301 Moved Permanently ");
                header("Location:".$friendly_url.".php" . ($PageNumber ? '?page=' . $PageNumber : ''));
            }		
		include_once($page_name);
		return;
        }
        else {
		$is_friendly_url = false;
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
                echo file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/page404.php");
		exit;
	}	
   
    }
    else {
        
		if (isset($_SERVER["REQUEST_URI"])) {
		$request_uri = $_SERVER["REQUEST_URI"];
	} elseif (isset($_SERVER["URL"])) {
		$request_uri = $_SERVER["URL"];
	} elseif (isset($_SERVER["HTTP_X_REWRITE_URL"])) {
		$request_uri = $_SERVER["HTTP_X_REWRITE_URL"]; // IIS Mod-Rewrite - HTTP_X_ORIGINAL_URL
	} elseif (isset($_SERVER["SERVER_SOFTWARE"]) && preg_match("/IIS/i", $_SERVER["SERVER_SOFTWARE"])
		&& isset($_SERVER["QUERY_STRING"]) && preg_match("/^404;/i", $_SERVER["QUERY_STRING"])) {
		// IIS 404 Error
		$request_uri = preg_replace("/^404;/", "", $_SERVER["QUERY_STRING"]);
	} else {
		$request_uri = getenv("REQUEST_URI");
		if (!$request_uri) { $request_uri = getenv("URL"); }
		if (!$request_uri) { $request_uri = getenv("HTTP_X_REWRITE_URL"); }
	}
    $request_uri = ( (strrpos($request_uri, '/') + 1) == strlen($request_uri)) ? substr($request_uri, 0, -1) : $request_uri;

	$friendly_url = ""; $query_string = "";
	if ($request_uri) {
		//$slash_position = strrpos ($request_uri, "/");
		//$request_uri = ($slash_position === false) ? $request_uri : substr($request_uri, $slash_position + 1);
		$question_mark = strrpos ($request_uri, "?");
		if ($question_mark === false) {
			$friendly_url = $request_uri;
		} else {
			$friendly_url = substr($request_uri, 0, $question_mark);
			$query_string = substr($request_uri, $question_mark + 1);
		}
	}
    // $is_friendly_url = preg_match("/(\.html)|(\.htm)|(.php)$/", $friendly_url);
 	$is_friendly_url = preg_match("/(\.html)|(\.htm)|(.php)|(\/)$/", $friendly_url) || !preg_match("/\./", $friendly_url);
	if (!$is_friendly_url) {
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
         echo file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/page404.php");
		exit;
	}
	$friendly_url = preg_replace("/(\.html)|(\.htm)|(.php)$/i", "", $friendly_url);


	if ($query_string) {
		$query_params = explode("&", $query_string);
		for ($qp = 0; $qp < sizeof($query_params); $qp++) {
			$query_param = $query_params[$qp];
			if (preg_match("/^([^=]+)=(.*)$/", $query_param, $matches)) {
				set_get_param($matches[1], urldecode($matches[2]));
			} else {
				set_get_param($query_param, "");
			}
		}
	}

	include_once("./includes/common.php");

	$parsed_url = parse_url($settings["site_url"]);
	$site_path = isset($parsed_url["path"]) ? $parsed_url["path"] : "/";
	$friendly_url = preg_replace("/^".preg_quote($site_path, "/")."/i", "", $friendly_url);
	$friendly_url = urldecode($friendly_url);

	// what page should be included
	$page_name = "";

	// check products categories
	if (!$page_name) {
		$sql  = " SELECT category_id FROM " . $table_prefix . "categories ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
			set_get_param("category_id", $category_id);
			$page_name = "products.php";
		}
	}

	// check product details page
	if (!$page_name) {
		$sql  = " SELECT item_id FROM " . $table_prefix . "items ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$item_id = $db->f("item_id");
			set_get_param("item_id", $item_id);
			$page_name = "product_details.php";
		}
	}

	// check manufacturers page
	if (!$page_name) {
		$sql  = " SELECT manufacturer_id FROM " . $table_prefix . "manufacturers ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$manufacturer_id= $db->f("manufacturer_id");
			set_get_param("manf", $manufacturer_id);
			$page_name = "products.php";
		}
	}

	// check user list page
	if (!$page_name) {
		$sql  = " SELECT user_id FROM " . $table_prefix . "users ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$user_id = $db->f("user_id");
			set_get_param("user", $user_id);
			$page_name = "user_list.php";
		}
	}

	// check articles categories
	if (!$page_name) {
		$sql  = " SELECT category_id FROM " . $table_prefix . "articles_categories ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
			set_get_param("category_id", $category_id);
			$page_name = "articles.php";
		}
	}

	// check article details page
	if (!$page_name) {
		$sql  = " SELECT article_id FROM " . $table_prefix . "articles ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$article_id = $db->f("article_id");
			set_get_param("article_id", $article_id);
			$page_name = "article.php";
		}
	}

	// check forum categories
	if (!$page_name) {
		$sql  = " SELECT category_id FROM " . $table_prefix . "forum_categories ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
			set_get_param("category_id", $category_id);
			$page_name = "forums.php";
		}
	}

	// check forum
	if (!$page_name) {
		$sql  = " SELECT forum_id FROM " . $table_prefix . "forum_list ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$forum_id = $db->f("forum_id");
			set_get_param("forum_id", $forum_id);
			$page_name = "forum.php";
		}
	}

	// check forum topic
	if (!$page_name) {
		$sql  = " SELECT thread_id FROM " . $table_prefix . "forum ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$thread_id = $db->f("thread_id");
			set_get_param("thread_id", $thread_id);
			$page_name = "forum_topic.php";
		}
	}


	// check ads categories
	if (!$page_name) {
		$sql  = " SELECT category_id FROM " . $table_prefix . "ads_categories ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
			set_get_param("category_id", $category_id);
			$page_name = "ads.php";
		}
	}

	// check ads items
	if (!$page_name) {
		$sql  = " SELECT item_id FROM " . $table_prefix . "ads_items ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$item_id = $db->f("item_id");
			set_get_param("item_id", $item_id);
			$page_name = "ads_details.php";
		}
	}

	// check custom page
	if (!$page_name) {
		$sql  = " SELECT page_code FROM " . $table_prefix . "pages ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$page_code = $db->f("page_code");
			set_get_param("page", $page_code);
			$page_name = "page.php";
		}
	}

	// check manuals list
	if (!$page_name) {
		$sql  = " SELECT manual_id FROM " . $table_prefix . "manuals_list ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$manual_id = $db->f("manual_id");
			set_get_param("manual_id", $manual_id);
			$page_name = "manuals_articles.php";
		}
	}

	// check manual article
	if (!$page_name) {
		$sql  = " SELECT article_id FROM " . $table_prefix . "manuals_articles ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$article_id = $db->f("article_id");
			set_get_param("article_id", $article_id);
			$page_name = "manuals_article_details.php";
		}
	}

	// check manual categories
	if (!$page_name) {
		$sql  = " SELECT category_id FROM " . $table_prefix . "manuals_categories ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$category_id = $db->f("category_id");
			set_get_param("category_id", $category_id);
			$page_name = "manuals.php";
		}
	}

	if (!$page_name) {
		$sql  = " SELECT script_name FROM " . $table_prefix . "friendly_urls ";
		$sql .= " WHERE friendly_url=" . $db->tosql($friendly_url, TEXT);
		$db->query($sql);
		if ($db->next_record()) {
			$page_name = $db->f("script_name");
		}
	}

	if ($page_name) {
		header("HTTP/1.0 200 OK");
		header("Status: 200 OK");
		include_once($page_name);
		return;
	} else {
		$is_friendly_url = false;
		header("HTTP/1.0 404 Not Found");
		header("Status: 404 Not Found");
         echo file_get_contents("http://" . $_SERVER['SERVER_NAME'] . "/page404.php");
		exit;
	}
	}
?>