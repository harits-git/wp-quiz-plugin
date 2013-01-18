<?php
require('wpframe.php');
wpframe_stop_direct_call(__FILE__);
$action = 'new';
if($_REQUEST['action'] == 'edit') $action = 'edit';

$dquiz = array();
if($action == 'edit') {
	$dquiz = $wpdb->get_row($wpdb->prepare("SELECT name,description,final_screen, passed_rate, no_of_question  FROM {$wpdb->prefix}quiz_quiz WHERE ID=%d", $_REQUEST['quiz']));
	$final_screen = stripslashes($dquiz->final_screen);
} else {
	$final_screen = t("<p>Congratulations - you have completed %%QUIZ_NAME%%.</p>\n\n<p>You scored %%SCORE%% out of %%TOTAL%%.</p>\n\n<p>Your performance have been rated as '%%RATING%%'</p>");
}
?>
<script type="text/javascript">
function validate(){
	var passed_rate = document.getElementById('passedMark').value;
	var re = /\s/g; //Match any white space including space, tab, form-feed, etc.
	var str = passed_rate.replace(re, "");
	
	if (str.length == 0) {
		alert("Please Enter  Passing  Rate");
		  return false;
	}  
	if(isNaN(str) || str > 100 || str < 1){
		alert("Please Enter  a valid Passing  Rate");
		return false;
	}
	var name = document.getElementById('title').value;
	str = name.replace(re, "");
	
	if (str.length == 0) {
		alert("Please Enter  Quiz title");
		  return false;
	}  
	
	var no = document.getElementById('no_of_ques').value;
	str = no.replace(re, "");
	
	if (str.length == 0) {
		document.getElementById('no_of_ques').value = 10;
	} 
	if (str<1) {
		alert("Please Enter a  valid no");
		document.getElementById('no_of_ques').value = '';
		return false;
	}   
}
</script>

<?php if( $_REQUEST['action'] != 'result'){  ?>
<div class="wrap">
<h2><?php e(ucfirst($action) . " Quiz"); ?></h2>
<?php
wpframe_add_editor_js();
?>
<form name="post" action="<?php echo $GLOBALS['wpframe_plugin_folder'] ?>/quiz_action.php" method="post" id="post" onsubmit="return validate();">
<div id="poststuff">

<div class="postbox" id="titlediv">
<h3 class="hndle"><span><?php e('Quiz Name') ?></span></h3>
<div class="inside">
<input type='text' name='name' id="title" value='<?php echo stripslashes($dquiz->name); ?>' />
</div></div>

<div class="postbox">
<h3 class="hndle"><span><?php e('Description') ?></span></h3>
<div class="inside">
<textarea name='description' rows='5' cols='50' style='width:100%'><?php echo stripslashes($dquiz->description); ?></textarea>
</div></div>

<div class="postbox" id="titlediv">
<h3 class="hndle"><span><?php e('Passed Mark') ?></span></h3>
<div class="inside">
<input type='text' name='passedMark' id="passedMark" value='<?php echo stripslashes($dquiz->passed_rate); ?>' /> %
</div></div>

<div class="postbox" id="titlediv">
<h3 class="hndle"><span><?php e('No of question in a set') ?></span></h3>
<div class="inside">
<input type='text' name='no_of_ques' id="no_of_ques" value='<?php echo stripslashes($dquiz->no_of_question); ?>' /> 
</div></div>

<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea postbox">


<h3 class="hndle"><span><?php e('Final Screen') ?></span></h3>


<div class="inside">
<?php the_editor($final_screen); ?>
<p><strong><?php e('Usable Variables...') ?></strong></p>

<table>

<?php if($dquiz->passed_rate) $passed_rate = $dquiz->passed_rate;
      else   $passed_rate = 40; 
		$fail_rate = $passed_rate-1; ?>
        
<tr><th style="text-align:left;"><?php e('Variable') ?></th><th style="text-align:left;"><?php e('Value') ?></th></tr>
<tr><th>%%SCORE%%</th><th><?php e('The number of correct answers') ?></th></tr>
<tr><th>%%TOTAL%%</th><th><?php e('Total number of questions') ?></th></tr>
<tr><th>%%PERCENTAGE%%</th><th><?php e('Correct answer percentage') ?></th></tr>
<tr><th>%%GRADE%%</th><th><?php e('1-10 value. 1 is 10% or less, 2 is 20% or less, and so on') ?>.</th></tr>
<tr><th>%%WRONG_ANSWERS%%</th><th><?php e('Number of answers you got wrong') ?></th></tr>
<tr><th>%%RATING%%</th><th><?php e("A rating of your performance - it could be 'Failed'(0-{$fail_rate}%), 'Passed'({$passed_rate}%-100%)") ?></th></tr>
<tr><th>%%QUIZ_NAME%%</th><th><?php e('The name of the quiz') ?></th></tr>
<tr><th>%%DESCRIPTION%%</th><th><?php e('The text entered in the description field.') ?></th></tr>
</table>
</div>
</div>

<?php
// I'll put 2 editors here - as soon as 'http://wordpress.org/support/topic/179110?replies=2' bug is fixed.
?>

<p class="submit">
<?php wp_nonce_field('JibuPro_create_edit_quiz'); ?>
<input type="hidden" name="action" value="<?php echo $action; ?>" />
<input type="hidden" name="quiz" value="<?php echo $_REQUEST['quiz']; ?>" />
<input type="hidden" id="user-id" name="user_ID" value="<?php echo (int) $user_ID ?>" />
<span id="autosave"></span>
<input type="submit" name="submit" value="<?php e('Save') ?>" style="font-weight: bold;" tabindex="4" />
</p>
</div>
</form>

</div>
<?php } else{ ?>
<div class="wrap">
<!--results of all Quiz-->

<h2><?php e("Quiz Results"); ?></h2>
<table class="widefat">
	<thead><tr><th>ID</th><th>Title</th><th>Attempts</th><th>Passed</th><th>Failed</th><th>Abandoned</th><th>Created on</th></tr></thead>
	<tbody class="the-list">
	<?php	
		$quiz_ds= $wpdb->get_results("SELECT * FROM {$wpdb->prefix}quiz_quiz WHERE 1 ORDER BY ID");
		foreach ($quiz_ds as $quiz_d) { ?>
		<tr>
			<th><?php echo $quiz_d->ID; ?></th>
			<th><?php echo $quiz_d->name; ?></th>
			<th><?php if($quiz_d->taken_time==0){
				?> <?php echo $quiz_d->taken_time; ?> <?php
				}else { ?><a href="edit.php?page=jibu-pro/quiz.php&amp;action=All&amp;quiz_id=<?php echo $quiz_d->ID; ?>"><?php echo $quiz_d->taken_time; ?></a> <?php } ?>
            </th>
			<th><?php if($quiz_d->passed==0){
				?> <?php echo $quiz_d->passed; ?> <?php
				}else { ?><a href="edit.php?page=jibu-pro/quiz.php&amp;action=Passed&amp;quiz_id=<?php echo $quiz_d->ID; ?>"><?php echo $quiz_d->passed; ?></a> <?php } ?>
            </th>
			<th><?php if($quiz_d->failed==0){
				?> <?php echo $quiz_d->failed; ?> <?php
				}else { ?><a href="edit.php?page=jibu-pro/quiz.php&amp;action=Failed&amp;quiz_id=<?php echo $quiz_d->ID; ?>"><?php echo $quiz_d->failed; ?></a> <?php } ?>
            </th>
			<th><?php
				$abd=$quiz_d->taken_time-($quiz_d->passed+$quiz_d->failed); 
			 	if($abd==0){
				?> <?php echo $abd ?> <?php
				}else { ?><a href="edit.php?page=jibu-pro/quiz.php&amp;action=Abandon&amp;quiz_id=<?php echo $quiz_d->ID; ?>"><?php echo $abd; ?></a> <?php } ?>
			</th>
			<th><?php echo $quiz_d->added_on; ?></th>
		</tr>
	<?php } ?>
	</tbody>
</table>
</div>
<?php } ?>


