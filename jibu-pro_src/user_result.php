<?php
 require_once('wpframe.php');
 global $wpdb;
     $current_user = wp_get_current_user();
	if(!empty($current_user->user_login)){ ?>
		<div class="wrap" style="width:90%; margin-left:15px;">
<h2><?php echo "Results of ".$current_user->user_login; ?></h2>
<table class="widefat">
	<thead>
	<tr>
		<th scope="col"><div style="text-align: center;"><?php e('ID') ?></div></th>
		<th scope="col"><?php e('Quiz Title') ?></th>
		<th scope="col"><?php e('Last Exam Date') ?></th>
		<th scope="col"><?php e('Status') ?></th>
	</tr>
	</thead>

	<tbody id="the-list">
<?php
// Retrieve the quizes
	$user_ = $current_user->user_login;
$all_quiz = $wpdb->get_results("SELECT  DISTINCT quiz_id FROM {$wpdb->prefix}quiz_result WHERE user_login='$user_'");
 
if (count($all_quiz)) {
	$i=1;
	foreach($all_quiz as $quiz){
	    
		$latestDate = $wpdb->get_row($wpdb->prepare("SELECT MAX(exam_on) as lastDate  FROM {$wpdb->prefix}quiz_result WHERE user_login='$user_' AND quiz_id='$quiz->quiz_id'"));
		$quiz_result = $wpdb->get_row($wpdb->prepare("SELECT *  FROM {$wpdb->prefix}quiz_result WHERE user_login='$user_' AND quiz_id='$quiz->quiz_id' AND exam_on='$latestDate->lastDate'"));
		$class = ('alternate' == $class) ? '' : 'alternate';
		 
		print "<tr class='$class'>\n";
		?>
		<th scope="row" style="text-align: center;"><?php echo $i++; ?></th>
		<th><?php echo $quiz_result->quiz_title; ?></th>
		<th><?php echo $latestDate->lastDate; ?></th>
		<th><?php echo $quiz_result->status; ?></th>
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

<?php	} ?>