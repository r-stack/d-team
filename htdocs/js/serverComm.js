
function sendMessage(){
	var slideNum = document.getElementsByClassName("item active")[0].getElementsByTagName("img")[0].getAttribute("src");
	var testMsg = document.getElementById("queryMsg").value;
	var chatPanel = document.getElementById("chatPanel");
	
	/*creating query message for showing on chat pannel*/
	var qryMsgFrame = document.createElement("div");
	var qryMsg = document.createElement("blockquote");
	qryMsgFrame.setAttribute("class", "clearfix"); 
	qryMsg.setAttribute("class", "you pull-left");
	qryMsg.innerHTML = testMsg;
	
	/*set query on panel*/
	qryMsgFrame.appendChild(qryMsg);
	chatPanel.appendChild(qryMsgFrame);
	
	/*creating response message*/
	var rspMsgFrame = document.createElement("div");
	var rspMsg = document.createElement("blockquote");
	rspMsgFrame.setAttribute("class", "clearfix"); 
	rspMsg.setAttribute("class", "me pull-right");
	rspMsg.innerHTML = "あんたなんかの質問に答えるわけないでしょ";
	
	/*set response on panel*/
	rspMsgFrame.appendChild(rspMsg);
	chatPanel.appendChild(rspMsgFrame);
	
	
}