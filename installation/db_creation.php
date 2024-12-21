<?php
error_reporting(E_ERROR|E_WARNING|E_PARSE);
ini_set ('display_errors', 1);
ini_set ('log_errors', 1);

include ("../conf/esa-config.php");
include (ESA_DIR."/modules/esa-db.php");

$db = DB::getInstance();
$db->setConnectionParameters(ESA_DB_HOST ,ESA_DB_NAME, ESA_DB_USER, ESA_DB_PASSWORD);
$db->connect();

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_browser ( 
    `id` bigint NOT NULL,
    `name` varchar(50) NOT NULL DEFAULT '',
    `version` varchar(50) NOT NULL DEFAULT '',
    `type` varchar(50) NOT NULL DEFAULT '',
    `category` varchar(50) NOT NULL DEFAULT '',
    `full_user_agent` varchar(255) NOT NULL DEFAULT '',  
    PRIMARY KEY (`id`)
    )");

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_os ( 
    `id` bigint NOT NULL,
    `name` varchar(50) NOT NULL DEFAULT '',
    `version` varchar(50) NOT NULL DEFAULT '', 
    PRIMARY KEY (`id`)
    )");

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_refferer ( 
    `id` bigint NOT NULL,
    `url` varchar(255) NOT NULL DEFAULT '',
    `site` varchar(50) NOT NULL DEFAULT '', 
    `type` int NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    )");

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_location ( 
    `id` bigint NOT NULL,
    `ip` varchar(255) NOT NULL DEFAULT '',
    `host` varchar(255) NOT NULL DEFAULT '',
    `domain` varchar(255) NOT NULL DEFAULT '',
    `country` varchar(50) NOT NULL DEFAULT '',
    `countryCode` varchar(3) NOT NULL DEFAULT '', 
    `city` varchar(50) NOT NULL DEFAULT '', 
    `lat` FLOAT(7,4) NOT NULL DEFAULT 0.0,
    `lng` FLOAT(7,4) NOT NULL DEFAULT 0.0,
    PRIMARY KEY (`id`)
    )");

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_url ( 
    `id` bigint NOT NULL,
    `url` varchar(255) NOT NULL DEFAULT '',
    `domain` varchar(255) NOT NULL DEFAULT '',
    `path` varchar(255) NOT NULL DEFAULT '',
    `query` varchar(50) NOT NULL DEFAULT '', 
    `fragment` varchar(50) NOT NULL DEFAULT '', 
    PRIMARY KEY (`id`)
    )");

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_events ( 
    `id` bigint NOT NULL,
    `visitor_id` bigint NOT NULL,
    `session_id` bigint NOT NULL,
    `location_id` bigint NOT NULL,
    `refferer_id` bigint NOT NULL DEFAULT 0,
    `os_id` bigint NOT NULL,
    `browser_id` bigint NOT NULL,
    `url_id` bigint NOT NULL,
    `timestamp` timestamp NOT NULL,
    `screen_width` int NOT NULL DEFAULT 0,
    `screen_height` int NOT NULL DEFAULT 0,
    `browser_width` int NOT NULL DEFAULT 0,
    `browser_height` int NOT NULL DEFAULT 0,
    `color_depth` int NOT NULL DEFAULT 0,
    `pixel_depth` int NOT NULL DEFAULT 0,
    `pixel_ratio` int NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    )");

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_errors ( 
    `id` bigint NOT NULL,
    `location_id` bigint NOT NULL,
    `os_id` bigint NOT NULL,
    `browser_id` bigint NOT NULL,
    `url_id` bigint NOT NULL,
    `timestamp` timestamp NOT NULL,
    `type` varchar(50) NOT NULL DEFAULT '',
    `message` varchar(250) NOT NULL DEFAULT '',
    `file` varchar(250) NOT NULL DEFAULT '',
    `line_no` int NOT NULL DEFAULT 0,
    `column_no` int NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    )");

$db->query("CREATE TABLE IF NOT EXISTS ".ESA_DB_PREFIX."_users (
    `id` bigint NOT NULL,
    `login` varchar(250) NOT NULL,
    `pass_hash` varchar(50) NOT NULL,
    `created_date` timestamp NOT NULL,
    `lastlogin_date` timestamp NOT NULL,
    `sites` varchar(250) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`)
    )");    

$db->close();


/*
id 	type version fullUserAgent type category


LOGS
id 	ip 	urlId 	agentId 	referenceId 	userId 	screenWidth 	screenHeight 	clientWidth 	clientHeight 	browserWidth 	browserHeight 	colorDepth 	pixelDepth 	density 	timestamp
Click
id 	visitor_id 	session_id 	site_id 	referer_id 	ua_id 	host_id 	os_id 	location_id 	referring_search_term_id 	timestamp 	yyyymmdd 	year 	month 	day 	dayofweek 	dayofyear 	weekofyear 	last_req 	ip_address 	is_new_visitor 	is_repeat_visitor 	language 	days_since_prior_session 	days_since_first_session 	num_prior_sessions 	medium 	source_id 	ad_id 	campaign_id 	user_name 	cv1_name 	cv1_value 	cv2_name 	cv2_value 	cv3_name 	cv3_value 	cv4_name 	cv4_value 	cv5_name 	cv5_value 	last_impression_id 	document_id 	target_id 	target_url 	hour 	minute 	second 	msec 	click_x 	click_y 	page_width 	page_height 	position 	approx_position 	dom_element_x 	dom_element_y 	dom_element_name 	dom_element_id 	dom_element_value 	dom_element_tag 	dom_element_text 	dom_element_class 	dom_element_parent_id 	tag_id 	placement_id 	ad_group_id 	host
Request
id 	visitor_id 	session_id 	site_id 	referer_id 	ua_id 	host_id 	os_id 	location_id 	referring_search_term_id 	timestamp 	yyyymmdd 	year 	month 	day 	dayofweek 	dayofyear 	weekofyear 	last_req 	ip_address 	is_new_visitor 	is_repeat_visitor 	language 	days_since_prior_session 	days_since_first_session 	num_prior_sessions 	medium 	source_id 	ad_id 	campaign_id 	user_name 	cv1_name 	cv1_value 	cv2_name 	cv2_value 	cv3_name 	cv3_value 	cv4_name 	cv4_value 	cv5_name 	cv5_value 	inbound_visitor_id 	inbound_session_id 	feed_subscription_id 	user_email 	hour 	minute 	second 	msec 	document_id 	site 	os 	prior_document_id 	is_comment 	is_entry_page 	is_browser 	is_robot 	is_feedreader 	
Session
id 	visitor_id 	site_id 	referer_id 	ua_id 	host_id 	os_id 	location_id 	referring_search_term_id 	timestamp 	yyyymmdd 	year 	month 	day 	dayofweek 	dayofyear 	weekofyear 	last_req 	ip_address 	is_new_visitor 	is_repeat_visitor 	language 	days_since_prior_session 	days_since_first_session 	num_prior_sessions 	medium 	source_id 	ad_id 	campaign_id 	user_name 	cv1_name 	cv1_value 	cv2_name 	cv2_value 	cv3_name 	cv3_value 	cv4_name 	cv4_value 	cv5_name 	cv5_value 	user_email 	hour 	minute 	num_pageviews 	num_comments 	is_bounce 	prior_session_lastreq 	prior_session_id 	time_sinse_priorsession 	prior_session_year 	prior_session_month 	prior_session_day 	prior_session_dayofweek 	prior_session_hour 	prior_session_minute 	os 	first_page_id 	last_page_id 	host 	city 	country 	site 	is_robot 	is_browser 	is_feedreader 	latest_attributions 	num_goals 	num_goal_starts 	goals_value 	commerce_trans_count 	commerce_trans_revenue 	commerce_items_revenue 	commerce_items_count 	commerce_items_quantity 	commerce_shipping_revenue 	commerce_tax_revenue 	goal_1 	goal_1_start 	goal_1_value 	goal_2 	goal_2_start 	goal_2_value 	goal_3 	goal_3_start 	goal_3_value 	goal_4 	goal_4_start 	goal_4_value 	goal_5 	goal_5_start 	goal_5_value 	goal_6 	goal_6_start 	goal_6_value 	goal_7 	goal_7_start 	goal_7_value 	goal_8 	goal_8_start 	goal_8_value 	goal_9 	goal_9_start 	goal_9_value 	goal_10 	goal_10_start 	goal_10_value 	goal_11 	goal_11_start 	goal_11_value 	goal_12 	goal_12_start 	goal_12_value 	goal_13 	goal_13_start 	goal_13_value 	goal_14 	goal_14_start 	goal_14_value 	goal_15 	goal_15_start 	goal_15_value 	

REFERERS 
idReference 	name
id 	url 	site_name 	site 	query_terms 	refering_anchortext 	page_title 	snippet 	is_searchengine 	
Source
id 	source_domain 	

URL (document)
idUrl 	name
id 	url 	uri 	page_title 	page_type 	

USER-AGENT
id 	type version fullUA

id 	ua 	browser_type 	browser 	

Host 
id 	ip_address 	host 	full_host 	city 	country 	latitude 	longitude 	

Location
id 	country 	country_code 	state 	city 	latitude 	longitude 	

Os
id 	name 	

Site
id 	site_id 	domain 	name 	description 	site_family 	settings 	

User 
id 	user_id 	password 	role 	real_name 	email_address 	temp_passkey 	creation_date 	last_update_date 	api_key 	

Visitor
id 	user_name 	user_email 	first_session_id 	first_session_year 	first_session_month 	first_session_day 	first_session_dayofyear 	first_session_timestamp 	first_session_yyyymmdd 	last_session_id 	last_session_year 	last_session_month 	last_session_day 	last_session_dayofyear 	num_prior_sessions

*/

?>