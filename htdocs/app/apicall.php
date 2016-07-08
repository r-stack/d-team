<?php



function call_meaning_api($text){
    // リクエストの作成
    $API_KEY = '4464534557594b6c49595533546c375336306973686d4a63594a64426f512f6c36554b326367327a30652e';
    $payload = array(
        'option' => 'SP+K',
        'text' => $text
    );
    $result = post_json_to_json_api('https://api.apigw.smt.docomo.ne.jp/nazukiSA/v1/analyze?APIKEY='. $API_KEY, json_encode($payload));
    $obj = json_decode($result, true);
    $sentence = $obj['page-results'][0]['sentence-results'][0];

    // キーワードを取得する
    $keywords = $sentence['kex-results'];
    // 感情情報が含まれる場合はそれ(id)を返す。
    $sence_id = null;
    if(isset($sentence['intx-results'][0])){
        $sence_id = $sentence['intx-results'][0]['sense-id'];
        // 質問の場合かつ、質問対象が特定できている場合は、キーワードを質問対象で上書きする。
        if(isset($sentence['intx-results'][0]['relations'][0]) && ($sence_id == 52 || $sence_id == 53)){
            $keywords = array($sentence['intx-results'][0]['relations'][0]);
        }
    }
    return array(
        'sence_id' => $sence_id,
        'keywords' => $keywords
    );
}


function call_voice_out_api($text, $speaker_id){
    // リクエストの作成
    $API_KEY = '4464534557594b6c49595533546c375336306973686d4a63594a64426f512f6c36554b326367327a30652e';
    $payload = array(
        'Command' => 'AP_Synth',
        'SpeakerID' => $speaker_id,
        'StyleID' => '1',
        'SpeechRate' => '1.15',
        'AudioFileFormat' => '0',
        'TextData' => $text
    );
    $result = post_json_to_file_api('https://api.apigw.smt.docomo.ne.jp/crayonCorp/v1/textToSpeech?APIKEY='. $API_KEY, json_encode($payload), 'aac');
    
    return $result;
}

// 対話のユーザ識別IDを得る(最初の一回だけ呼ぶ)
function call_repl_get_user_id(){
    $API_KEY = '9ObKMycnBh9pVqVh1AdNj97pXGkN8cMr4jnAAwCJ';
    $payload = array(
        'botId' => 'test'
    );
    $result = post_json_to_json_api('https://api.repl-ai.jp/v1/registration', json_encode($payload), $API_KEY);
    $obj = json_decode($result, true);
    return $obj['appUserId'];
}

function call_repl_api($user_id, $appRecvTime, $text){
    $API_KEY = '9ObKMycnBh9pVqVh1AdNj97pXGkN8cMr4jnAAwCJ';
    $is_initial = $appRecvTime == null? true: false;
    $payload = array(
        'appUserId' => $user_id,
        'botId' => 'test',
        'voiceText' => $text,
        'initTalkingFlag' => $is_initial,
        'initTopicId' => 'test',
        'appRecvTime' => $appRecvTime,
        'appSendTime' => date('Y-m-d H:i:s')
    );
    //var_dump($payload);
    $result = post_json_to_json_api('https://api.repl-ai.jp/v1/dialogue', json_encode($payload), $API_KEY);
    $obj = json_decode($result, true);
    return $obj['systemText']['expression'];
}

// 雑談APIを呼び出す
function call_free_talk_api($comment){
    // リクエストの作成
    $API_KEY = '4464534557594b6c49595533546c375336306973686d4a63594a64426f512f6c36554b326367327a30652e';
    $payload = array(
        'utt' => $comment,
    );
    $result = post_json_to_json_api('https://api.apigw.smt.docomo.ne.jp/dialogue/v1/dialogue?APIKEY='. $API_KEY, json_encode($payload));
    $obj = json_decode($result, true);
    return $obj['utt'];
}

// Q&AAPIを呼び出す
function call_qna_api($keywords){
    // リクエストの作成
    $API_KEY = '4464534557594b6c49595533546c375336306973686d4a63594a64426f512f6c36554b326367327a30652e';
    $keywords = urlencode($keywords);
    $result = get_to_json_api('https://api.apigw.smt.docomo.ne.jp/knowledgeQA/v1/ask?APIKEY='.$API_KEY.'&q='.$keywords);
    $obj = json_decode($result, true);
    return $obj['message']['textForDisplay'];
}

// JSONで戻るAPIに対してGETする。
function get_to_json_api($url){
    // cURL設定
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // cURL実行
    $result=curl_exec($ch);
    curl_close($ch);
    return $result;
}

// JSONで戻るAPIに対してPOSTする。
function post_json_to_json_api($url, $json_string, $api_key_in_header=NULL){
    // cURL設定
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $req_header = array('Content-Type: application/json');
    if($api_key_in_header != NULL){
        array_push($req_header, 'x-api-key: '.$api_key_in_header);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $req_header);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // cURL実行
    $result=curl_exec($ch);
    curl_close($ch);
    return $result;
}

// FILEで戻るAPIに対してPOSTする。
function post_json_to_file_api($url, $json_string, $file_extension){
    // ファイル名をランダムで生成。
    $file_name = uniqid(rand()).'.'.$file_extension;
    $fp = fopen(dirname(__FILE__) . '/../cache/'.$file_name, 'w');
    // cURL設定
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FILE, $fp);
    // cURL実行
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $file_name;
}


