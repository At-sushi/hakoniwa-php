<?php
/*******************************************************************

  箱庭諸島２ for PHP

  
  $Id: hako-log.php,v 1.2 2004/08/10 22:00:03 Watson Exp $

*******************************************************************/

class Log extends LogIO {

  function discover($name) {
    global $init;
    $this->history("{$init->tagName_}{$name}島{$init->_tagName}が発見される。");
  }
  function changeName($name1, $name2) {
    global $init;
    $this->history("{$init->tagName_}{$name1}島{$init->_tagName}、名称を{$init->tagName_}{$name2}島{$init->_tagName}に変更する。");
  }
  // 受賞
  function prize($id, $name, $pName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が<strong>$pName</strong>を受賞しました。",$id);
    $this->history("{$init->tagName_}{$name}島{$init->_tagName}、<strong>$pName</strong>を受賞");
  }
  // 死滅
  function dead($id, $name) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}から人がいなくなり、<strong>無人島</strong>になりました。", $id);
    $this->history("{$init->tagName_}{$name}島{$init->_tagName}、人がいなくなり<strong>無人島</strong>となる。");
  }
  function doNothing($id, $name, $comName) {
    //global $init;
    //$this->out("{$init->tagName_}{$name}島{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
  }
  // 資金足りない
  function noMoney($id, $name, $comName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、資金不足のため中止されました。",$id);
  }
  // 食料足りない
  function noFood($id, $name, $comName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、備蓄食料不足のため中止されました。",$id);
  }
  // 対象地形の種類による失敗
  function landFail($id, $name, $comName, $kind, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}が<strong>{$kind}</strong>だったため中止されました。",$id);
  }
  // 成功
  function landSuc($id, $name, $comName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
  }
  // 埋蔵金
  function maizo($id, $name, $comName, $value) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}での{$init->tagComName_}{$comName}{$init->_tagComName}中に、<strong>{$value}{$init->unitMoney}もの埋蔵金</strong>が発見されました。",$id);
  }
  function noLandAround($id, $name, $comName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、予定地の{$init->tagName_}{$point}{$init->_tagName}の周辺に陸地がなかったため中止されました。",$id);
  }
  // 油田発見
  function oilFound($id, $name, $point, $comName, $str) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}で<strong>{$str}</strong>の予算をつぎ込んだ{$init->tagComName_}{$comName}{$init->_tagComName}が行われ、<strong>油田が掘り当てられました</strong>。",$id);
  }
  // 油田発見ならず
  function oilFail($id, $name, $point, $comName, $str) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}で<strong>{$str}</strong>の予算をつぎ込んだ{$init->tagComName_}{$comName}{$init->_tagComName}が行われましたが、油田は見つかりませんでした。",$id);
  }
  // 防衛施設、自爆セット
  function bombSet($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>の<strong>自爆装置がセット</strong>されました。",$id);
  }
  // 防衛施設、自爆作動
  function bombFire($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>、{$init->tagDisaster_}自爆装置作動！！{$init->_tagDisaster}",$id);
  }
  // 植林orミサイル基地
  function PBSuc($id, $name, $comName, $point) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
    $this->out("こころなしか、{$init->tagName_}{$name}島{$init->_tagName}の<strong>森</strong>が増えたようです。",$id);
  }
  // ハリボテ
  function hariSuc($id, $name, $comName, $comName2, $point) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$point}{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
    $this->landSuc($id, $name, $comName2, $point);
  }
  // 記念碑、発射
  function monFly($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>が<strong>轟音とともに飛び立ちました</strong>。",$id);
  }
  // ミサイル撃とうとした(or 怪獣派遣しようとした)がターゲットがいない
  function msNoTarget($id, $name, $comName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、目標の島に人が見当たらないため中止されました。",$id);
  }
  // ステルスミサイル撃ったが範囲外
  function msOutS($id, $tId, $name, $tName, $comName, $point) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島$point{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、<strong>領域外の海</strong>に落ちた模様です。",$id, $tId);
    $this->late("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}へ向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、<strong>領域外の海</strong>に落ちた模様です。",$tId);
  }
  // ミサイル撃ったが範囲外
  function msOut($id, $tId, $name, $tName, $comName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、<strong>領域外の海</strong>に落ちた模様です。",$id, $tId);
  }
  // ステルスミサイル撃ったが防衛施設でキャッチ
  function msCaughtS($id, $tId, $name, $tName, $comName, $point, $tPoint) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}地点上空にて力場に捉えられ、<strong>空中爆発</strong>しました。",$id, $tId);
    $this->late("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}へ向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}地点上空にて力場に捉えられ、<strong>空中爆発</strong>しました。",$tId);
  }
  // ミサイル撃ったが防衛施設でキャッチ
  function msCaught($id, $tId, $name, $tName, $comName, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}地点上空にて力場に捉えられ、<strong>空中爆発</strong>しました。",$id, $tId);
  }
  // ステルスミサイル撃ったが効果なし
  function msNoDamageS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に落ちたので被害がありませんでした。",$id, $tId);
    $this->late("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に落ちたので被害がありませんでした。",$tId);
  }  
  // ミサイル撃ったが効果なし
  function msNoDamage($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に落ちたので被害がありませんでした。",$id, $tId);
  }
  // 陸地破壊弾、山に命中
  function msLDMountain($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中。<strong>{$tLname}</strong>は消し飛び、荒地と化しました。",$id, $tId);
  }
  // 陸地破壊弾、海底基地に命中
  function msLDSbase($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}に着水後爆発、同地点にあった<strong>{$tLname}</strong>は跡形もなく吹き飛びました。",$id, $tId);
  }
  // 陸地破壊弾、怪獣に命中
  function msLDMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}に着弾し爆発。陸地は<strong>怪獣{$tLname}</strong>もろとも水没しました。",$id, $tId);
  }
  // 陸地破壊弾、浅瀬に命中
  function msLDSea1($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に着弾。海底がえぐられました。",$id, $tId);
  }
  // 陸地破壊弾、その他の地形に命中
  function msLDLand($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に着弾。陸地は水没しました。",$id, $tId);
  }
  // ステルスミサイル、荒地に着弾
  function msWasteS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に落ちました。",$id, $tId);
    $this->late("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に落ちました。",$tId);
  }
  // 通常ミサイル、荒地に着弾
  function msWaste($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行いましたが、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に落ちました。",$id, $tId);
  }
  // ステルスミサイル、怪獣に命中、硬化中にて無傷
  function msMonNoDamageS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中、しかし硬化状態だったため効果がありませんでした。",$id, $tId);
    $this->out("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中、しかし硬化状態だったため効果がありませんでした。",$tId);
  }
  // 通常ミサイル、怪獣に命中、硬化中にて無傷
  function msMonNoDamage($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中、しかし硬化状態だったため効果がありませんでした。",$id, $tId);
  }
  // ステルスミサイル、怪獣に命中、殺傷
  function msMonKillS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は力尽き、倒れました。",$id, $tId);
    $this->late("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は力尽き、倒れました。", $tId);
  }
  // 通常ミサイル、怪獣に命中、殺傷
  function msMonKill($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は力尽き、倒れました。",$id, $tId);
  }
  // 怪獣の死体
  function msMonMoney($tId, $mName, $value) {
    global $init;
    $this->out("<strong>怪獣{$mName}</strong>の残骸には、<strong>{$value}{$init->unitMoney}</strong>の値が付きました。",$tId);
  }
  // ステルスミサイル、怪獣に命中、ダメージ
  function msMonsterS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は苦しそうに咆哮しました。",$id, $tId);
    $this->late("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は苦しそうに咆哮しました。",$tId);
  }
  // 通常ミサイル、怪獣に命中、ダメージ
  function msMonster($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>怪獣{$tLname}</strong>に命中。<strong>怪獣{$tLname}</strong>は苦しそうに咆哮しました。",$id, $tId);
  }
  // ステルスミサイル通常地形に命中
  function msNormalS($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->secret("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、一帯が壊滅しました。",$id, $tId);
    $this->late("<strong>何者か</strong>が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、一帯が壊滅しました。",$tId);
  }
  // 通常ミサイル通常地形に命中
  function msNormal($id, $tId, $name, $tName, $comName, $tLname, $point, $tPoint) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$point}{$init->_tagName}地点に向けて{$init->tagComName_}{$comName}{$init->_tagComName}を行い、{$init->tagName_}{$tPoint}{$init->_tagName}の<strong>{$tLname}</strong>に命中、一帯が壊滅しました。",$id, $tId);
  }
  // ミサイル撃とうとしたが基地がない
  function msNoBase($id, $name, $comName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で予定されていた{$init->tagComName_}{$comName}{$init->_tagComName}は、<strong>ミサイル設備を保有していない</strong>ために実行できませんでした。",$id);
  }
  // ミサイル難民到着
  function msBoatPeople($id, $name, $achive) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}にどこからともなく<strong>{$achive}{$init->unitPop}もの難民</strong>が漂着しました。{$init->tagName_}{$name}島{$init->_tagName}は快く受け入れたようです。",$id);
  }
  // 怪獣派遣
  function monsSend($id, $tId, $name, $tName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が<strong>人造怪獣</strong>を建造。{$init->tagName_}{$tName}島{$init->_tagName}へ送りこみました。",$id, $tId);
  }
  // 輸出
  function sell($id, $name, $comName, $value) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が<strong>{$value}{$init->unitFood}</strong>の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。",$id);
  }
  // 援助
  function aid($id, $tId, $name, $tName, $comName, $str) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}が{$init->tagName_}{$tName}島{$init->_tagName}へ<strong>{$str}</strong>の{$init->tagComName_}{$comName}{$init->_tagComName}を行いました。",$id, $tId);
  }
  // 誘致活動
  function propaganda($id, $name, $comName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で{$init->tagComName_}{$comName}{$init->_tagComName}が行われました。",$id);
  }
  // 放棄
  function giveup($id, $name) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}は放棄され、<strong>無人島</strong>になりました。",$id);
    $this->history("{$init->tagName_}{$name}島{$init->_tagName}、放棄され<strong>無人島</strong>となる。");
  }
  // 油田からの収入
  function oilMoney($id, $name, $lName, $point, $str) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>から、<strong>{$str}</strong>の収益が上がりました。",$id);
  }
  // 油田枯渇
  function oilEnd($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は枯渇したようです。",$id);
  }
  // 怪獣、防衛施設を踏む
  function monsMoveDefence($id, $name, $lName, $point, $mName) {
    global $init;
    $this->out("<strong>怪獣{$mName}</strong>が{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>へ到達、<strong>{$lName}の自爆装置が作動！！</strong>",$id);
  }
  // 怪獣動く
  function monsMove($id, $name, $lName, $point, $mName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>が<strong>怪獣{$mName}</strong>に踏み荒らされました。",$id);
  }
  // 火災
  function fire($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>が{$init->tagDisaster_}火災{$init->_tagDisaster}により壊滅しました。",$id);
  }
  // 広域被害、海の建設
  function wideDamageSea2($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は跡形もなくなりました。",$id);
  }
  // 広域被害、怪獣水没
  function wideDamageMonsterSea($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の陸地は<strong>怪獣{$lName}</strong>もろとも水没しました。",$id);
  }
  // 広域被害、水没
  function wideDamageSea($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は<strong>水没</strong>しました。",$id);
  }
  // 広域被害、怪獣
  function wideDamageMonster($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>怪獣{$lName}</strong>は消し飛びました。",$id);
  }
  // 広域被害、荒地
  function wideDamageWaste($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は一瞬にして<strong>荒地</strong>と化しました。",$id);
  }
  // 地震発生
  function earthquake($id, $name) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で大規模な{$init->tagDisaster_}地震{$init->_tagDisaster}が発生！！",$id);
  }
  // 地震被害
  function eQDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}地震{$init->_tagDisaster}により壊滅しました。",$id);
  }
  // 飢餓
  function starve($id, $name) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}の{$init->tagDisaster_}食料が不足{$init->_tagDisaster}しています！！",$id);
  }
  // 食料不足被害
  function svDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>に<strong>食料を求めて住民が殺到</strong>。<strong>{$lName}</strong>は壊滅しました。",$id);
  }
  // 津波発生
  function tsunami($id, $name) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}付近で{$init->tagDisaster_}津波{$init->_tagDisaster}発生！！",$id);
  }
  // 津波被害
  function tsunamiDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}津波{$init->_tagDisaster}により崩壊しました。",$id);
  }
  // 怪獣現る
  function monsCome($id, $name, $mName, $point, $lName) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}に<strong>怪獣{$mName}</strong>出現！！{$init->tagName_}{$point}{$init->_tagName}の<strong>{$lName}</strong>が踏み荒らされました。",$id);
  }
  // 地盤沈下発生
  function falldown($id, $name) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}で{$init->tagDisaster_}地盤沈下{$init->_tagDisaster}が発生しました！！",$id);
  }
  // 地盤沈下被害
  function falldownLand($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は海の中へ沈みました。",$id);
  }
  // 台風発生
  function typhoon($id, $name) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$init->_tagName}に{$init->tagDisaster_}台風{$init->_tagDisaster}上陸！！",$id);
  }

  // 台風被害
  function typhoonDamage($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>は{$init->tagDisaster_}台風{$init->_tagDisaster}で飛ばされました。",$id);
  }
  // 隕石、その他
  function hugeMeteo($id, $name, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}地点に{$init->tagDisaster_}巨大隕石{$init->_tagDisaster}が落下！！",$id);
  }
  // 記念碑、落下
  function monDamage($id, $name, $point) {
    global $init;
    $this->out("<strong>何かとてつもないもの</strong>が{$init->tagName_}{$name}島{$point}{$init->_tagName}地点に落下しました！！",$id);
  }
  // 隕石、海
  function meteoSea($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下しました。",$id);
  }
  // 隕石、山
  function meteoMountain($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、<strong>{$lName}</strong>は消し飛びました。",$id);
  }
  // 隕石、海底基地
  function meteoSbase($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、<strong>{$lName}</strong>は崩壊しました。",$id);
  }
  // 隕石、怪獣
  function meteoMonster($id, $name, $lName, $point) {
    global $init;
    $this->out("<strong>怪獣{$lName}</strong>がいた{$init->tagName_}{$name}島{$point}{$init->_tagName}地点に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、陸地は<strong>怪獣{$lName}</strong>もろとも水没しました。",$id);
  }
  // 隕石、浅瀬
  function meteoSea1($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}地点に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、海底がえぐられました。",$id);
  }
  // 隕石、その他
  function meteoNormal($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}地点の<strong>{$lName}</strong>に{$init->tagDisaster_}隕石{$init->_tagDisaster}が落下、一帯が水没しました。",$id);
  }
  // 噴火
  function eruption($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}地点で{$init->tagDisaster_}火山が噴火{$init->_tagDisaster}、<strong>山</strong>が出来ました。",$id);
  }
  // 噴火、浅瀬
  function eruptionSea1($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で陸地になりました。",$id);
  }
  // 噴火、海or海基
  function eruptionSea($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で海底が隆起、浅瀬になりました。",$id);
  }
  // 噴火、その他
  function eruptionNormal($id, $name, $lName, $point) {
    global $init;
    $this->out("{$init->tagName_}{$name}島{$point}{$init->_tagName}地点の<strong>{$lName}</strong>は、{$init->tagDisaster_}噴火{$init->_tagDisaster}の影響で壊滅しました。",$id);
  }
}

?>