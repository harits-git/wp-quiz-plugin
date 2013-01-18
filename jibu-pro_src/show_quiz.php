<?php

require_once('wpframe.php');


if(!is_single() and isset($GLOBALS['JibuPro_client_includes_loaded'])) { #If this is in the listing page - and a quiz is already shown, don't show another.

	printf(t("Please go to <a href='%s'>%s</a> to view the quiz"), get_permalink(), get_the_title());

} else {



global $wpdb;

$GLOBALS['wpframe_plugin_name'] = basename(dirname(__FILE__));

$GLOBALS['wpframe_plugin_folder'] = $GLOBALS['wpframe_wordpress'] . '/wp-content/plugins/' . $GLOBALS['wpframe_plugin_name'];

$active_user = wp_get_current_user();
//echo "active_user ".$active_user->user_login;
$cek = $wpdb->get_row("SELECT  count(*) as cek  FROM {$wpdb->prefix}quiz_result WHERE user_login='$active_user->user_login' AND quiz_id=1  AND status!='Abandoned' ");
echo "hasil cek ".$cek->cek;
if($cek->cek < 1){//cek

$answer_display = get_option('JibuPro_show_answers');

$all_question = $wpdb->get_results($wpdb->prepare("SELECT ID,question,explanation FROM {$wpdb->prefix}quiz_question WHERE quiz_id=%d ORDER BY ID", $quiz_id));

if($all_question) {
	
	if(!isset($GLOBALS['JibuPro_client_includes_loaded'])) {
	
	
?>


<script language="JavaScript">
TargetDate = "12/15/2012 0:50:03 ";
BackColor = "palegreen";
ForeColor = "navy";
CountActive = true;
CountStepper = -1;
LeadingZero = true;
DisplayFormat = "%%M%% Minutes, %%S%% Seconds.";
FinishMessage = "Waktu habis!";

function removeElement(parentDiv, childDiv){
     if (childDiv == parentDiv) {
          alert("The parent div cannot be removed.");
     }
     else if (document.getElementById(childDiv)) {     
          var child = document.getElementById(childDiv);
          var parent = document.getElementById(parentDiv);
          parent.removeChild(child);
     }
     else {
          alert("Child div has already been removed or does not exist.");
          return false;
     }
}

function calcage(secs, num1, num2) {
  s = ((Math.floor(secs/num1))%num2).toString();
  if (LeadingZero && s.length < 2)
    s = "0" + s;
  return "<b>" + s + "</b>";
}

function CountBack(secs) {
  if (secs < 0) {
    //TargetDate = "12/15/2012 0:00:00 ";
	//CountActive = false;
	document.getElementById("cntdwn").innerHTML = FinishMessage;
    document.getElementById("action-button").click();
	
	removeElement('parent','cntdwn');
	return;
  }else{
	  DisplayStr = DisplayFormat.replace(/%%D%%/g, calcage(secs,86400,100000));
	  DisplayStr = DisplayStr.replace(/%%H%%/g, calcage(secs,3600,24));
	  DisplayStr = DisplayStr.replace(/%%M%%/g, calcage(secs,60,60));
	  DisplayStr = DisplayStr.replace(/%%S%%/g, calcage(secs,1,60));

	  document.getElementById("cntdwn").innerHTML = "time left : "+DisplayStr;
	  if (CountActive)
		setTimeout("CountBack(" + (secs+CountStepper) + ")", SetTimeOutPeriod);
	}
}

function putspan(backcolor, forecolor) {
 document.write("<div id='parent'><span id='cntdwn' style='background-color:" + backcolor + 
                "; color:" + forecolor + "'></span></div>");
}

if (typeof(BackColor)=="undefined")
  BackColor = "white";
if (typeof(ForeColor)=="undefined")
  ForeColor= "black";
if (typeof(TargetDate)=="undefined")
  TargetDate = "12/31/2020 5:00 AM";
if (typeof(DisplayFormat)=="undefined")
  DisplayFormat = "%%D%% Days, %%H%% Hours, %%M%% Minutes, %%S%% Seconds.";
if (typeof(CountActive)=="undefined")
  CountActive = true;
if (typeof(FinishMessage)=="undefined")
  FinishMessage = "";
if (typeof(CountStepper)!="number")
  CountStepper = -1;
if (typeof(LeadingZero)=="undefined")
  LeadingZero = true;


CountStepper = Math.ceil(CountStepper);
if (CountStepper == 0)
  CountActive = false;
var SetTimeOutPeriod = (Math.abs(CountStepper)-1)*1000 + 990;
putspan(BackColor, ForeColor);
var dthen = new Date(TargetDate);
var dnow = new Date("December 15, 2012 00:00:00");
if(CountStepper>0)
  ddiff = new Date(dnow-dthen);
else
  ddiff = new Date(dthen-dnow);
gsecs = Math.floor(ddiff.valueOf()/1000);
CountBack(gsecs);
</script>

<link type="text/css" rel="stylesheet" href="<?php echo $GLOBALS['wpframe_plugin_folder']?>/style.css" />

<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_wordpress']?>/wp-includes/js/jquery/jquery.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['wpframe_plugin_folder']?>/script.js"></script>

<?php

	$GLOBALS['JibuPro_client_includes_loaded'] = true; // Make sure that this code is not loaded more than once.

}

if(isset($_REQUEST['action']) and $_REQUEST['action']) { // Quiz Reuslts.

	$score = 0;
	$total = 0;
	$result = '';
	
	
	$rand_q_id=explode('!',$_REQUEST["rand_id"]);
	$result .= "<p>" . t('All the questions in the quiz along with their answers are shown below. Your answers are bolded. The correct answers have a green background while the incorrect ones have a red background.') . "</p>";
       
	foreach ($rand_q_id as $q_id) {
		$result .= "<div class='show-question'>";
		$result .= "<div class='show-question-content'>". stripslashes($all_question[$q_id]->question) . "</div>\n";
		$all_answers = $wpdb->get_results("SELECT ID,answer,correct FROM {$wpdb->prefix}quiz_answer WHERE question_id={$all_question[$q_id]->ID} ORDER BY sort_order");
		
		$correct = false;
		$result .= "<ul>";

		foreach ($all_answers as $ans) {
			$class = 'answer';
			if($ans->ID == $_REQUEST["answer-" . $all_question[$q_id]->ID]) $class .= ' user-answer';
			if($ans->correct == 1) $class .= ' correct-answer';
			if($ans->ID == $_REQUEST["answer-" . $all_question[$q_id]->ID] and $ans->correct == 1) {$correct = true; $score++;}
			$result .= "<li class='$class'><span class='answer'>" . stripslashes($ans->answer) . "</span></li>\n";
		}

		$result .= "</ul>";

		if(!$_REQUEST["answer-" . $all_question[$q_id]->ID]) $result .= "<p class='unanswered'>" . t('Question was not answered') . "</p>";
		$result .= "<p class='explanation'>" . stripslashes($all_question[$q_id]->explanation) . "</p>";
		$result .= "</div>";
		$total++;

	}

	//Find scoring details of this guy.

	$percent = number_format($score / $total * 100, 2);
						//0-9			10-19%,	 	20-29%, 	30-39%			40-49%						
	$all_rating = array(t('Failed'), t('Failed'), t('Failed'), t('Failed'), t('Just Passed'), 

						//																			100%			More than 100%?!

					t('Satisfactory'), t('Competent'), t('Good'), t('Very Good'),t('Excellent'), t('Unbeatable'), t('Cheater'));

	$grade = intval($percent / 10);

	if($percent == 100) $grade = 9;

	if($score == $total) $grade = 10;

	/* $rating = $all_rating[$grade]; */

	

	$quiz_details = $wpdb->get_row($wpdb->prepare("SELECT name,final_screen, description, passed, failed, passed_rate  FROM {$wpdb->prefix}quiz_quiz WHERE ID=%d", $quiz_id));

	

	if( $percent < $quiz_details->passed_rate ) $rating = "Failed";

	else $rating = "Passed";

	

	$replace_these	= array('%%SCORE%%', '%%TOTAL%%', '%%PERCENTAGE%%', '%%GRADE%%', '%%RATING%%', '%%CORRECT_ANSWERS%%', '%%WRONG_ANSWERS%%', '%%QUIZ_NAME%%',	  '%%DESCRIPTION%%');

	$with_these		= array($score,		 $total,	  $percent,			$grade,		 $rating,		$score,					$total-$score,	   stripslashes($quiz_details->name), stripslashes($quiz_details->description));

	

	// Show the results

    $current_user = wp_get_current_user();

	if(!empty($current_user->user_login)){

	$time_zone = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}options WHERE option_name='timezone_string'",''));

	date_default_timezone_set($time_zone->option_value);

    $time= date( 'Y-m-d H:i:s' ); 

	$result_id = $_REQUEST["result_id"];
	//echo "result_id : ".$result_id;

		  $wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}quiz_result SET percent=%d, status='$rating' ,  exam_on='$time' WHERE ID=%d", $percent, $result_id ));

	}
	if($rating=="Failed"){
		$passed=$quiz_details->passed;
		$failed=$quiz_details->failed+1;
	}else{
		$passed=$quiz_details->passed+1;
		$failed=$quiz_details->failed;
	}

if(!empty($current_user->user_login)){	
    $wpdb->get_results($wpdb->prepare("UPDATE {$wpdb->prefix}quiz_quiz SET  passed='$passed',failed='$failed' WHERE ID=%d", $quiz_id ));
}
	print str_replace($replace_these, $with_these, stripslashes($quiz_details->final_screen));
	if($answer_display == 1) print '<hr />' . $result;
} else { // Show The Quiz.
	$single_page = get_option('JibuPro_single_page'); 
?>
<div class="quiz-area <?php if($single_page) echo 'single-page-quiz'; ?>">
<form action="" method="post" class="quiz-form" id="quiz-<?php echo $quiz_id?>">
<?php
$question_count = 1;
$quiz_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}quiz_quiz WHERE ID=%d", $quiz_id));
$current_user = wp_get_current_user(); 
$user=$current_user->user_login;

if(count($all_question)<$quiz_details->no_of_question){ $rand=count($all_question); }
else $rand=$quiz_details->no_of_question;

$rand_index = array_rand($all_question,$rand);
 $num = 1;
foreach($rand_index as $rand_id){
	echo "<div class='JibuPro-question' id='question-$question_count'>";
	echo "<div class='question-content'>".$num++.". ". stripslashes($all_question[$rand_id]->question) . "</div><br />";
	echo "<input type='hidden' name='question_id[]' value='{$all_question[$rand_id]->ID}' />";
	$dans = $wpdb->get_results("SELECT ID,answer,correct FROM {$wpdb->prefix}quiz_answer WHERE question_id={$all_question[$rand_id]->ID} ORDER BY sort_order");
	
	shuffle($dans);
	
	foreach ($dans as $ans) {
		if($answer_display == 2) {
			$answer_class = 'wrong-answer-label';
			if($ans->correct) $answer_class = 'correct-answer-label';
		}

		echo "<input type='radio' name='answer-{$all_question[$rand_id]->ID}' id='answer-id-{$ans->ID}' class='answer answer-$question_count $answer_class' value='{$ans->ID}' />";
		echo "<label for='answer-id-{$ans->ID}' id='answer-label-{$ans->ID}' class='$answer_class answer label-$question_count'><span>" . stripslashes($ans->answer) . "</span></label><br />";
	}

	echo "</div>";
	$question_count++;
}

?><br />
<input type="button" id="next-question" value="<?php e("Next") ?> &gt;"  /> 
<input type="submit" name="action" id="action-button" value="<?php e("Show Results") ?>"  /><br />
<input type="button" id="prev-question" value=" &lt;<?php e("Prev") ?>"  /> 
<input type="hidden" name="quiz_id" id="quiz_id" value="<?php echo  $quiz_id; ?>" />
<input type="hidden" name="result_id" id="result_id" value="" />
<input type="hidden" name="current_user" id="current_user" value="<?php echo $user;  ?>" />
<input type="hidden" name="url" id="url" value="<?php echo $GLOBALS['wpframe_plugin_folder']; ?>" />
<input type="hidden" name="rand_id" id="rand_id" value="<?php echo implode('!',$rand_index); ?>" />
</form>
</div>
<?php 
}//show the quiz

}

}//cek
else{
	echo "<h3>Anda Sudah Pernah Ujian.</h3>";
}

}
?>