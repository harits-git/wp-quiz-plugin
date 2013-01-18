var current_question = 1;
var total_questions = 0;
var mode = "show";

  
       function handleHttpResponse() { 
        
        if (http.readyState == 4) {
              
					var results=http.responseText;
                                          document.getElementById('result_id').value = results;
               
              }
        }
       
        function riseAttemmptConut() {     
            var sId = document.getElementById('quiz_id').value;
            var user = document.getElementById('current_user').value;
			var url = document.getElementById('url').value+"/quiz_action.php?action=count&quiz_id="; // The server-side script
			
			http.open("POST", url + escape(sId)+'&user='+ escape(user), true);
            http.onreadystatechange = handleHttpResponse;
            http.send(null);
        }

function checkAnswer(e) {
	var answered = false;
	
	jQuery("#question-" + current_question + " .answer").each(function(i) {
		if(this.checked) {
			answered = true;
			return true;
		}
	});
	if(!answered) {
		if(!confirm("You did not select any answer. Are you sure you want to continue?")) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	}
	return true;
}

function nextQuestion(e) {
	if(!checkAnswer(e)) return;
	
	jQuery("#question-" + current_question).hide();
	current_question++;
	if(current_question == 2){
		riseAttemmptConut(); 
	}
	jQuery("#prev-question").show();
	jQuery("#question-" + current_question).show();
	
	if(total_questions <= current_question) {
		jQuery("#next-question").hide();
		jQuery("#action-button").show();
	}
}
function prevQuestion(e) {
	if(!checkAnswer(e)) return;
	
	jQuery("#question-" + current_question).hide();
	current_question--;
	if(current_question == 2){
		riseAttemmptConut(); 
	}
	if(current_question == 1){ jQuery("#prev-question").hide(); }
	jQuery("#question-" + current_question).show();
	
	if(total_questions > current_question) {
		jQuery("#next-question").show();
	}
}
// This part is used only if the answers are show on a per question basis.
function showAnswer(e) {
	if(!checkAnswer(e)) return;
	
	if(mode == "next") {
		mode = "show";
		
		jQuery("#question-" + current_question).hide();
		current_question++; 
		jQuery("#question-" + current_question).show();
		jQuery("#show-answer").val("Show Answer");
		return;
	}
	
	mode = "next";
	
	jQuery(".correct-answer-label.label-"+current_question).addClass("correct-answer");
	jQuery(".answer-"+current_question).each(function(i) {
		if(this.checked && this.className.match(/wrong\-answer/)) {
			var number = this.id.toString().replace(/\D/g,"");
			if(number) {
				jQuery("#answer-label-"+number).addClass("user-answer");
			}
		}
	});
	
	if(total_questions <= current_question) {
		jQuery("#show-answer").hide();
		jQuery("#action-button").show();
	} else {
		jQuery("#show-answer").val("Next >");
	}
}

function JibuProInit() {
	jQuery("#question-1").show();
	total_questions = jQuery(".JibuPro-question").length;

	jQuery("#action-button").show(); 
	jQuery("#prev-question").hide();

	if(total_questions == 1) {
		jQuery("#action-button").show();
		jQuery("#next-question").hide();
		jQuery("#prev-question").hide();
		jQuery("#show-answer").hide();
	
	} else {
		jQuery("#next-question").click(nextQuestion);
		jQuery("#prev-question").click(prevQuestion);
		jQuery("#show-answer").click(showAnswer);
	}
	
	jQuery('.single-page-quiz .answer').click(function (e) {
		if(count==0)
		{
			riseAttemmptConut();
			count++;
		}
	});
}

jQuery(document).ready(JibuProInit);
    
function getHTTPObject() {
  var xmlhttp;

  if(window.XMLHttpRequest){
    xmlhttp = new XMLHttpRequest();
  }
  else if (window.ActiveXObject){
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    if (!xmlhttp){
        xmlhttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
}
  return xmlhttp;

 
}
var http = getHTTPObject(); // We create the HTTP Object
var count=0;