<?php



require('wpframe.php');

wpframe_stop_direct_call(__FILE__);
$action=$_REQUEST['action'];

if($action == 'All' || $action == 'Passed' || $action == 'Failed' || $action == 'Abandon'){  

$q_id=$_REQUEST['quiz_id'];

if($action == 'Passed'){ $query="status='Passed'"; $t=" - Passed"; }
if($action == 'Failed'){ $query="status='Failed'"; $t=" - Failed"; }
if($action == 'Abandon'){ $query="status='Abandoned'"; $t=" - Abandoned"; }
if($action == 'All'){ $query="1"; $t=""; }

$quiz = $wpdb->get_row("SELECT  name   FROM {$wpdb->prefix}quiz_quiz WHERE  ID={$q_id}   "); ?>
<div class="wrap" style="width:90%; margin-left:15px;">
<h2><?php echo $quiz->name." Results".$t; ?></h2>
<div  style="float:right; margin-top: -25px;"><a href="edit.php?page=jibu-pro/quiz_form.php&action=result">Back to Results page</a></div>
<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php e('ID') ?></div></th>
		<th scope="col"><?php e('Nama') ?></th>
                <th scope="col"><?php e('NIM') ?></th>
				<th scope="col"><?php e('Percent') ?></th>
        <?php
        if($action == 'All'){ ?>
		<th scope="col"><?php e('Passed') ?></th>
        <th scope="col"><?php e('Failed') ?></th>
        <th scope="col"><?php e('Abandoned') ?></th>



		<?php }else{ ?>



		<th scope="col"><?php e('No. of Times') ?></th>



		<?php }?>



	</tr>



	</thead>







	<tbody id="the-list">







<?php



	$all_user = $wpdb->get_results("SELECT  DISTINCT user_login FROM {$wpdb->prefix}quiz_result WHERE {$query} AND quiz_id={$q_id}");



	



if (count($all_user)) {
	$i=1;
	foreach($all_user as $user){

		$count = $wpdb->get_row("SELECT  count(quiz_id) as count  FROM {$wpdb->prefix}quiz_result WHERE user_login='$user->user_login' AND {$query} AND quiz_id={$q_id}   ");
        
		$user_id = $wpdb->get_results("SELECT  ID  FROM {$wpdb->prefix}users WHERE user_login='$user->user_login'");
		
		$uid = $user_id[0]->ID;
		
		$usernim = $wpdb->get_results("SELECT  meta_value  FROM {$wpdb->prefix}usermeta WHERE user_id='$uid' AND meta_key='last_name'   ");
		
		$unim = $usernim[0]->meta_value;

		$class = ('alternate' == $class) ? '' : 'alternate';
		
		$percent = $wpdb->get_row("SELECT  max(percent) as top FROM {$wpdb->prefix}quiz_result WHERE user_login='$user->user_login' AND {$query} AND quiz_id={$q_id}   ");
		
		print "<tr class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $i++; ?></th>
		<th><?php echo $user->user_login; ?></th>
        <th><?php echo $unim;?></th>		
		<th><?php echo $percent->top; ?></th>
         <?php
        if($action == 'All'){ 
			$result1 = $wpdb->get_row("SELECT  count(quiz_id) as pass  FROM {$wpdb->prefix}quiz_result WHERE user_login='$user->user_login' AND status='passed' AND quiz_id={$q_id}   ");

			$result2 = $wpdb->get_row("SELECT  count(quiz_id) as fail  FROM {$wpdb->prefix}quiz_result WHERE user_login='$user->user_login' AND status='failed' AND quiz_id={$q_id}   ");

			$result3 = $wpdb->get_row("SELECT  count(quiz_id) as aban  FROM {$wpdb->prefix}quiz_result WHERE user_login='$user->user_login' AND status='abandoned' AND quiz_id={$q_id}   ");

		?>
		<th scope="col"><?php echo $result1->pass; ?></th>
        <th scope="col"><?php echo $result2->fail; ?></th>
        <th scope="col"><?php echo $result3->aban; ?></th>
		<?php }else{ ?>
        <th><?php echo $count->count; ?></th>
		<?php }?>
	</tr>
<?php
		}
	} else {
?>
	<tr>
		<th colspan="5"><?php e('No Result found.') ?></th>
	</tr>
<?php
}
?>
	</tbody>
</table>
</div>

<?php

}else{

if($_REQUEST['message'] == 'updated') wpframe_message('Quiz Updated');

if($_REQUEST['action'] == 'delete') {

	$wpdb->get_results("DELETE FROM {$wpdb->prefix}quiz_quiz WHERE ID='$_REQUEST[quiz]'");

	$wpdb->get_results("DELETE FROM {$wpdb->prefix}quiz_answer WHERE question_id=(SELECT ID FROM {$wpdb->prefix}quiz_question WHERE quiz_id='$_REQUEST[quiz]')");

	$wpdb->get_results("DELETE FROM {$wpdb->prefix}quiz_question WHERE quiz_id='$_REQUEST[quiz]'");
	wpframe_message("Quiz Deleted");
}

?>

<div class="wrap">
<h2><?php e("Manage Quiz"); ?></h2>

<?php
wp_enqueue_script( 'listman' );
wp_print_scripts();
?>
<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php e('ID') ?></div></th>
		<th scope="col"><?php e('Title') ?></th>
		<th scope="col"><?php e('Questions') ?></th>
		<th scope="col"><?php e('Created on') ?></th>
		<th scope="col" colspan="3"><?php e('Action') ?></th>
	</tr>
	</thead>
	<tbody id="the-list">



<?php



// Retrieve the quizes

$all_quiz = $wpdb->get_results("SELECT Q.ID,Q.name,Q.added_on,(SELECT COUNT(*) FROM {$wpdb->prefix}quiz_question WHERE quiz_id=Q.ID) AS question_count
									FROM `{$wpdb->prefix}quiz_quiz` AS Q ");
if (count($all_quiz)) {
	foreach($all_quiz as $quiz) {
		$class = ('alternate' == $class) ? '' : 'alternate';
		print "<tr id='quiz-{$quiz->ID}' class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $quiz->ID ?></th>
		<td><?php echo stripslashes($quiz->name)?></td>
		<td><?php echo $quiz->question_count ?></td>
		<td><?php echo date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quiz->added_on)) ?></td>
		<td><a href='edit.php?page=jibu-pro/question.php&amp;quiz=<?php echo $quiz->ID?>' class='edit'><?php e('Manage Questions')?></a></td>
		<td><a href='edit.php?page=jibu-pro/quiz_form.php&amp;quiz=<?php echo $quiz->ID?>&amp;action=edit' class='edit'><?php e('Edit'); ?></a></td>
		<td><a href='edit.php?page=jibu-pro/quiz.php&amp;action=delete&amp;quiz=<?php echo $quiz->ID?>' class='delete' onclick="return confirm('<?php echo  addslashes(t("You are about to delete this quiz? This will delete all the questions and answers within this quiz. Press 'OK' to delete and 'Cancel' to stop."))?>');"><?php e('Delete')?></a></td>
		</tr>
<?php
		}
	} else {
?>
	<tr>
		<td colspan="5"><?php e('No Quizes found.') ?></td>
	</tr>
<?php
}

?>
	</tbody>
</table>
</div> 
<?php } ?>