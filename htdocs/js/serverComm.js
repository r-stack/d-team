var token = null;
var user_id = null;

function play_voice (filename){
	var voiceTag = document.getElementById("ai_voice");
	var voiceSrc = voiceTag.getElementsByTagName("source")[0];
	
	voiceTag.src = filename;
	document.getElementById("ai_voice").play();
	
}

function sendMessage(){

	/*disable button for disable consecutive querying*/
	document.getElementById("msgSend").disabled = true;
	
	var slideNum = document.getElementsByClassName("item active")[0].getElementsByTagName("img")[0].getAttribute("src");
	var queryMsg = document.getElementById("queryMsg").value;
	var chatPanel = document.getElementById("chatPanel");
	
	/*creating query message for showing on chat pannel*/
	var qryMsgFrame = document.createElement("div");
	var qryMsg = document.createElement("blockquote");
	qryMsgFrame.setAttribute("class", "clearfix"); 
	qryMsg.setAttribute("class", "you pull-left");
	qryMsg.innerHTML = queryMsg;
	
	/*set query on panel*/
	qryMsgFrame.appendChild(qryMsg);
	chatPanel.appendChild(qryMsgFrame);
	
	var query = {
		"user_id": user_id,
 		"comment": queryMsg,
 		"page": slideNum.toString(),
		"token": token,
 		"speaker_id": "1"
	}
	
	$.ajax({
		type: "POST",
		url: "app/comment.php",
		data: query,
		dataType: "json" 
	
	
	}).done(function(data, textStatus, jqXHR){
  		/*creating response message*/
		var rspMsgFrame = document.createElement("div");
		var rspMsg = document.createElement("blockquote");
		rspMsgFrame.setAttribute("class", "clearfix"); 
		rspMsg.setAttribute("class", "me pull-right");
		rspMsg.innerHTML = data["response"];
		
		/*update token and user_id*/	
		if (data["token"] != null){
			token = data["token"]
		}
		user_id = data["user_id"];
			
		/*set response on panel*/
		rspMsgFrame.appendChild(rspMsg);
		chatPanel.appendChild(rspMsgFrame);
		
		/*play audio*/
		var audio_path = "cache/" + data["voice"];
		play_voice(audio_path);
	
	}).fail(function (data, textStatus, jqXHR){
		alert(data.status);
		alert(textStatus);
	});
	
	document.getElementById("msgSend").disabled = false;
}
