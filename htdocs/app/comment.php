<?php
include(dirname(__FILE__) . "/apicall.php");

error_reporting(E_ERROR | E_WARNING | E_PARSE);

// リクエストの取得
$json_string = file_get_contents('php://input');
$req_body = json_decode($json_string, true);
$comment = $req_body['comment'];
$page = $req_body['page'];
$token = $req_body['token'];
$user_id = $req_body['user_id'];
$speaker_id = $req_body['speaker_id'];

// USER ID が指定されていない場合は取得する(初めての呼び出し)
if($user_id == NULL){
    $user_id = call_repl_get_user_id();
}
// NTTDATA意味情報抽出APIからニュアンスを取得
$meaning = call_meaning_api($comment); //(return: int 'sence_id', array 'keywords')
$comment_type = null;
// 言語解析の結果が問い合わせ系の場合
if($meaning['sence_id'] == 52 || $meaning['sence_id'] == 53){
    $comment_type = 1;
    // 拾ったキーワードで定形質問する。キーワードが拾えていない場合は「何がわからないか分からない」系質問。
    $comment = count($meaning['keywords'])== 0 ? "分からない。": $meaning['keywords'][0]['surface']."が分からない。";
}
// 好評の感想の場合
else if($meaning['sence_id'] <= 21){
    $comment_type = 2;
}
// 不満の感想の場合
else if($meaning['sence_id'] <= 48){
    $comment_type = 3;
}

// 問い合わせ系(52,53)として意味抽出されたもの以外はそのままのコメントを対話APIに食わせてみる
$response = call_repl_api($user_id, $token, $comment);
//var_dump($response); //■■■■デバッグ用■■■■
$keywords = implode_keyword($meaning['keywords']);

// シナリオで定義してあるものに引っかかればそれで対応されるし、なければ雑談に流す。
$return_token = null;
$debug_pattern = null;
if(preg_match('/★(.+)★/', $response, $matches)){  // 答えるパターン
    $comment_type = 1; // 答えるパターンになる場合は質問としてDBに登録する。
    $response = str_replace('★'.$matches[1].'★', '', $response);
    $response = $response.call_qna_api($matches[1]);
    $debug_pattern = '応える';
    //insert_comment_info($page, $comment_type, $keywords);
}
else if(strpos($response, '■') !== false){  // 再度問い直しループ
    $response = str_replace('■', '', $response);
    $return_token = date('Y-m-d H:i:s');
    $debug_pattern = 'ループ';
}
else{  // パターン以外は雑談で返す
    $response = call_free_talk_api($comment);
    // 好評か不満の場合はDBに登録しておく
    if($comment_type != null){
        //insert_comment_info($page, $comment_type, $keywords);
    }
    $debug_pattern = '雑談';
}

// レスポンス文章を音声合成
$return_voice_url = call_voice_out_api($response, $speaker_id);


// レスポンス作成
header('Content-Type: application/json;charset=UTF-8');
header("Access-Control-Allow-Origin: *");
$json = json_encode(
    array(
        'user_id' => $user_id,
        'token' => $return_token,
        'response' => $response,
        'voice' => $return_voice_url,
        'debug' => $debug_pattern
    ), JSON_UNESCAPED_UNICODE);
echo $json;


// 配列形式のキーワードを空白分割でくっつけるメソッド
function implode_keyword($array){
    $result = "";
    for ($i=0; $i < count($array) ; $i++) {
        $result = $result.$array[$i]['surface'].' ';
    }
    return trim($result);
}


