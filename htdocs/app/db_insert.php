<?php

function insert_comment_info($page, $category, $keyword){

$link = mysql_connect('localhost', 'user', 'password');
if (!$link) {
    die('接続失敗です。'.mysql_error());
}

$db_selected = mysql_select_db('RStack', $link);
if (!$db_selected){
    die('データベース選択失敗です。'.mysql_error());
}

mysql_set_charset('utf8');

if ($category == 1) {
    $query = 'SELECT count FROM QList WHERE slideId = '.$page .' AND keyword = "'.$keyword.'";';
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    
    if ($row['count'] == "") {
        //$query = 'INSERT INTO QList (slideId, keyword, count) VALUES ("'.$page .'", "'.$keyword .'", 1);';
        $query = 'INSERT INTO QList (slideId, keyword) VALUES ("'.$page .'", "'.$keyword .'");';
        $result = mysql_query($query);
    }
    
    //DBにある回数に1つ加算する
    $count = $row['count'] + 1;
    $query = 'UPDATE QList SET count = '.$count .' WHERE slideId = '.$page .' AND keyword = "'.$keyword.'";';
    $result = mysql_query($query);

    //テスト用コード
//    echo "クエリ実行後の回数は、、、、";
//    $query = 'SELECT count FROM QList WHERE slideId = '.$page .' AND keyword = "'.$keyword.'";';
//    $result = mysql_query($query);
//    $row = mysql_fetch_assoc($result);
//    echo $row['count'];
} else
    if ($category == 2) {
    //echo "良い評価でした";
    $query = 'SELECT ok FROM ScoreList WHERE slideId = '.$page .';';
    //echo $query.'<br>';
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    $ok = $row['ok'] + 1;
    $query = 'UPDATE ScoreList SET ok = '.$ok .' WHERE slideId = '.$page .';';
    //echo $query.'<br>';
    $result = mysql_query($query);
} else
    if ($category == 3) {
    //echo "悪い評価でした";
    $query = 'SELECT ng FROM ScoreList WHERE slideId = '.$page .';';
    //echo $query.'<br>';
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    $ng = $row['ng'] + 1;
    $query = 'UPDATE ScoreList SET ng = '.$ng .' WHERE slideId = '.$page .';';
    //echo $query.'<br>';
    $result = mysql_query($query);
} 
    

$close_flag = mysql_close($link);

if ($close_flag){
    //print('<p>切断に成功しました。</p>');
}
}
?>