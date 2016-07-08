<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>sample</title>
</head>
<body>
    
<?php
header('Content-Type: application/json;charset=UTF-8');
header("Access-Control-Allow-Origin: *");

$link = mysql_connect('localhost', 'user', 'password');
if (!$link) {
    die('接続失敗です。'.mysql_error());
}

$db_selected = mysql_select_db('RStack', $link);
if (!$db_selected){
    die('データベース選択失敗です。'.mysql_error());
}

mysql_set_charset('utf8');

//質問の抽出
$result = mysql_query('SELECT * FROM QList where slideId = '.$_POST["slideId"]);
if (!$result) {
    die('クエリーが失敗しました。'.mysql_error());
}

$questions = array();
while ($row = mysql_fetch_assoc($result)) {
    array_push($questions,array(
        'id' => $row['id'],
        'slideId' => $row['slideId'],
        'keyword' => $row['keyword'],
        'count' => $row['count']
    ));
}

//評価の抽出
$result = mysql_query('SELECT * FROM ScoreList where slideId = '.$_POST["slideId"]);
if (!$result) {
    die('クエリーが失敗しました。'.mysql_error());
}
$row = mysql_fetch_assoc($result);

$slideInfo = array(
    'question' => $questions,
    'satisfaction' => array(
        'ok' => $row['ok'],
        'ng' => $row['ng']
    )
);


//JSON標準出力
$json = json_encode($slideInfo, JSON_UNESCAPED_UNICODE);
echo $json;

$close_flag = mysql_close($link);

if ($close_flag){
    //print('<p>切断に成功しました。</p>');
}
?>

</body>
</html>