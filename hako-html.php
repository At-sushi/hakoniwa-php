<?php
/*******************************************************************

  箱庭諸島２ for PHP

  
  $Id: hako-html.php,v 1.12 2004/08/10 22:00:41 Watson Exp $

*******************************************************************/

if(GZIP == true) {
  // gzip圧縮転送用
  require_once "HTTP/Compress.php";
  $http = new HTTP_Compress;
}

//--------------------------------------------------------------------
class HTML {

  //---------------------------------------------------
  // HTML ヘッダ出力
  //---------------------------------------------------
  function header($data = "") {
    global $init;
    global $PRODUCT_VERSION;

    // 圧縮転送
    if(GZIP == true) {
      global $http;
      $http->start();
    }
    header("X-Product-Version: {$PRODUCT_VERSION}");
    $css = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
    print <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<base href="{$init->imgDir}/">
<meta http-equiv="Content-type" content="text/html; charset=Shift_JIS">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="{$init->cssDir}/{$css}">
<title>{$init->title}</title>
<script type="text/javascript" src="{$init->baseDir}/hako.js"></script>
</head>
<body>

<div id="LinkHeader">
<a href="http://www.bekkoame.ne.jp/‾tokuoka/hakoniwa.html">箱庭諸島スクリプト配布元</a>
<a href="http://scrlab.g-7.ne.jp/">[PHP]</a>　
[<a href="{$GLOBALS['THIS_FILE']}?mode=conf">島の登録・設定変更</a>]
</div>
<hr>

END;
  }
  //---------------------------------------------------
  // HTML フッタ出力
  //---------------------------------------------------
  function footer() {
    global $init;

    print <<<END
<hr>
<div id="LinkFoot">
管理者：{$init->adminName}(<a href="mailto:{$init->adminEmail}">{$init->adminEmail}</a>)<br>
掲示板：(<a href="{$init->urlBbs}">{$init->urlBbs}</a>)<br>
トップページ：(<a href="{$init->urlTopPage}">{$init->urlTopPage}</a>)
</div>
</body>
</html>

END;

    if(GZIP == true) {
      global $http;
      $http->output();
    }
  }
  //---------------------------------------------------
  // 最終更新時刻 ＋ 次ターン更新時刻出力
  //---------------------------------------------------
  function lastModified($hako) {
    global $init;
    $timeString = date("Y年m月d日　H時", $hako->islandLastTime);
    print <<<END
<h2 class="lastModified">最終更新時間 : $timeString
<span style="font-weight: normal;">
(次のターンまで、あと
<script type="text/javascript"> <!--
 var nextTime = $hako->islandLastTime + $init->unitTime;
 remainTime(nextTime);
//-->
</script>
</span>
</h2>

END;
   }
}
//--------------------------------------------------------------------
class HtmlTop extends HTML {
  //---------------------------------------------------
  // ＴＯＰページ
  //---------------------------------------------------
  function main($hako, $data) {
    global $init;

    // 最終更新時刻 ＋ 次ターン更新時刻出力
    $this->lastModified($hako);
    if(empty($data['defaultDevelopeMode']) || $data['defaultDevelopeMode'] == "cgi") {
      $radio = "checked"; $radio2 = "";
    } else {
      $radio = ""; $radio2 = "checked";
    }

    print "<h1>{$init->title}</h1>¥n";
    if(DEBUG == true) {
      print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<input type="hidden" name="mode" value="debugTurn">
<input type="submit" value="ターンを進める">
</form>

END;
  }
    print <<<END
<h2 class='Turn'>ターン$hako->islandTurn</h2>
<hr>
<div id="MyIsland">
<h2>自分の島へ</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
あなたの島の名前は？<br>
<select name="ISLANDID">
$hako->islandList
</select>
<br>
パスワードをどうぞ！！<br>
<input type="password" name="PASSWORD" value="{$data['defaultPassword']}" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="owner">
<input type="radio" name="DEVELOPEMODE" value="cgi" id="cgi" $radio><label for="cgi">通常モード</label>
<input type="radio" name="DEVELOPEMODE" value="java" id="java" $radio2><label for="java">Javaスクリプトモード</label><BR>
<input type="submit" value="開発しに行く">
</form>
</div>
<hr>

<div ID="IslandView">
<h2>諸島の状況</h2>
<p>
島の名前をクリックすると、<strong>観光</strong>することができます。
</p>
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}順位{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}島{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}面積{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}資金{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}食料{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}農場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}工場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}採掘場規模{$init->_tagTH}</th>
</tr>

END;
    for($i = 0; $i < $hako->islandNumber; $i++) {
      $island = $hako->islands[$i];
      $j = $i + 1;
      $id    = $island['id'];
      $pop   = $island['pop'] . $init->unitPop;
      $area  = $island['area'] . $init->unitArea;
      $money = Util::aboutMoney($island['money']);
      $food  = $island['food'] . $init->unitFood;
      $farm  = ($island['farm'] <= 0) ? "保有せず" : $island['farm'] * 10 . $init->unitPop;
      $factory  = ($island['factory'] <= 0) ? "保有せず" : $island['factory'] * 10 . $init->unitPop;
      $mountain = ($island['mountain'] <= 0) ? "保有せず" : $island['mountain'] * 10 . $init->unitPop;
      $comment  = $island['comment'];
      $comment_turn = $island['comment_turn'];
      $monster = '';
      if($island['monster'] > 0) {
        $monster = "<strong class=¥"monster¥">[怪獣{$island['monster']}体]</strong>";
      }
      
      $name = "";
      if($island['absent']  == 0) {
        $name = "{$init->tagName_}{$island['name']}島{$init->_tagName}";
      } else {
        $name = "{$init->tagName2_}{$island['name']}島({$island['absent']}){$init->_tagName2}";
      }
      if(!empty($island['owner'])) {
        $owner = $island['owner'];
      } else {
        $owner = "コメント";
      }
      
      $prize = $island['prize'];
      $prize = $hako->getPrizeList($prize);

      if($init->commentNew > 0 && ($comment_turn + $init->commentNew) > $hako->islandTurn) {
        $comment .= " <span class=¥"new¥">New</span>";
      }
      
      print "<tr>¥n";
      print "<th {$init->bgNumberCell} rowspan=¥"2¥">{$init->tagNumber_}$j{$init->_tagNumber}</th>¥n";
      print "<td {$init->bgNameCell} rowspan=¥"2¥"><a href=¥"{$GLOBALS['THIS_FILE']}?Sight={$id}¥">{$name}</a> {$monster}<br>¥n{$prize}</td>¥n";

      print "<td {$init->bgInfoCell}>$pop</td>¥n";
      print "<td {$init->bgInfoCell}>$area</td>¥n";
      print "<td {$init->bgInfoCell}>$money</td>¥n";
      print "<td {$init->bgInfoCell}>$food</td>¥n";
      print "<td {$init->bgInfoCell}>$farm</td>¥n";
      print "<td {$init->bgInfoCell}>$factory</td>¥n";
      print "<td {$init->bgInfoCell}>$mountain</td>¥n";
      print "</tr>¥n";
      print "<tr>¥n";
      print "<td {$init->bgCommentCell} colspan=¥"7¥">{$init->tagTH_}{$owner}：{$init->_tagTH}$comment</td>¥n";
      print "</tr>¥n";
    }
    print "</table>¥n</div>¥n";
    print "<hr>¥n";
    $this->logPrintTop();
    $this->historyPrint();
  }
  //---------------------------------------------------
  // 島の登録と設定
  //---------------------------------------------------
  function regist(&$hako) {
    global $init;
    $this->newDiscovery($hako->islandNumber);
    $this->changeIslandInfo($hako->islandList);
    $this->changeOwnerName($hako->islandList);
    $this->setStyleSheet();
  }
  //---------------------------------------------------
  // 新しい島を探す
  //---------------------------------------------------
  function newDiscovery($number) {
    global $init;

    print "<div id=¥"NewIsland¥">¥n";
    print "<h2>新しい島を探す</h2>¥n";
    if($number < $init->maxIsland) {
      print <<<END
<form action="{$GLOBALS['THIS_FILE']}" method="post">
どんな名前をつける予定？<br>
<input type="text" name="ISLANDNAME" size="32" maxlength="32">島<br>
あなたのお名前は？(省略可)<br>
<input type="text" name="OWNERNAME" size="32" maxlength="32"><br>
パスワードは？<br>
<input type="password" name="PASSWORD" size="32" maxlength="32"><br>
念のためパスワードをもう一回<br>
<input type="password" name="PASSWORD2" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="new">
<input type="submit" value="探しに行く">
</form>
END;
    } else {
      print "島の数が最大数です・・・現在登録できません。¥n";
    }
    print "</div>¥n";
    print "<hr>¥n";
  }
  //---------------------------------------------------
  // 島の名前とパスワードの変更
  //---------------------------------------------------
  function changeIslandInfo($islandList = "") {
    global $init;
    print <<<END
<div id="ChangeInfo">
<h2>島の名前とパスワードの変更</h2>
<p>
(注意)名前の変更には500億円かかります。
</p>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
どの島ですか？<br>
<select NAME="ISLANDID">
$islandList
</select>
<br>
どんな名前に変えますか？(変更する場合のみ)<br>
<input type="text" name="ISLANDNAME" size="32" maxlength="32">島<br>
パスワードは？(必須)<br>
<input type="password" name="OLDPASS" size="32" maxlength="32"><br>
新しいパスワードは？(変更する時のみ)<br>
<input type="password" name="PASSWORD" size="32" maxlength="32"><br>
念のためパスワードをもう一回(変更する時のみ)<br>
<input type="password" name="PASSWORD2" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="change">
<input type="submit" value="変更する">
</form>
</div>
<hr>

END;
  }
  //---------------------------------------------------
  // オーナー名の変更
  //---------------------------------------------------
  function changeOwnerName($islandList = "") {
    global $init;
    print <<<END
<div id="ChangeOwnerName">
<h2>オーナー名の変更</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
どの島ですか？<br>
<select name="ISLANDID">
{$islandList}
</select>
<br>
新しいオーナー名は？<br>
<input type="text" name="OWNERNAME" size="32" maxlength="32"><br>
パスワードは？<br>
<input type="password" name="OLDPASS" size="32" maxlength="32"><br>
<input type="hidden" name="mode" value="ChangeOwnerName">
<input type="submit" value="変更する">
</form>
</div>
END;
  }
  //---------------------------------------------------
  // スタイルシートの設定
  //---------------------------------------------------
  function setStyleSheet() {
    global $init;
    $styleSheet;
    for($i = 0; $i < count($init->cssList); $i++) {
      $styleSheet .= "<option value=¥"{$init->cssList[$i]}¥">{$init->cssList[$i]}</option>¥n";
    }
    print <<<END
<div id="HakoSkin">
<h2>スタイルシートの設定</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<select name="SKIN">
$styleSheet
</select>
<input type="hidden" name="mode" value="skin">
<input type="submit" value="設定">
</form>
</div>
<hr>

END;
  }
  //---------------------------------------------------
  // 最近の出来事
  //---------------------------------------------------
  function logPrintTop() {
    global $init;
    print "<div id=¥"RecentlyLog¥">¥n";
    print "<h2>最近の出来事</h2>¥n";
    for($i = 0; $i < $init->logTopTurn; $i++) {
      LogIO::logFilePrint($i, 0, 0);
    }
    print "</div>¥n";
  }
  //---------------------------------------------------
  // 発見の記録
  //---------------------------------------------------
  function historyPrint() {
    print "<div id=¥"HistoryLog¥">¥n";
    print "<h2>発見の記録</h2>";
    LogIO::historyPrint();
    print "</div>¥n";
  }
}
//------------------------------------------------------------------
class HtmlMap extends HTML {
  //---------------------------------------------------
  // 開発画面
  //---------------------------------------------------
  function owner($hako, $data) {
    global $init;
    $id     = $data['ISLANDID'];
    $number = $hako->idToNumber[$id];
    $island = $hako->islands[$number];

    // パスワードチェック
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])){
      Error::wrongPassword();
      return;
    }
    $this->tempOwer($hako, $data, $number);
    
    if($init->useBbs) {
      print "<div id=¥"localBBS¥">¥n";
      $this->lbbsHead($island);
      $this->lbbsInputOW($island, $data);
      $this->lbbsContents($island);
      print "</div>¥n";
    }
    $this->islandRecent($island, 1);
  }

  //---------------------------------------------------
  // 観光画面
  //---------------------------------------------------
  function visitor($hako, $data) {
    global $init;
    $id     = $data['ISLANDID'];
    $number = $hako->idToNumber[$id];
    $island = $hako->islands[$number];
    print <<<END
<div align="center">
{$init->tagBig_}{$init->tagName_}「{$island['name']}島」{$init->_tagName}へようこそ！！{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>

END;

    $this->islandInfo($island, $number, 0);
    $this->islandMap($hako, $island, 0);

    if($init->useBbs) {
      print "<div id=¥"localBBS¥">¥n";
      $this->lbbsHead($island);
      $this->lbbsInput($island, $data);
      $this->lbbsContents($island);
      print "</div>¥n";
    }
    $this->islandRecent($island, 0);
  }
  //---------------------------------------------------
  // 島の情報
  //---------------------------------------------------
  function islandInfo($island, $number = 0, $mode = 0) {
    global $init;
    $rank = $number + 1;
    $pop   = $island['pop'] . $init->unitPop;
    $area  = $island['area'] . $init->unitArea;
    $money = ($mode == 0) ? Util::aboutMoney($island['money']) : "{$island['money']}{$init->unitMoney}";
    $food  = $island['food'] . $init->unitFood;
    $farm  = ($island['farm'] <= 0) ? "保有せず" : $island['farm'] * 10 . $init->unitPop;
    $factory  = ($island['factory'] <= 0) ? "保有せず" : $island['factory'] * 10 . $init->unitPop;
    $mountain = ($island['mountain'] <= 0) ? "保有せず" : $island['mountain'] * 10 . $init->unitPop;
    $comment  = $island['comment'];

    print <<<END
<div id="islandInfo">
<table border="1">
<tr>
<th {$init->bgTitleCell}>{$init->tagTH_}順位{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}人口{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}面積{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}資金{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}食料{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}農場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}工場規模{$init->_tagTH}</th>
<th {$init->bgTitleCell}>{$init->tagTH_}採掘場規模{$init->_tagTH}</th>
</tr>
<tr>
<th {$init->bgNumberCell}>{$init->tagNumber_}$rank{$init->_tagNumber}</th>
<td {$init->bgInfoCell}>$pop</td>
<td {$init->bgInfoCell}>$area</td>
<td {$init->bgInfoCell}>$money</td>
<td {$init->bgInfoCell}>$food</td>
<td {$init->bgInfoCell}>$farm</td>
<td {$init->bgInfoCell}>$factory</td>
<td {$init->bgInfoCell}>$mountain</td>
</tr>
<tr>
<td colspan="8" {$init->bgCommentCell}>$comment</td>
</tr>
</table>
</div>

END;
  }
  //---------------------------------------------------
  // 地形出力
  // $mode = 1 -- ミサイル基地なども表示
  //---------------------------------------------------
  function islandMap($hako, $island, $mode = 0) {
    global $init;
    $land      = $island['land'];
    $landValue = $island['landValue'];
    $command   = $island['command'];
    if($mode == 1) {
      for($i = 0; $i < $init->commandMax; $i++) {
        $j = $i + 1;
        $com = $command[$i];
        if($com['kind'] < 20) {
          $comStr[$com['x']][$com['y']] .=
            "[{$j}]{$init->comName[$com['kind']]} ";
        }
      }
    }

    print "<div id=¥"islandMap¥" align=¥"center¥"><table border=¥"1¥"><tr><td>¥n";
    print "<img src=¥"xbar.gif¥" width=¥"400¥" height=¥"16¥" alt=¥"¥"><br>¥n";
    for($y = 0; $y < $init->islandSize; $y++) {
      if($y % 2 == 0) { print "<img src=¥"space{$y}.gif¥" width=¥"16¥" height=¥"32¥" alt=¥"{$y}¥">"; }

      for($x = 0; $x < $init->islandSize; $x++) {
        $hako->landString($land[$x][$y], $landValue[$x][$y], $x, $y, $mode, $comStr[$x][$y]);
      }
      
      if($y % 2 == 1) { print "<img src=¥"space{$y}.gif¥" width=¥"16¥" height=¥"32¥" alt=¥"{$y}¥">"; }

      print "<br>";
    }
    print "<div id=¥"NaviView¥">&shy;</div>";
    print "</td></tr></table></div>¥n";
  }
  //---------------------------------------------------
  // 観光者通信
  //---------------------------------------------------
  function lbbsHead($island) {
    global $init;
    print <<<END
<hr>
<h2>{$init->tagName_}{$island['name']}島{$init->_tagName}観光者通信</h2>

END;
  }
  //---------------------------------------------------
  // 観光者通信 入力部分
  //---------------------------------------------------
  function lbbsInput($island, $data) {
    global $init;
    print <<<END
<div align="center">
<table border="1">
<tr>
<th>名前</th>
<th>内容</th>
<th>動作</th>
</tr>
<tr>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<td><input type="text" size="32" maxlength="32" name="LBBSNAME" value="{$data['defaultName']}"></td>
<td><input type="text" size="80" name="LBBSMESSAGE"></td>
<td>
<input type="hidden" name="mode" value="lbbs">
<input type="hidden" name="lbbsMode" value="0">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="記帳する">
</td>
</form>
</tr>
</table>
</div>

END;
  }
  //---------------------------------------------------
  // 観光者通信 入力部分 オーナ用
  //---------------------------------------------------
  function lbbsInputOW($island, $data) {
    global $init;
    print <<<END
<div align="center">
<table border="1">
<tr>
<th>名前</th>
<th colspan="2">内容</th>
</tr>
<tr>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
<td><input type="text" size="32" maxlength="32" name="LBBSNAME" VALUE="{$data['defaultName']}"></TD>
<td colspan="2"><input type="text" size="80" name="LBBSMESSAGE"></td>
</tr>
<tr>
<th colspan="2">動作</th>
</tr>
<tr>
<td align="right">
<input type="hidden" name="mode" value="lbbs">
<input type="hidden" name="lbbsMode" value="1">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="記帳する">
</td>
</form>
<td align="right">
<form action="{$GLOBALS['THIS_FILE']}" method="post">
番号
<select name="NUMBER">

END;
    
    // 発言番号
    for($i = 0; $i < $init->lbbsMax; $i++) {
      $j = $i + 1;
      print "<option value=¥"{$i}¥">{$j}</option>¥n";
    }
    print <<<END
</select>
<input type="hidden" name="mode" value="lbbs">
<input type="hidden" name="lbbsMode" value="2">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="{$data['DEVELOPEMODE']}">
<input type="submit" value="削除する">
</form>
</td>
</tr>
</table>
</div>

END;
  }
  //---------------------------------------------------
  // 観光者通信 書き込まれた内容を出力
  //---------------------------------------------------
  function lbbsContents($island) {
    global $init;
    $lbbs = $island['lbbs'];
    print <<<END
<div align="center">
<table border="1">
<tr>
<th style="width:3em;">番号</th>
<th>記帳内容</th>
</tr>

END;
    for($i = 0; $i < $init->lbbsMax; $i++) {
      $j = $i + 1;
      $line = $lbbs[$i];
      list($mode, $turn, $message) = split(">", $line);
      print "<tr><th>{$init->tagNumber_}{$j}{$init->_tagNumber}</th>";
      if($mode == 0) {
        // 観光者
        print "<td>{$init->tagLbbsSS_}{$turn} &gt; {$message}{$init->_tagLbbsSS}</td></tr>¥n";
      } else {
        // 島主
        print "<td>{$init->tagLbbsOW_}{$turn} &gt; {$message}{$init->_tagLbbsOW}</td></tr>¥n";
      }
      
    }
    print "</table></div>¥n";
  }
  //---------------------------------------------------
  // 島の近況
  //---------------------------------------------------
  function islandRecent($island, $mode = 0) {
    global $init;
    print "<hr>¥n";
    print "<div id=¥"RecentlyLog¥">¥n";
    print "<h2>{$init->tagName_}{$island['name']}島{$init->_tagName}の近況</h2>¥n";
    for($i = 0; $i < $init->logMax; $i++) {
      LogIO::logFilePrint($i, $island['id'], $mode);
    }
    print "</div>¥n";
  }
  //---------------------------------------------------
  // 開発画面
  //---------------------------------------------------
  function tempOwer($hako, $data, $number = 0) {
    global $init;
    $island = $hako->islands[$number];

    $width  = $init->islandSize * 32 + 50;
    $height = $init->islandSize * 32 + 100;
    $defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;
    print <<<END
<script type="text/javascript">
<!--
var w;
var p = $defaultTarget;
function ps(x, y) {
  document.InputPlan.POINTX.options[x].selected = true;
  document.InputPlan.POINTY.options[y].selected = true;
  return true;
}

function ns(x) {
  document.InputPlan.NUMBER.options[x].selected = true;
  return true;
}

function settarget(part){
  p = part.options[part.selectedIndex].value;
}
function targetopen() {
  w = window.open("{$GLOBALS['THIS_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}


//-->
</script>
<div align="center">
{$init->tagBig_}{$init->tagName_}{$island['name']}島{$init->_tagName}開発計画{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>

END;
    $this->islandInfo($island, $number, 1);
    print <<<END
<div align="center">
<table border="1">
<tr>
<td {$init->bgInputCell}>
<div align="center">
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="submit" value="計画送信">
<hr>
<strong>計画番号</strong>
<select name="NUMBER">

END;
    // 計画番号
    for($i = 0; $i < $init->commandMax; $i++) {
      $j = $i + 1;
      print "<option value=¥"{$i}¥">{$j}</option>";
    }
    print <<<END
</select><br>
<hr>
<strong>開発計画</strong><br>
<select name="COMMAND">

END;
    // コマンド
    for($i = 0; $i < $init->commandTotal; $i++) {
      $kind = $init->comList[$i];
      $cost = $init->comCost[$kind];
      if($cost == 0) {
        $cost = '無料';
      } elseif($cost < 0) {
        $cost  = - $cost;
        $cost .= $init->unitFood;
      } else {
        $cost .= $init->unitMoney;
      }
      if($kind == $data['defaultKind']) {
        $s = 'selected';
      } else {
        $s = '';
      }
      print "<option value=¥"{$kind}¥" {$s}>{$init->comName[$kind]}({$cost})</option>¥n";
    }
    print <<<END
</select>
<hr>
<strong>座標(</strong>
<select name="POINTX">

END;
    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultX']) {
        print "<option value=¥"{$i}¥" selected>{$i}</option>¥n";
      } else {
        print "<option value=¥"{$i}¥">{$i}</option>¥n";
      }
    }
    print "</select>, <select name=¥"POINTY¥">";
    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultY']) {
        print "<option value=¥"{$i}¥" selected>{$i}</option>¥n";
      } else {
        print "<option value=¥"{$i}¥">{$i}</option>¥n";
      }
    }
    print <<<END
</select><strong>)</strong>
<hr>
<strong>数量</strong>
<select name="AMOUNT">

END;
     for($i = 0; $i < 100; $i++)
       print "<option value=¥"{$i}¥">{$i}</option>¥n";

     print <<<END
</select>
<hr>
<strong>目標の島</strong><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();" onkeypress="javascript: targetopen();">
<hr>
<strong>動作</strong><br>
<input type="radio" name="COMMANDMODE" id="insert" value="insert" checked><label for="insert">挿入</label>
<input type="radio" name="COMMANDMODE" id="write" value="write"><label for="write">上書き</label><BR>
<input type="radio" name="COMMANDMODE" id="delete" value="delete"><label for="delete">削除</label>
<hr>
<input type="hidden" name="DEVELOPEMODE" value="cgi">
<input type="submit" value="計画送信">
</form>
</div>
</td>
<td {$init->bgMapCell}>

END;
    $this->islandMap($hako, $island, 1);    // 島の地図、所有者モード
    print <<<END
</td>
<td {$init->bgCommandCell}>
END;
    $command = $island['command'];
    for($i = 0; $i < $init->commandMax; $i++) {
      $this->tempCommand($i, $command[$i], $hako);
    }
    print <<<END
</td>
</tr>
</table>
</div>
<hr>
<div id='CommentBox'>
<h2>コメント更新</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
コメント<input type="text" name="MESSAGE" size="80" value="{$island['comment']}"><br>
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="hidden" name="mode" value="comment">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="DEVELOPEMODE" value="cgi">
<input type="submit" value="コメント更新">
</form>
</div>

END;

  }
  //---------------------------------------------------
  // 入力済みコマンド表示
  //---------------------------------------------------
  function tempCommand($number, $command, $hako) {
    global $init;

    $kind   = $command['kind'];
    $target = $command['target'];
    $x      = $command['x'];
    $y      = $command['y'];
    $arg    = $command['arg'];

    $comName = "{$init->tagComName_}{$init->comName[$kind]}{$init->_tagComName}";
    $point   = "{$init->tagName_}({$x},{$y}){$init->_tagName}";
    $target  = $hako->idToName[$target];
    if(empty($target)) {
      $target = "無人";
    }
    $target = "{$init->tagName_}{$target}島{$init->_tagName}";
    $value = $arg * $init->comCost[$kind];
    if($value == 0) {
      $value = $init->comCost[$kind];
    }
    if($value < 0) {
      $value = -$value;
      $value = "{$value}{$init->unitFood}";
    } else {
      $value = "{$value}{$init->unitMoney}";
    }
    $value = "{$init->tagName_}{$value}{$init->_tagName}";

    $j = sprintf("%02d：", $number + 1);

    print "<a href=¥"javascript:void(0);¥" onclick=¥"ns({$number})¥" onkeypress=¥"ns({$number})¥">{$init->tagNumber_}{$j}{$init->_tagNumber}";

    switch($kind) {
    case $init->comDoNothing:
    case $init->comGiveup:
    case $init->comPropaganda:
      $str = "{$comName}";
      break;
    case $init->comMissileNM:
    case $init->comMissilePP:
    case $init->comMissileST:
    case $init->comMissileLD:
      // ミサイル系
      $n = ($arg == 0) ? '無制限' : "{$arg}発";
      $str = "{$target}{$point}へ{$comName}({$init->tagName_}{$n}{$init->_tagName})";
      break;
    case $init->comSendMonster:
      // 怪獣派遣
      $str = "{$target}へ{$comName}";
      break;
    case $init->comSell:
      // 食料輸出
      $str ="{$comName}{$value}";
      break;
    case $init->comMoney:
    case $init->comFood:
      // 援助
      $str = "{$target}へ{$comName}{$value}";
      break;
    case $init->comDestroy:
      // 掘削
      if($arg != 0) {
        $str = "{$point}で{$comName}(予算{$value})";
      } else {
        $str = "{$point}で{$comName}";
      }
      break;
    case $init->comFarm:
    case $init->comFactory:
    case $init->comMountain:
      // 回数付き
      if($arg == 0) {
        $str = "{$point}で{$comName}";
      } else {
        $str = "{$point}で{$comName}({$arg}回)";
      }      
      break;
    default:
      // 座標付き
      $str = "{$point}で{$comName}";
    }

    print "{$str}</a><br>";
  }
  //---------------------------------------------------
  // 新しく発見した島
  //---------------------------------------------------
  function newIslandHead($name) {
    global $init;
    print <<<END
<div align="center">
{$init->tagBig_}島を発見しました！！{$init->_tagBig}<br>
{$init->tagBig_}{$init->tagName_}「{$name}島」{$init->_tagName}と命名します。{$init->_tagBig}<br>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>
END;
  }
  //---------------------------------------------------
  // 目標捕捉モード
  //---------------------------------------------------
  function printTarget($hako, $data) {
    global $init;
    // idから島番号を取得
    $id     = $data['ISLANDID'];
    $number = $hako->idToNumber[$id];
    // なぜかその島がない場合
    if($number < 0 || $number > $hako->islandNumber) {
      Error::problem();
      return;
    }
    $island = $hako->islands[$number];

print <<<END
<script type="text/javascript">
<!--
function ps(x, y) {
  window.opener.document.InputPlan.POINTX.options[x].selected = true;
  window.opener.document.InputPlan.POINTY.options[y].selected = true;
  return true;
}
//-->
</script>

<div align="center">
{$init->tagBig_}{$init->tagName_}{$island['name']}島{$init->_tagName}{$init->_tagBig}<br>
</div>

END;

    //島の地図
    $this->islandMap($hako, $island, 2);

  }
}
//------------------------------------------------------------------
class HtmlJS extends HtmlMap {
  function header($data = "") {
    global $init;
    global $PRODUCT_VERSION;

    // 圧縮転送
    if(GZIP == true) {
      global $http;
      $http->start();
    }
    header("X-Product-Version: {$PRODUCT_VERSION}");
    $css = (empty($data['defaultSkin'])) ? $init->cssList[0] : $data['defaultSkin'];
    print <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html lang="ja">
<head>
<base href="{$init->imgDir}/">
<meta http-equiv="Content-type" content="text/html; charset=Shift_JIS">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="{$init->cssDir}/{$css}">
<title>{$init->title}</title>
<script type="text/javascript" src="{$init->baseDir}/hako.js"></script>
</head>
<body onload="init()">
<div id="LinkHeader">
<a href="http://www.bekkoame.ne.jp/‾tokuoka/hakoniwa.html">箱庭諸島スクリプト配布元</a>
<a href="http://scrlab.g-7.ne.jp/">[PHP]</a>　
[<a href="{$GLOBALS['THIS_FILE']}?mode=conf">島の登録・設定変更</a>]
</div>
<hr>

END;
  }
  //---------------------------------------------------
  // 開発画面
  //---------------------------------------------------
  function tempOwer($hako, $data, $number = 0) {
    global $init;
    $island = $hako->islands[$number];

    $width  = $init->islandSize * 32 + 50;
    $height = $init->islandSize * 32 + 100;

    // コマンドセット
    $set_com = "";
    $com_max = "";
    for($i = 0; $i < $init->commandMax; $i++) {
      // 各要素の取り出し
      $command  = $island['command'][$i];
      $s_kind   = $command['kind'];
      $s_target = $command['target'];
      $s_x      = $command['x'];
      $s_y      = $command['y'];
      $s_arg    = $command['arg'];

      // コマンド登録
      if($i == $init->commandMax - 1){
        $set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target]¥n";
        $com_max .= "0";
      } else {
        $set_com .= "[$s_kind, $s_x, $s_y, $s_arg, $s_target],¥n";
        $com_max .= "0,";
      }
    }

    //コマンドリストセット
    $l_kind;
    $set_listcom = "";
    $click_com = "";
    $click_com2 = "";
    $All_listCom = 0;
    $com_count = count($init->commandDivido);
    for($m = 0; $m < $com_count; $m++) {
      list($aa,$dd,$ff) = split(",", $init->commandDivido[$m]);
      $set_listcom .= "[ ";
      for($i = 0; $i < $init->commandTotal; $i++) {
        $l_kind = $init->comList[$i];
        $l_cost = $init->comCost[$l_kind];
        if($l_cost == 0) {
          $l_cost = '無料';
        } elseif($l_cost < 0) {
          $l_cost = - $l_cost; $l_cost .= $init->unitFood;
        } else {
          $l_cost .= $init->unitMoney;
        }
        if($l_kind > $dd-1 && $l_kind < $ff+1) {
          $set_listcom .= "[$l_kind, '{$init->comName[$l_kind]}', '{$l_cost}'],¥n";
          if($m == 0){
            $click_com .= "<a href='javascript:void(0);' onclick='cominput(InputPlan, 6, {$l_kind})' onkeypress='cominput(InputPlan, 6, {$l_kind})' style='text-decoration:none'>{$init->comName[$l_kind]}({$l_cost})<¥¥/a><br>¥n";
          } elseif($m == 1) {
            $click_com2 .= "<a href='javascript:void(0);' onclick='cominput(InputPlan, 6, {$l_kind})' onkeypress='cominput(InputPlan, 6, {$l_kind})' style='text-decoration:none'>{$init->comName[$l_kind]}({$l_cost})<¥¥/a><br>¥n";
          }
          $All_listCom++;
        }
        if($l_kind < $ff+1) { next; }
      }
      $bai = strlen($set_listcom);
      $set_listcom = substr($set_listcom, 0, $bai - 2);
      $set_listcom .= " ],¥n";
    }
    $bai = strlen($set_listcom);
    $set_listcom = substr($set_listcom, 0, $bai - 2);
    if(empty($data['defaultKind'])) {
      $default_Kind = 1;
    } else {
      $default_Kind = $data['defaultKind'];
    }

    // 島リストセット
    $set_island = "";
    for($i = 0; $i < $hako->islandNumber; $i++) {
      $l_name = $hako->islands[$i]['name'];
      $l_name = preg_replace("/'/", "¥'", $l_name);
      $l_id = $hako->islands[$i]['id'];
      if($i == $hako->islandNumber - 1){
        $set_island .= "[$l_id, '$l_name']¥n";
      }else{
        $set_island .= "[$l_id, '$l_name'],¥n";
      }
    }
    $defaultTarget = ($init->targetIsland == 1) ? $island['id'] : $hako->defaultTarget;

    print <<<END
<div align="center">
{$init->tagBig_}{$init->tagName_}{$island['name']}島{$init->_tagName}開発計画{$init->_tagBig}<BR>
{$GLOBALS['BACK_TO_TOP']}<br>
</div>
<script type="text/javascript">
<!--
var w;
var p = $defaultTarget;

// ＪＡＶＡスクリプト開発画面配布元
// あっぽー庵箱庭諸島（ http://appoh.execweb.cx/hakoniwa/ ）
// Programmed by Jynichi Sakai(あっぽー)
// ↑ 削除しないで下さい。
var str;
g = [$com_max];
k1 = [$com_max];
k2 = [$com_max];
tmpcom1 = [ [0,0,0,0,0] ];
tmpcom2 = [ [0,0,0,0,0] ];
command = [
$set_com];

comlist = [
$set_listcom
];

islname = [
$set_island];


function init() {
  for(i = 0; i < command.length ;i++) {
    for(s = 0; s < $com_count ;s++) {
      var comlist2 = comlist[s];
      for(j = 0; j < comlist2.length ; j++) {
        if(command[i][0] == comlist2[j][0]) {
          g[i] = comlist2[j][1];
        }
      }
    }
  }
  SelectList('');
  outp();
  str = plchg();
  str = '<font color="blue">■ 送信済み ■<¥¥/font><br>' + str;
  disp(str, "");

}

function cominput(theForm, x, k) {
  a = theForm.NUMBER.options[theForm.NUMBER.selectedIndex].value;
  b = theForm.COMMAND.options[theForm.COMMAND.selectedIndex].value;
  c = theForm.POINTX.options[theForm.POINTX.selectedIndex].value;
  d = theForm.POINTY.options[theForm.POINTY.selectedIndex].value;
  e = theForm.AMOUNT.options[theForm.AMOUNT.selectedIndex].value;
  f = theForm.TARGETID.options[theForm.TARGETID.selectedIndex].value;
  //  if(x == 6){ b = k; menuclose(); }
  if (x == 1 || x == 6){
    for(i = $init->commandMax - 1; i > a; i--) {
      command[i] = command[i-1];
      g[i] = g[i-1];
    }
  } else if(x == 3) {
    for(i = Math.floor(a); i < ($init->commandMax - 1); i++) {
      command[i] = command[i + 1];
      g[i] = g[i+1];
    }
    command[$init->commandMax - 1] = [41, 0, 0, 0, 0];
    g[$init->commandMax - 1] = '資金繰り';
    str = plchg();
    str = '<font color="red"><strong>■ 未送信 ■<¥¥/strong><¥¥/font><br>' + str;
    disp(str,"white");
    outp();
    return true;
  } else if(x == 4) {
    i = Math.floor(a);
    if (i == 0){ return true; }
    i = Math.floor(a);
    tmpcom1[i] = command[i];tmpcom2[i] = command[i - 1];
    command[i] = tmpcom2[i];command[i-1] = tmpcom1[i];
    k1[i] = g[i];k2[i] = g[i - 1];
    g[i] = k2[i];g[i-1] = k1[i];
    ns(--i);
    str = plchg();
    str = '<font color="red"><strong>■ 未送信 ■<¥¥/strong><¥¥/font><br>' + str;
    disp(str,"white");
    outp();
    return true;
  } else if(x == 5) {
    i = Math.floor(a);
    if (i == $init->commandMax - 1){ return true; }
    tmpcom1[i] = command[i];tmpcom2[i] = command[i + 1];
    command[i] = tmpcom2[i];command[i + 1] = tmpcom1[i];
    k1[i] = g[i];k2[i] = g[i + 1];
    g[i] = k2[i];g[i + 1] = k1[i];
    ns(++i);
    str = plchg();
    str = '<font color="red"><strong>■ 未送信 ■<¥¥/strong><¥¥/font><br>' + str;
    disp(str,"white");
    outp();
    return true;
  }

  for(s = 0; s < $com_count; s++) {
    var comlist2 = comlist[s];
    for(i = 0; i < comlist2.length; i++){
      if(comlist2[i][0] == b){
        g[a] = comlist2[i][1];
        break;
      }
    }
  }
  command[a] = [b, c, d, e, f];
  ns(++a);
  str = plchg();
  str = '<font color="red"><b>■ 未送信 ■<¥¥/b><¥¥/font><br>' + str;
  disp(str, "white");
  outp();
  return true;
}
function plchg() {
  strn1 = "";
  for(i = 0; i < $init->commandMax; i++) {
    c = command[i];

    kind = '{$init->tagComName_}' + g[i] + '{$init->_tagComName}';
    x = c[1];
    y = c[2];
    tgt = c[4];
    point = '{$init->tagName_}' + "(" + x + "," + y + ")" + '{$init->_tagName}';
    for(j = 0; j < islname.length ; j++) {
      if(tgt == islname[j][0]){
        tgt = '{$init->tagName_}' + islname[j][1] + "島" + '{$init->_tagName}';
      }
    }
    if(c[0] == $init->comDoNothing || c[0] == $init->comGiveup){ // 資金繰り、島の放棄
      strn2 = kind;
    }else if(c[0] == $init->comMissileNM || // ミサイル関連
             c[0] == $init->comMissilePP ||
             c[0] == $init->comMissileST ||
             c[0] == $init->comMissileLD){
      if(c[3] == 0) {
        arg = "（無制限）";
      } else {
        arg = "（" + c[3] + "発）";
      }
      strn2 = tgt + point + "へ" + kind + arg;
    } else if(c[0] == $init->comSendMonster) { // 怪獣派遣
      strn2 = tgt + "へ" + kind;
    } else if(c[0] == $init->comSell) { // 食料輸出
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 100;
      arg = "（" + arg + "{$init->unitFood}）";
      strn2 = kind + arg;
    } else if(c[0] == $init->comPropaganda) { // 誘致活動
      strn2 = kind;
    } else if(c[0] == $init->comMoney) { // 資金援助
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * {$init->comCost[$init->comMoney]};
      arg = "（" + arg + "{$init->unitMoney}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comFood) { // 食料援助
      if(c[3] == 0){ c[3] = 1; }
      arg = c[3] * 100;
      arg = "（" + arg + "{$init->unitFood}）";
      strn2 = tgt + "へ" + kind + arg;
    } else if(c[0] == $init->comDestroy) { // 掘削
      if(c[3] == 0){
        strn2 = point + "で" + kind;
      } else {
        arg = c[3] * {$init->comCost[$init->comDestroy]};
        arg = "（予¥算" + arg + "{$init->unitMoney}）";
        strn2 = point + "で" + kind + arg;
      }
    } else if(c[0] == $init->comFarm || // 農場、工場、採掘場整備
              c[0] == $init->comFactory ||
              c[0] == $init->comMountain) {
      if(c[3] != 0){
        arg = "（" + c[3] + "回）";
        strn2 = point + "で" + kind + arg;
      }else{
        strn2 = point + "で" + kind;
      }
    }else{
      strn2 = point + "で" + kind;
    }
    tmpnum = '';
    if(i < 9){ tmpnum = '0'; }
    strn1 +=
      '<a style="text-decoration:none;color:000000" HREF="javascript:void(0);" onclick="ns(' + i + ')" onkeypress="ns(' + i + ')"><nobr>' +
        tmpnum + (i + 1) + ':' +
          strn2 + '<¥¥/nobr><¥¥/a><br>¥¥n';
  }
  return strn1;
}

function disp(str,bgclr) {
  if(str==null)  str = "";

  if(document.getElementById){
    document.getElementById("LINKMSG1").innerHTML = str;
    if(bgclr != "")
      document.getElementById("plan").bgColor = bgclr;
  } else if(document.all){
    el = document.all("LINKMSG1");
    el.innerHTML = str;
    if(bgclr != "")
      document.all.plan.bgColor = bgclr;
  } else if(document.layers) {
    lay = document.layers["PARENT_LINKMSG"].document.layers["LINKMSG1"];
    lay.document.open();
    lay.document.write("<font style='font-size:11pt'>"+str+"<¥¥/font>");
    lay.document.close();
    if(bgclr != "")
      document.layers["PARENT_LINKMSG"].bgColor = bgclr;
  }
}

function outp() {
  comary = "";

  for(k = 0; k < command.length; k++){
    comary = comary + command[k][0]
      + " " + command[k][1]
        + " " + command[k][2]
          + " " + command[k][3]
            + " " + command[k][4]
              + " " ;
  }
  document.InputPlan.COMARY.value = comary;
}

function ps(x, y) {
  document.InputPlan.POINTX.options[x].selected = true;
  document.InputPlan.POINTY.options[y].selected = true;
  return true;
}


function ns(x) {
  if (x == $init->commandMax){ return true; }
  document.InputPlan.NUMBER.options[x].selected = true;
  return true;
}

function set_com(x, y, land) {
  com_str = land + " ";
  for(i = 0; i < $init->commandMax; i++) {
    c = command[i];
    x2 = c[1];
    y2 = c[2];
    if(x == x2 && y == y2 && c[0] < 30){
      com_str += "[" + (i + 1) +"]" ;
      kind = g[i];
      if(c[0] == $init->comDestroy){
        if(c[3] == 0){
          com_str += kind;
        } else {
          arg = c[3] * 200;
          arg = "（予¥算" + arg + "{$init->unitMoney}）";
          com_str += kind + arg;
        }
      } else if(c[0] == $init->comFarm ||
                c[0] == $init->comFactory ||
                c[0] == $init->comMountain) {
        if(c[3] != 0){
          arg = "（" + c[3] + "回）";
          com_str += kind + arg;
        } else {
          com_str += kind;
        }
      } else {
        com_str += kind;
      }
      com_str += " ";
    }
  }
  document.InputPlan.COMSTATUS.value= com_str;
}


function SelectList(theForm) {
  var u, selected_ok;
  if(!theForm) { s = '' }
  else { s = theForm.menu.options[theForm.menu.selectedIndex].value; }
  if(s == ''){
    u = 0; selected_ok = 0;
    document.InputPlan.COMMAND.options.length = $All_listCom;
    for (i=0; i<comlist.length; i++) {
      var command = comlist[i];
      for (a=0; a<command.length; a++) {
        comName = command[a][1] + "(" + command[a][2] + ")";
        document.InputPlan.COMMAND.options[u].value = command[a][0];
        document.InputPlan.COMMAND.options[u].text = comName;
        if(command[a][0] == $default_Kind){
          document.InputPlan.COMMAND.options[u].selected = true;
          selected_ok = 1;
        }
        u++;
      }
    }
    if(selected_ok == 0)
      document.InputPlan.COMMAND.selectedIndex = 0;
  } else {
    var command = comlist[s];
    document.InputPlan.COMMAND.options.length = command.length;
    for (i=0; i<command.length; i++) {
      comName = command[i][1] + "(" + command[i][2] + ")";
      document.InputPlan.COMMAND.options[i].value = command[i][0];
      document.InputPlan.COMMAND.options[i].text = comName;
      if(command[i][0] == $default_Kind){
        document.InputPlan.COMMAND.options[i].selected = true;
        selected_ok = 1;
      }
    }
    if(selected_ok == 0)
      document.InputPlan.COMMAND.selectedIndex = 0;
  }
}


function settarget(part){
  p = part.options[part.selectedIndex].value;
}
function targetopen() {
  w = window.open("{$GLOBALS['THIS_FILE']}?target=" + p, "","width={$width},height={$height},scrollbars=1,resizable=1,toolbar=1,menubar=1,location=1,directories=0,status=1");
}

    //-->
</script>
END;

    $this->islandInfo($island, $number, 1);

    print <<<END
<div align="center">
<table border="1">
<tr valign="top">
<td $init->bgInputCell>
<form action="{$GLOBALS['THIS_FILE']}" method="post" name="InputPlan">
<input type="hidden" name="mode" value="command">
<input type="hidden" name="COMARY" value="comary">
<input type="hidden" name="DEVELOPEMODE" value="java">
<div align="center">
<br>
<b>コマンド入力</b><br>
<b>
<a href="javascript:void(0);" onclick="cominput(InputPlan,1)" onkeypress="cominput(InputPlan,1)">挿入</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,2)" onkeypress="cominput(InputPlan,2)">上書き</a>
　<a href="javascript:void(0);" onclick="cominput(InputPlan,3)" onkeypress="cominput(InputPlan,3)">削除</a>
</b>
<hr>
<b>計画番号</b>
<select name="NUMBER">
END;

    // 計画番号
    for($i = 0; $i < $init->commandMax; $i++) {
      $j = $i + 1;
      print "<option value=¥"$i¥">$j</option>¥n";
    }

    if ($HmenuOpen == 'on') {
      $open = "CHECKED";
    }else{
      $open = "";
    }

    print <<<END
</select>
<hr>
<b>開発計画</b>
<br>
<select name="menu" onchange="SelectList(InputPlan)">
<option value="">全種類</option>

END;

    for($i = 0; $i < $com_count; $i++) {
      list($aa, $tmp) = split(",", $init->commandDivido[$i], 2);
      print "<option value=¥"$i¥">{$aa}</option>¥n";
    }
    print <<<END
</select><br>
<select name="COMMAND">
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
<option>　　　　　　　　　　</option>
</select>
<hr>
<b>座標(</b>
<select name="POINTX">

END;

    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultX']) {
        print "<option value=¥"$i¥" selected>$i</option>¥n";
      } else {
        print "<option value=¥"$i¥">$i</option>¥n";
      }
    }

    print "</select>, <select name=¥"POINTY¥">¥n";

    for($i = 0; $i < $init->islandSize; $i++) {
      if($i == $data['defaultY']) {
        print "<option value=¥"$i¥" selected>$i</option>¥n";
      } else {
        print "<option value=¥"$i¥">$i</option>¥n";
      }
    }

    print <<<END
</select><b> )</b>
<hr>
<b>数量</b><select name="AMOUNT">

END;

    // 数量
    for($i = 0; $i < 100; $i++) {
      print "<option value=¥"$i¥">$i</option>¥n";
    }

    print <<<END
</select>
<hr>
<b>目標の島</b><br>
<select name="TARGETID" onchange="settarget(this);">
$hako->targetList
</select>
<input type="button" value="目標捕捉" onClick="javascript: targetopen();" onkeypress="javascript: targetopen();">
<hr>
<b>コマンド移動</b>：
<a href="javascript:void(0);" onclick="cominput(InputPlan,4)" onkeypress="cominput(InputPlan,4)" style="text-decoration:none"> ▲ </a>・・
<a href="javascript:void(0);" onclick="cominput(InputPlan,5)" onkeypress="cominput(InputPlan,5)" style="text-decoration:none"> ▼ </a>
<hr>
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="submit" value="計画送信">
<br>最後に<font color="red">計画送信ボタン</font>を<br>押すのを忘れないように。
</div>
</form>
</td>
<td $init->bgMapCell><div align="center">

END;

    $this->islandMap($hako, $island, 1);    // 島の地図、所有者モード

    $comment = $hako->islands[$number]['comment'];

    print <<<END
</div>
</td>
<td $init->bgCommandCell id="plan">
<ilayer name="PARENT_LINKMSG" width="100%" height="100%">
<layer name="LINKMSG1" width="200"></layer>
<span id="LINKMSG1"></span>
</ilayer>
<br>
</td>
</tr>
</table>
</div>
<hr>
<div id='CommentBox'>
<h2>コメント更新</h2>
<form action="{$GLOBALS['THIS_FILE']}" method="post">
コメント<input type="text" name="MESSAGE" size="80" value="{$island['comment']}"><br>
<input type="hidden" name="PASSWORD" value="{$data['defaultPassword']}">
<input type="hidden" name="mode" value="comment">
<input type="hidden" name="DEVELOPEMODE" value="java">
<input type="hidden" name="ISLANDID" value="{$island['id']}">
<input type="submit" value="コメント更新">
</FORM>
</DIV>
END;

  }
}



class HtmlSetted extends HTML {
  function setSkin() {
    global $init;
    print "{$init->tagBig_}スタイルシートを設定しました。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
  }
  function comment() {
    global $init;
    print "{$init->tagBig_}コメントを更新しました{$init->_tagBig}<hr>";
  }
  function change() {
    global $init;
    print "{$init->tagBig_}変更完了しました{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
  }
  function lbbsDelete() {
    global $init;
    print "{$init->tagBig_}記帳内容を削除しました{$init->_tagBig}<hr>";
  }
  function lbbsAdd() {
    global $init;
    print "{$init->tagBig_}記帳を行いました{$init->_tagBig}<hr>";
  }
  // コマンド削除
  function commandDelete() {
    global $init;
    print "{$init->tagBig_}コマンドを削除しました{$init->_tagBig}<hr>¥n";
  }

  // コマンド登録
  function commandAdd() {
    global $init;
    print "{$init->tagBig_}コマンドを登録しました{$init->_tagBig}<hr>¥n";
  }
}
class Error {
  function wrongPassword() {
    global $init;
    print "{$init->tagBig_}パスワードが違います。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  // hakojima.datがない
  function noDataFile() {
    global $init;
    print "{$init->tagBig_}データファイルが開けません。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function newIslandFull() {
    global $init;
    print "{$init->tagBig_}申し訳ありません、島が一杯で登録できません！！{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function newIslandNoName() {
    global $init;
    print "{$init->tagBig_}島につける名前が必要です。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function newIslandBadName() {
    global $init;
    print "{$init->tagBig_},?()<>¥$とか入ってたり、「無人島」とかいった変な名前はやめましょうよ〜。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function newIslandAlready() {
    global $init;
    print "{$init->tagBig_}その島ならすでに発見されています。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function newIslandNoPassword() {
    global $init;
    print "{$init->tagBig_}パスワードが必要です。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function changeNoMoney() {
    global $init;
    print "{$init->tagBig_}資金不足のため変更できません{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function changeNothing() {
    global $init;
    print "{$init->tagBig_}名前、パスワードともに空欄です{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function problem() {
    global $init;
    print "{$init->tagBig_}問題発生、とりあえず戻ってください。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function lbbsNoMessage() {
    global $init;
    print "{$init->tagBig_}名前または内容の欄が空欄です。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
  function lockFail() {
    global $init;
    print "{$init->tagBig_}同時アクセスエラーです。<BR>ブラウザの「戻る」ボタンを押し、<BR>しばらく待ってから再度お試し下さい。{$init->_tagBig}{$GLOBALS['BACK_TO_TOP']}¥n";
    HTML::footer();
    exit;
  }
}
?>
