<?php

	require('../../../wp-blog-header.php');

	require('wpframe.php');	

$time_zone = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}options WHERE option_name='timezone_string'", ''));

	date_default_timezone_set($time_zone->option_value);

    $time= date( 'Y-m-d H:i:s' ); 





// I could have put this in the quiz_form.php - but the redirect will not work.

if($_REQUEST['action'] == 'count'){

	$quiz_id = $_REQUEST['quiz_id'];

	$user = $_REQUEST['user'];

	

	if(empty($user)){

		$user="";

	}else{

	$quiz_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}quiz_quiz WHERE ID=%d", $quiz_id));

	$taken_time=$quiz_details->taken_time+1;

	$wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}quiz_quiz SET  taken_time='$taken_time' WHERE ID=%d", $quiz_id ));



	$wpdb->get_results($wpdb->prepare("INSERT INTO {$wpdb->prefix}quiz_result(ID,quiz_id,quiz_title,user_login,status,exam_on) VALUES('','$quiz_id','$quiz_details->name','$user','Abandoned','$time')", ''));	

		$result_id = $wpdb->insert_id;

		echo  $result_id;

	}

	

}else{ if(isset($_REQUEST['submit'])) {

	require('../../../wp-blog-header.php');

	auth_redirect();

	if($wp_version >= '2.6.5') check_admin_referer('JibuPro_create_edit_quiz');

	require('wpframe.php');	

	

	if($_REQUEST['action'] == 'edit') { //Update goes here
		$wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}quiz_quiz SET name=%s, description=%s,final_screen=%s, passed_rate=%s, no_of_question=%s WHERE ID=%d", $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['content'], $_REQUEST['passedMark'],$_REQUEST['no_of_ques'], $_REQUEST['quiz']));
		$wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}quiz_result SET quiz_title=%s  WHERE quiz_id=%d", $_REQUEST['name'], $_REQUEST['quiz']));
		wp_redirect($wpframe_home . '/wp-admin/edit.php?page=jibu-pro/quiz.php&message=updated');
	} else {
		$wpdb->get_results($wpdb->prepare("INSERT INTO {$wpdb->prefix}quiz_quiz(name,description,final_screen,added_on, passed_rate, no_of_question) VALUES(%s,%s,%s,'$time',%s,%s)", $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['content'],$_REQUEST['passedMark'],$_REQUEST['no_of_ques'] ));
		$quiz_id = $wpdb->insert_id;
		wp_redirect($wpframe_home . '/wp-admin/edit.php?page=jibu-pro/question.php&message=new_quiz&quiz='.$quiz_id);
	}

	}

	exit;

}

