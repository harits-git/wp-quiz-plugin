<?php
/*
Plugin Name:Jibu Pro
Plugin URI: http://jibupro.com
Description: Jibu Pro lets you add quizzes to your page/blog. This plugin is designed to be as easy to use as possible. Quizzes, questions and answers can be added from the admin side. This will appear in your post if you add a small HTML snippet in your post.
Version: 1.7
Author: Nickel Pro
Author URI: http://nickelpro.com
*/

/**
 * Add a new menu under Manage, visible for all users with template viewing level.
 */

// Administration menus 
add_action( 'admin_menu', 'JibuPro_add_menu_links' );
function JibuPro_add_menu_links() {
	global $wp_version, $_registered_pages;
	$view_level= 'delete_others_posts';
	$page = 'edit.php';
	if($wp_version >= '2.7') $page = 'tools.php';
	add_menu_page('wp Quiz Test','Jibu Pro' ,'read','quiz-plug','');
	add_submenu_page('quiz-plug', 'Readme', 'Readme', 'read', 'quiz-plug','readmeQuiz');
	add_submenu_page('quiz-plug', 'Create Quiz', 'Create Quiz',$view_level, 'edit.php?page=jibu-pro/quiz_form.php&amp;action=new');
	add_submenu_page('quiz-plug', 'Quiz Results', 'Quiz Results',$view_level, 'edit.php?page=jibu-pro/quiz_form.php&amp;action=result');
	add_submenu_page('quiz-plug', __('Manage Quiz', 'JibuPro'), __('Manage Quiz', 'JibuPro'), $view_level, 'jibu-pro/quiz.php');
	$code_pages = array('quiz_form.php','quiz_action.php', 'question_form.php', 'question.php');
	foreach($code_pages as $code_page) {
		$hookname = get_plugin_page_hookname("jibu-pro/$code_page", '' );
		$_registered_pages[$hookname] = true;
	}
    $hookname = get_plugin_page_hookname( "jibu-pro/quiz.php" , '' );
    $_registered_pages[$hookname] = true;
}
/// Initialize this plugin. Called by 'init' hook.
add_action('init', 'JibuPro_init');
function JibuPro_init() {
	load_plugin_textdomain('JibuPro', 'wp-content/plugins' );
	JibuPro_activate();
}

/// Add an option page for JibuPro
add_action('admin_menu', 'JibuPro_option_page');
function JibuPro_option_page() {
	//add_options_page(__('JibuPro Settings', 'JibuPro'), __('JibuPro Settings', 'JibuPro'), 'administrator', basename(__FILE__), 'JibuPro_options');
	add_submenu_page('quiz-plug', 'Settings', 'Settings', 'read', basename(__FILE__), 'JibuPro_options');
	}
function JibuPro_options() {
	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) die(__("Cheatin' uh?", 'JibuPro'));
	if (! user_can_access_admin_page()) wp_die( __('You do not have sufficient permissions to access this page', 'JibuPro') );
	require(ABSPATH. '/wp-content/plugins/jibu-pro/options.php');
}
function readmeQuiz(){
 	$image = plugins_url('logo.png', __FILE__);
 echo "<div class='wrap'><img src=\"".$image."\" alt='JIBU PRO' />";;
 echo "<p>JibuPro lets you create tests/quizzes with any number of questions in them. Each question can have as many answers as you want - and you can have an unlimited number of tests/quizzes.</p><br/>
<strong>Documentation</strong>
	<ol>
<li><a href=\"http://jibupro.com/?page_id=45\">Creating a new test</a></li>
	<li><a href=\"http://jibupro.com/?page_id=44\">Adding questions to a quiz/test</a></li>
	<li><a href=\"http://jibupro.com/?page_id=46\">Displaying a quiz/test</a></li>
	<li><a href=\"http://jibupro.com/?page_id=43\">Quiz & test managment</a></li>
	<li><a href=\"http://jibupro.com/?page_id=47\">Administration</a></li>
	<li><a href=\"http://jibupro.com/?page_id=48\">User management</a></li></ol>
";
echo "</div>";
}

/**
 * This will scan all the content pages that wordpress outputs for our special code. If the code is found, it will replace the requested quiz.
 */

 add_shortcode( 'Test', 'JibuPro_shortcode' );
 add_shortcode( 'Test_result', 'JibuPro_shortcode2' );
function JibuPro_shortcode( $attr ) {
	$quiz_id = $attr[0];
	$contents = '';
	if(is_numeric($quiz_id)) { // Basic validiation - more on the show_quiz.php file.
		ob_start();
		include(ABSPATH . 'wp-content/plugins/jibu-pro/show_quiz.php');
		$contents = ob_get_contents();
		ob_end_clean();

	}
	return $contents;
}
function JibuPro_shortcode2() {
	include(ABSPATH . 'wp-content/plugins/jibu-pro/user_result.php');
}
//add_action('activate_JibuPro/JibuPro.php','JibuPro_activate');


function JibuPro_activate() {
	global $wpdb;
	$database_version = '3';
	$installed_db = '4';
$upload = wp_upload_dir();
	$jibupro_dir = WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__));
	$newImage = $jibupro_dir.'/logo.png';
	if(!is_dir($jibupro_dir)){ mkdir($jibupro_dir); }
    if(!is_file($newImage)){
		copy('wp-content/plugins/jibu-pro/logo.png',$newImage);
	}	 

	// Initial options.
	add_option('JibuPro_show_answers', 1);
	add_option('JibuPro_single_page', 0);
	if($database_version != $installed_db) {
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE {$wpdb->prefix}quiz_answer (
					ID int(11) unsigned NOT NULL auto_increment,
					question_id int(11) unsigned NOT NULL,
					answer varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					correct enum('0','1') NOT NULL default '0',
					sort_order int(3) NOT NULL default 0,
					PRIMARY KEY  (ID)
				);
				CREATE TABLE {$wpdb->prefix}quiz_question (
					ID int(11) unsigned NOT NULL auto_increment,
					quiz_id int(11) unsigned NOT NULL,
					question mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					sort_order int(3) NOT NULL default 0,
					explanation mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					PRIMARY KEY  (ID),
					KEY quiz_id (quiz_id)
				);

				CREATE TABLE {$wpdb->prefix}quiz_quiz (
					ID int(11) unsigned NOT NULL auto_increment,
					name varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					description mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					final_screen mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					added_on datetime NOT NULL,
					taken_time int(11) unsigned NULL  default 0,
					passed int(11) unsigned NULL  default 0, 
					failed int(11) unsigned NULL  default 0,
					passed_rate int(3) unsigned NULL  default 40,
					no_of_question int(3) unsigned NULL  default 10,	
					PRIMARY KEY  (ID)
				);

				CREATE TABLE {$wpdb->prefix}quiz_result (
					ID int(11) unsigned NOT NULL auto_increment,
					quiz_id int(11) unsigned NOT NULL,
					user_login  varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					exam_on datetime NOT NULL,
					status  varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL default 'Failed',
					quiz_title varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci  NOT NULL,
					PRIMARY KEY  (ID)
				);";
		dbDelta($sql);
		update_option( "JibuPro_db_version", $database_version );
	}
}