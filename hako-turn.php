<?php
/*******************************************************************

  箱庭諸島２ for PHP

  
  $Id: hako-turn.php,v 1.12 2004/12/14 12:25:38 Watson Exp $

*******************************************************************/

require 'hako-log.php';

class Make {
  //---------------------------------------------------
  // 島の新規作成モード
  //---------------------------------------------------
  function newIsland($hako, $data) {
    global $init;
    $log = new Log;
    if($hako->islandNumber >= $init->maxIsland) {
      HakoError::newIslandFull();
      return;
    }
    if(empty($data['ISLANDNAME'])) {
      HakoError::newIslandNoName();
      return;
    }
    // 名前が正当化チェック
    if(preg_match("[,?()<>$]", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "無人") == 0) {
      HakoError::newIslandBadName();
      return;
    }
    // 名前の重複チェック
    if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
      HakoError::newIslandAlready();
      return;
    }
    // パスワードの存在判定
    if(empty($data['PASSWORD'])) {
      HakoError::newIslandNoPassword();
      return;
    }
    if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
      HakoError::wrongPassword();
      return;
    }
    // 新しい島の番号を決める
    $newNumber = $hako->islandNumber;
    $hako->islandNumber++;
    $island = $this->makeNewIsland();

    // 各種の値を設定
    $island['name']  = htmlspecialchars($data['ISLANDNAME']);
    $island['owner'] = htmlspecialchars($data['OWNERNAME']);
    $island['id']    = $hako->islandNextID;
    $hako->islandNextID++;
    $island['absent'] = $init->giveupTurn - 3;
    $island['comment'] = '(未登録)';
    $island['comment_turn'] = $hako->islandTurn;
    $island['password'] = Util::encode($data['PASSWORD']);

    Turn::estimate($island);
    $hako->islands[$newNumber] = $island;
    $hako->idToNumber[$island['id']] = $newNumber;
    $hako->writeIslandsFile($island['id'], true);

    $log->discover($island['name']);

    $htmlMap = new HtmlMap;
    $htmlMap->newIslandHead($island['name']);
    $htmlMap->islandInfo($island, $newNumber);
    $htmlMap->islandMap($hako, $island, 1, $data);
    
  }
  //---------------------------------------------------
  // 新しい島を作成する
  //---------------------------------------------------
  function makeNewIsland() {
    global $init;
    $command = array();
    // 初期コマンド生成
    for($i = 0; $i < $init->commandMax; $i++) {
      $command[$i] = array (
        'kind'   => $init->comDoNothing,
        'target' => 0,
        'x'      => 0,
        'y'      => 0,
        'arg'    => 0,
        );
    }
    $lbbs = "";
    // 初期掲示板生成
    for($i = 0; $i < $init->lbbsMax; $i++) {
      $lbbs[$i] = "0>>";
    }
    $land = array();
    $landValue = array();
    // 基本形を作成
    for($y = 0; $y < $init->islandSize; $y++) {
      for($x = 0; $x < $init->islandSize; $x++) {
        $land[$x][$y]      = $init->landSea;
        $landValue[$x][$y] = 0;
      }
    }
    
    // 4*4に荒地を配置
    $center = $init->islandSize / 2 - 1;
    for($y = $center -1; $y < $center + 3; $y++) {
      for($x = $center - 1; $x < $center + 3; $x++) {
        $land[$x][$y] = $init->landWaste;
      }
    }
    // 8*8範囲内に陸地を増殖
    for($i = 0; $i < 120; $i++) {
      $x = Util::random(8) + $center - 3;
      $y = Util::random(8) + $center - 3;
      if(Turn::countAround($land, $x, $y, $init->landSea, 7) != 7) {
        // 周りに陸地がある場合、浅瀬にする
        // 浅瀬は荒地にする
        // 荒地は平地にする
        if($land[$x][$y] == $init->landWaste) {
          $land[$x][$y] = $init->landPlains;
          $landValue[$x][$y] = 0;
        } else {
          if($landValue[$x][$y] == 1) {
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          } else {
            $landValue[$x][$y] = 1;
          }
        }
      }
    }
    // 森を作る
    $count = 0;
    while($count < 4) {
      // ランダム座標
      $x = Util::random(4) + $center - 1;
      $y = Util::random(4) + $center - 1;

      // そこがすでに森でなければ、森を作る
      if($land[$x][$y] != $init->landForest) {
        $land[$x][$y] = $init->landForest;
        $landValue[$x][$y] = 5; // 最初は500本
        $count++;
      }
    }
    $count = 0;
    while($count < 2) {
      // ランダム座標
      $x = Util::random(4) + $center - 1;
      $y = Util::random(4) + $center - 1;

      // そこが森か町でなければ、町を作る
      if(($land[$x][$y] != $init->landTown) &&
         ($land[$x][$y] != $init->landForest)) {
        $land[$x][$y] = $init->landTown;
        $landValue[$x][$y] = 5; // 最初は500人
        $count++;
      }
    }

    // 山を作る
    $count = 0;
    while($count < 1) {
      // ランダム座標
      $x = Util::random(4) + $center - 1;
      $y = Util::random(4) + $center - 1;

      // そこが森か町でなければ、町を作る
      if(($land[$x][$y] != $init->landTown) &&
         ($land[$x][$y] != $init->landForest)) {
        $land[$x][$y] = $init->landMountain;
        $landValue[$x][$y] = 0; // 最初は採掘場なし
        $count++;
      }
    }

    // 基地を作る
    $count = 0;
    while($count < 1) {
      // ランダム座標
      $x = Util::random(4) + $center - 1;
      $y = Util::random(4) + $center - 1;

      // そこが森か町か山でなければ、基地
      if(($land[$x][$y] != $init->landTown) &&
         ($land[$x][$y] != $init->landForest) &&
         ($land[$x][$y] != $init->landMountain)) {
        $land[$x][$y] = $init->landBase;
        $landValue[$x][$y] = 0;
        $count++;
      }
    }

    return array (
      'money'	  => $init->initialMoney,
      'food'	  => $init->initialFood,
      'land'	  => $land,
      'landValue' => $landValue,
      'command'	  => $command,
      'prize'	  => '0,0,',
      );    
  }
  //---------------------------------------------------
  // コメント更新
  //---------------------------------------------------
  function commentMain($hako, $data) {
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      HakoError::wrongPassword();
      return;
    }
    // メッセージを更新
    $island['comment'] = htmlspecialchars($data['MESSAGE']);
    $island['comment_turn'] = $hako->islandTurn;
    $hako->islands[$num] = $island;

    // データの書き出し
    $hako->writeIslandsFile();

    // コメント更新メッセージ
    HtmlSetted::Comment();

    
    // owner modeへ
    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    $html->owner($hako, $data);
  }
  //---------------------------------------------------
  // ローカル掲示板モード
  //---------------------------------------------------
  function localBbsMain($hako, $data) {
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // なぜかその島がない場合
    if(empty($data['ISLANDID'])) {
      HakoError::problem();
      return;
    }

    // 削除モードじゃなくて名前かメッセージがない場合
    if($data['lbbsMode'] != 2) {
      if(empty($data['LBBSNAME']) || (empty($data['LBBSMESSAGE']))) {
        HakoError::lbbsNoMessage();
        return;
      }
    }

    // 観光者モードじゃない時はパスワードチェック
    if($data['lbbsMode'] != 0) {
      if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
        // password間違い
        HakoError::wrongPassword();
        return;
      }
    }

    $lbbs = $island['lbbs'];

    // モードで分岐
    if($data['lbbsMode'] == 2) {
      // 削除モード
      // メッセージを前にずらす
      Util::slideBackLbbsMessage($lbbs, $data['NUMBER']);
      HtmlSetted::lbbsDelete();
    } else {
      // 記帳モード
      Util::slideLbbsMessage($lbbs);

      // メッセージ書き込み
      if($data['lbbsMode'] == 0) {
        $message = '0';
      } else {
        $message = '1';
      }
      $bbs_name = "{$hako->islandTurn}：" . htmlspecialchars($data['LBBSNAME']);
      $bbs_message = htmlspecialchars($data['LBBSMESSAGE']);
      $lbbs[0] = "{$message}>{$bbs_name}>{$bbs_message}";

      HtmlSetted::lbbsAdd();
    }
    $island['lbbs'] = $lbbs;
    $hako->islands[$num] = $island;

    // データ書き出し
    $hako->writeIslandsFile($id);

    if($data['DEVELOPEMODE'] == "cgi") {
      $html = new HtmlMap;
    } else {
      $html = new HtmlJS;
    }
    // もとのモードへ
    if($data['lbbsMode'] == 0) {
      $html->visitor($hako, $data);
    } else {
      $html->owner($hako, $data);
    }
  }
  //---------------------------------------------------
  // 情報変更モード
  //---------------------------------------------------
  function changeMain($hako, $data) {
    global $init;
    $log = new Log;
    
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワードチェック
    if(strcmp($data['OLDPASS'], $init->specialPassword) == 0) {
      // 特殊パスワード
      $island['money'] = $init->maxMoney;
      $island['food']  = $init->maxFood;
    } elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
      // password間違い
      HakoError::wrongPassword();
      return;
    }

    // 確認用パスワード
    if(strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0) {
      // password間違い
      HakoError::wrongPassword();
      return;
    }

    if(!empty($data['ISLANDNAME'])) {
      // 名前変更の場合
      // 名前が正当かチェック
      if(preg_match("[,?()<>$]", $data['ISLANDNAME']) || strcmp($data['ISLANDNAME'], "無人") == 0) {
        HakoError::newIslandBadName();
        return;
      }

      // 名前の重複チェック
      if(Util::nameToNumber($hako, $data['ISLANDNAME']) != -1) {
        HakoError::newIslandAlready();
        return;
      }

      if($island['money'] < $init->costChangeName) {
        // 金が足りない
        HakoError::changeNoMoney();
        return;
      }

      // 代金
      if(strcmp($data['OLDPASS'], $init->specialPassword) != 0) {
        $island['money'] -= $init->costChangeName;
      }

      // 名前を変更
      $log->changeName($island['name'], $data['ISLANDNAME']);
      $island['name'] = $data['ISLANDNAME'];
      $flag = 1;
    }

    // password変更の場合
    if(!empty($data['PASSWORD'])) {
      // パスワードを変更
      $island['password'] = Util::encode($data['PASSWORD']);
      $flag = 1;
    }

    if(($flag == 0) && (strcmp($data['PASSWORD'], $data['PASSWORD2']) != 0)) {
      // どちらも変更されていない
      HakoError::changeNothing();
      return;
    }

    $hako->islands[$num] = $island;
    // データ書き出し
    $hako->writeIslandsFile($id);

    // 変更成功
    HtmlSetted::change();
  }
  //---------------------------------------------------
  // オーナ名変更モード
  //---------------------------------------------------
  function changeOwnerName($hako, $data) {
    global $init;

    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];

    // パスワードチェック
    if(strcmp($data['OLDPASS'], $init->specialPassword) == 0) {
      // 特殊パスワード
      $island['money'] = $init->maxMoney;
      $island['food']  = $init->maxFood;
    } elseif(!Util::checkPassword($island['password'], $data['OLDPASS'])) {
      // password間違い
      HakoError::wrongPassword();
      return;
    }
    $island['owner'] = htmlspecialchars($data['OWNERNAME']);
    $hako->islands[$num] = $island;
    // データ書き出し
    $hako->writeIslandsFile($id);

    // 変更成功
    HtmlSetted::change();
  }
  //---------------------------------------------------
  // コマンドモード
  //---------------------------------------------------
  function commandMain($hako, $data) {
    global $init;
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      HakoError::wrongPassword();
      return;
    }

    // モードで分岐
    $command = $island['command'];

    if(strcmp($data['COMMANDMODE'], 'delete') == 0) {
      Util::slideFront($command, $data['NUMBER']);
      HtmlSetted::commandDelete();
    } elseif(($data['COMMAND'] == $init->comAutoPrepare) ||
             ($data['COMMAND'] == $init->comAutoPrepare2)) {
      // フル整地、フル地ならし
      // 座標配列を作る
      $r = Util::makeRandomPointArray();
      $rpx = $r[0];
      $rpy = $r[1];
      $land = $island['land'];
      // コマンドの種類決定
      $kind = $init->comPrepare;
      if($data['COMMAND'] == $init->comAutoPrepare2) {
        $kind = $init->comPrepare2;
      }

      $i = $data['NUMBER'];
      $j = 0;
      while(($j < $init->pointNumber) && ($i < $init->commandMax)) {
        $x = $rpx[$j];
        $y = $rpy[$j];
        if($land[$x][$y] == $init->landWaste) {
          Util::slideBack($command, $data['NUMBER']);
          $command[$data['NUMBER']] = array (
            'kind'	=> $kind,
            'target'	=> 0,
            'x'		=> $x,
            'y'		=> $y,
            'arg'	=> 0,
            );
          $i++;
        }
        $j++;
      }
      HtmlSetted::commandAdd();
    } elseif($data['COMMAND'] == $init->comAutoDelete) {
      // 全消し
      for($i = 0; $i < $init->commandMax; $i++) {
        Util::slideFront($command, 0);
      }
      HtmlSetted::commandDelete();
    } else {
      if(strcmp($data['COMMANDMODE'], 'insert') == 0) {
        Util::slideBack($command, $data['NUMBER']);
      }
      HtmlSetted::commandAdd();
      // コマンドを登録
      $command[$data['NUMBER']] = array (
        'kind'   => $data['COMMAND'],
        'target' => $data['TARGETID'],
        'x'      => $data['POINTX'],
        'y'      => $data['POINTY'],
        'arg'    => $data['AMOUNT'],
        );
    }

    // データの書き出し
    $island['command'] = $command;
    $hako->islands[$num] = $island;
    $hako->writeIslandsFile($island['id']);

    // owner modeへ
    $html = new HtmlMap;
    $html->owner($hako, $data);
  }
}
class MakeJS extends Make {
  //---------------------------------------------------
  // コマンドモード
  //---------------------------------------------------
  function commandMain($hako, $data) {
    global $init;
    $id  = $data['ISLANDID'];
    $num = $hako->idToNumber[$id];
    $island = $hako->islands[$num];
    $name = $island['name'];

    // パスワード
    if(!Util::checkPassword($island['password'], $data['PASSWORD'])) {
      // password間違い
      HakoError::wrongPassword();
      return;
    }
    // モードで分岐
    $command = $island['command'];
    $comary = explode(" " , $data['COMARY']);
    
    for($i = 0; $i < $init->commandMax; $i++) {
      $pos = $i * 5;
      $kind   = $comary[$pos];
      $x      = $comary[$pos + 1];
      $y      = $comary[$pos + 2];
      $arg    = $comary[$pos + 3];
      $target = $comary[$pos + 4];
      // コマンド登録
      if($kind == 0) {
        $kind = $init->comDoNothing;
      }
      $command[$i] = array (
        'kind'   => $kind,
        'x'      => $x,
        'y'      => $y,
        'arg'    => $arg,
        'target' => $target
        );
    }
    HtmlSetted::commandAdd();

    // データの書き出し
    $island['command'] = $command;
    $hako->islands[$num] = $island;
    $hako->writeIslandsFile($island['id']);

    // owner modeへ
    $html = new HtmlJS;
    $html->owner($hako, $data);
  }
  
}
//--------------------------------------------------------------------
class Turn {
  var $log;
  var $rpx;
  var $rpy;
  //---------------------------------------------------
  // ターン進行モード
  //---------------------------------------------------
  function turnMain(&$hako, $data) {
    global $init;
    $this->log = new Log;
    
    // 最終更新時間を更新
    $hako->islandLastTime += $init->unitTime;
    // ログファイルを後ろにずらす
    $this->log->slideBackLogFile();

    // ターン番号
    $hako->islandTurn++;
    $GLOBALS['ISLAND_TURN'] = $hako->islandTurn;
    if($hako->islandNumber == 0) {
      // 島がなければターン数を保存して以降の処理は省く
      // ファイルに書き出し
      $hako->writeIslandsFile();
      return;
    }

    // 座標配列を作る
    $randomPoint = Util::makeRandomPointArray();
    $this->rpx = $randomPoint[0];
    $this->rpy = $randomPoint[1];
    // 順番決め
    $order = Util::randomArray($hako->islandNumber);

    // 収入・消費
    for($i = 0; $i < $hako->islandNumber; $i++) {
      $this->estimate($hako->islands[$order[$i]]);
      $this->income($hako->islands[$order[$i]]);

      // 人口をメモする
      $hako->islands[$order[$i]]['oldPop'] = $hako->islands[$order[$i]]['pop'];
    }
    // コマンド処理
    for($i = 0; $i < $hako->islandNumber; $i++) {
      // 戻り値1になるまで繰り返し
      while($this->doCommand($hako, $hako->islands[$order[$i]]) == 0);
    }
    // 成長および単ヘックス災害
    for($i = 0; $i < $hako->islandNumber; $i++) {
      $this->doEachHex($hako, $hako->islands[$order[$i]]);
    }
    // 島全体処理
    $remainNumber = $hako->islandNumber;
    for($i = 0; $i < $hako->islandNumber; $i++) {
      $island = $hako->islands[$order[$i]];
      $this->doIslandProcess($hako, $island);

      // 死滅判定
      if($island['dead'] == 1) {
        $island['pop'] = 0;
        $remainNumber--;
      } elseif($island['pop'] == 0) {
        $island['dead'] = 1;
        $remainNumber--;
        // 死滅メッセージ
        $tmpid = $island['id'];
        $this->log->dead($tmpid, $island['name']);
        if(is_file("{$init->dirName}/island.{$tmpid}")) {
          unlink("{$init->dirName}/island.{$tmpid}");
        }
        // DB削除処理
        $hako->deleteIsland($tmpid);
      }
      $hako->islands[$order[$i]] = $island;
    }
    // 人口順にソート
    $this->islandSort($hako);
    // ターン杯対象ターンだったら、その処理
    if(($hako->islandTurn % $init->turnPrizeUnit) == 0) {
      $island = $hako->islands[0];
      $this->log->prize($island['id'], $island['name'], "{$hako->islandTurn}{$init->prizeName[0]}");
      $hako->islands[0]['prize'] .= "{$hako->islandTurn},";
    }
    // 島数カット
    $hako->islandNumber = $remainNumber;

    // バックアップターンであれば、書く前にrename
    if(($hako->islandTurn % $init->backupTurn) == 0) {
      $hako->backUp();
    }
    // ファイルに書き出し
    $hako->writeIslandsFile(-1);

    // ログ書き出し
    $this->log->flush();

    // 記録ログ調整
    $this->log->historyTrim();

  }
  //---------------------------------------------------
  // コマンドフェイズ
  //---------------------------------------------------
  function doCommand(&$hako, &$island) {
    global $init;

    $comArray = &$island['command'];
    $command  = $comArray[0];
    Util::slideFront($comArray, 0);
    $island['command'] = $comArray;
    
    $kind   = $command['kind'];
    $target = $command['target'];
    $x      = $command['x'];
    $y      = $command['y'];
    $arg    = $command['arg'];

    $name = $island['name'];
    $id   = $island['id'];
    $land = $island['land'];
    $landValue = $island['landValue'];
    $landKind = &$land[$x][$y];
    $lv   = &$landValue[$x][$y];
    $cost = $init->comCost[$kind];
    $comName = $init->comName[$kind];
    $point = "({$x},{$y})";
    $landName = $this->landName($landKind, $lv);

    $prize = &$island['prize'];

    if($kind == $init->comDoNothing) {
      //$this->log->doNothing($id, $name, $comName);
      $island['money'] += 10;
      $island['absent']++;
      // 自動放棄
      if($island['absent'] >= $init->giveupTurn) {
        $comArray[0] = array (
          'kind'   => $init->comGiveup,
          'target' => 0,
          'x'      => 0,
          'y'      => 0,
          'arg'    => 0
          );
        $island['command'] = $comArray;
      }
      return 1;
    }
    $island['command'] = $comArray;
    $island['absent']  = 0;
    // コストチェック
    if($cost > 0) {
      // 金の場合
      if($island['money'] < $cost) {
        $this->log->noMoney($id, $name, $comName);
        return 0;
      }
    } elseif($cost < 0) {
      // 食料の場合
      if($island['food'] < (-$cost)) {
        $this->log->noFood($id, $name, $comName);
        return 0;
      }
    }

    $returnMode = 1;
    switch($kind) {
    case $init->comPrepare:
    case $init->comPrepare2:
      // 整地、地ならし
      if(($landKind == $init->landSea) ||
         ($landKind == $init->landSbase) ||
         ($landKind == $init->landOil) ||
         ($landKind == $init->landMountain) ||
         ($landKind == $init->landMonster)) {
        // 海、海底基地、油田、山、怪獣は整地できない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }
      // 目的の場所を平地にする
      $land[$x][$y] = $init->landPlains;
      $landValue[$x][$y] = 0;
      $this->log->landSuc($id, $name, '整地', $point);

      // 金を差し引く
      $island['money'] -= $cost;

      if($kind == $init->comPrepare2) {
        // 地ならし
        $island['prepare2']++;

        // ターン消費せず
        $returnMode = 0;
      } else {
        // 整地なら、埋蔵金の可能性あり
        if(Util::random(1000) < $init->disMaizo) {
          $v = 100 + Util::random(901);
          $island['money'] += $v;
          $this->log->maizo($id, $name, $comName, $v);
        }
        $returnMode = 1;
      }
      break;
    case $init->comReclaim:
      // 埋め立て
      if(($landKind != $init->landSea) &&
         ($landKind != $init->landOil) &&
         ($landKind != $init->landSbase)) {
        // 海、海底基地、油田しか埋め立てできない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      // 周りに陸があるかチェック
      $seaCount =
        Turn::countAround($land, $x, $y, $init->landSea, 7) +
          Turn::countAround($land, $x, $y, $init->landOil, 7) +
            Turn::countAround($land, $x, $y, $init->landSbase, 7);

      if($seaCount == 7) {
        // 全部海だから埋め立て不能
        $this->log->noLandAround($id, $name, $comName, $point);

        $returnMode = 0;
        break;
      }

      if(($landKind == $init->landSea) && ($lv == 1)) {
        // 浅瀬の場合
        // 目的の場所を荒地にする
        $land[$x][$y] = $init->landWaste;
        $landValue[$x][$y] = 0;
        $this->log->landSuc($id, $name, $comName, $point);
        $island['area']++;

        if($seaCount <= 4) {
          // 周りの海が3ヘックス以内なので、浅瀬にする

          for($i = 1; $i < 7; $i++) {
            $sx = $x + $init->ax[$i];
            $sy = $y + $init->ay[$i];

            // 行による位置調整
            if((($sy % 2) == 0) && (($y % 2) == 1)) {
              $sx--;
            }

            if(($sx < 0) || ($sx >= $init->islandSize) ||
               ($sy < 0) || ($sy >= $init->islandSize)) {
            } else {
              // 範囲内の場合
              if($land[$sx][$sy] == $init->landSea) {
                $landValue[$sx][$sy] = 1;
              }
            }
          }
        }
      } else {
        // 海なら、目的の場所を浅瀬にする
        $land[$x][$y] = $init->landSea;
        $landValue[$x][$y] = 1;
        $this->log->landSuc($id, $name, $comName, $point);
      }

      // 金を差し引く
      $island['money'] -= $cost;
      $returnMode =  1;
      break;

    case $init->comDestroy:
      // 掘削
      if(($landKind == $init->landSbase) ||
         ($landKind == $init->landOil) ||
         ($landKind == $init->landMonster)) {
        // 海底基地、油田、怪獣は掘削できない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      if(($landKind == $init->landSea) && ($lv == 0)) {
        // 海なら、油田探し
        // 投資額決定
        if($arg == 0) { $arg = 1; }

        $value = min($arg * ($cost), $island['money']);
        $str = "{$value}{$init->unitMoney}";
        $p = round($value / $cost);
        $island['money'] -= $value;

        // 見つかるか判定
        if($p > Util::random(100)) {
          // 油田見つかる
          $this->log->oilFound($id, $name, $point, $comName, $str);
          $land[$x][$y] = $init->landOil;
          $landValue[$x][$y] = 0;
        } else {
          // 無駄撃ちに終わる
          $this->log->oilFail($id, $name, $point, $comName, $str);
        }
        $returnMode = 1;
        break;
      }

      // 目的の場所を海にする。山なら荒地に。浅瀬なら海に。
      if($landKind == $init->landMountain) {
        $land[$x][$y] = $init->landWaste;
        $landValue[$x][$y] = 0;
      } elseif($landKind == $init->landSea) {
        $landValue[$x][$y] = 0;
      } else {
        $land[$x][$y] = $init->landSea;
        $landValue[$x][$y] = 1;
        $island['area']--;
      }
      $this->log->landSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;

      $returnMode = 1;
      break;

    case $init->comSellTree:
      // 伐採
      if($landKind != $init->landForest) {
        // 森以外は伐採できない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      // 目的の場所を平地にする
      $land[$x][$y] = $init->landPlains;
      $landValue[$x][$y] = 0;
      $this->log->landSuc($id, $name, $comName, $point);

      // 売却金を得る
      $island['money'] += $init->treeValue * $lv;

      $returnMode = 1;
      break;
    case $init->comPlant:
    case $init->comFarm:
    case $init->comFactory:
    case $init->comBase:
    case $init->comMonument:
    case $init->comHaribote:
    case $init->comDbase:
      // 地上建設系
      if(!
         (($landKind == $init->landPlains) ||
          ($landKind == $init->landTown)   ||
          (($landKind == $init->landMonument) && ($kind == $init->comMonument)) ||
          (($landKind == $init->landFarm)     && ($kind == $init->comFarm))     ||
          (($landKind == $init->landFactory)  && ($kind == $init->comFactory))  ||
          (($landKind == $init->landDefence)  && ($kind == $init->comDbase)))) {
        // 不適当な地形
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      // 種類で分岐
      switch($kind) {
      case $init->comPlant:
        // 目的の場所を森にする。
        $land[$x][$y] = $init->landForest;
        $landValue[$x][$y] = 1; // 木は最低単位
        $this->log->PBSuc($id, $name, $comName, $point);
        break;

      case $init->comBase:
        // 目的の場所をミサイル基地にする。
        $land[$x][$y] = $init->landBase;
        $landValue[$x][$y] = 0; // 経験値0
        $this->log->PBSuc($id, $name, $comName, $point);
        break;

      case $init->comHaribote:
        // 目的の場所をハリボテにする
        $land[$x][$y] = $init->landHaribote;
        $landValue[$x][$y] = 0;
        $this->log->hariSuc($id, $name, $comName, $init->comName[$init->comDbase], $point);
        break;

      case $init->comFarm:
        // 農場
        if($landKind == $init->landFarm) {
          // すでに農場の場合
          $landValue[$x][$y] += 2; // 規模 + 2000人
          if($landValue[$x][$y] > 50) {
            $landValue[$x][$y] = 50; // 最大 50000人
          }
        } else {
          // 目的の場所を農場に
          $land[$x][$y] = $init->landFarm;
          $landValue[$x][$y] = 10; // 規模 = 10000人
        }
        $this->log->landSuc($id, $name, $comName, $point);
        break;

      case $init->comFactory:
        // 工場
        if($landKind == $init->landFactory) {
          // すでに工場の場合
          $landValue[$x][$y] += 10; // 規模 + 10000人
          if($landValue[$x][$y] > 100) {
            $landValue[$x][$y] = 100; // 最大 100000人
          }
        } else {
          // 目的の場所を工場に
          $land[$x][$y] = $init->landFactory;
          $landValue[$x][$y] = 30; // 規模 = 10000人
        }
        $this->log->landSuc($id, $name, $comName, $point);
        break;
        
      case $init->comDbase:
        // 防衛施設
        if($landKind == $init->landDefence) {
          // すでに防衛施設の場合
          $landValue[$x][$y] = 1; // 自爆装置セット
          $this->log->bombSet($id, $name, $landName, $point);
        } else {
          // 目的の場所を防衛施設に
          $land[$x][$y] = $init->landDefence;
          $landValue[$x][$y] = 0;
          $this->log->landSuc($id, $name, $comName, $point);
        }
        break;
        
      case $init->comMonument:
        // 記念碑
        if($landKind == $init->landMonument) {
          // すでに記念碑の場合
          // ターゲット取得
          $tn = $hako->idToNumber[$target];
          if($tn != 0 && empty($tn)) {
            // ターゲットがすでにない
            // 何も言わずに中止

            $returnMode = 0;
            break;
          }

          $hako->islands[$tn]['bigmissile']++;

          // その場所は荒地に
          $land[$x][$y] = $init->landWaste;
          $landValue[$x][$y] = 0;
          $this->log->monFly($id, $name, $landName, $point);
        } else {
          // 目的の場所を記念碑に
          $land[$x][$y] = $init->landMonument;
          if($arg >= $init->monumentNumber) {
            $arg = 0;
          }
          $landValue[$x][$y] = $arg;
          $this->log->landSuc($id, $name, $comName, $point);
        }
        break;
      }

      // 金を差し引く
      $island['money'] -= $cost;

      // 回数付きなら、コマンドを戻す
      if(($kind == $init->comFarm) ||
         ($kind == $init->comFactory)) {
        if($arg > 1) {
          $arg--;
          Util::slideBack($comArray, 0);
          $comArray[0] = array (
            'kind'   => $kind,
            'target' => $target,
            'x'      => $x,
            'y'      => $y,
            'arg'    => $arg
            );
        }
      }

      $returnMode = 1;
      break;
      // ここまで地上建設系
    case $init->comMountain:
      // 採掘場
      if($landKind != $init->landMountain) {
        // 山以外には作れない
        $this->log->landFail($id, $name, $comName, $landName, $point);

        $returnMode = 0;
        break;
      }

      $landValue[$x][$y] += 5; // 規模 + 5000人
      if($landValue[$x][$y] > 200) {
        $landValue[$x][$y] = 200; // 最大 200000人
      }
      $this->log->landSuc($id, $name, $comName, $point);

      // 金を差し引く
      $island['money'] -= $cost;
      if($arg > 1) {
        $arg--;
        Util::slideBack($comArray, 0);
        $comArray[0] = array (
          'kind'   => $kind,
          'target' => $target,
          'x'      => $x,
          'y'      => $y,
          'arg'    => $arg,
          );
      }
      $returnMode = 1;
      break;

    case $init->comSbase:
      // 海底基地
      if(($landKind != $init->landSea) || ($lv != 0)){
        // 海以外には作れない
        $this->log->landFail($id, $name, $comName, $landName, $point);
        $returnMode = 0;
        break;
      }

      $land[$x][$y] = $init->landSbase;
      $landValue[$x][$y] = 0; // 経験値0
      $this->log->landSuc($id, $name, $comName, '(?, ?)');

      // 金を差し引く
      $island['money'] -= $cost;
      $returnMode = 1;
      break;

    case $init->comMissileNM:
    case $init->comMissilePP:
    case $init->comMissileST:
    case $init->comMissileLD:
      // ミサイル系
      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      if($tn != 0 && empty($tn)) {
        // ターゲットがすでにない
        $this->log->msNoTarget($id, $name, $comName);

        $returnMode = 0;
        break;
      }

      $flag = 0;
      if($arg == 0) {
        // 0の場合は撃てるだけ
        $arg = 10000;
      }

      // 事前準備
      $tIsland = &$hako->islands[$tn];
      $tName   = &$tIsland['name'];
      $tLand   = &$tIsland['land'];
      $tLandValue = &$tIsland['landValue'];
      // 難民の数
      $boat = 0;

      // 誤差
      if($kind == $init->comMissilePP) {
        $err = 7;
      } else {
        $err = 19;
      }

      $bx = $by = 0;
      // 金が尽きるか指定数に足りるか基地全部が撃つまでループ
      while(($arg > 0) &&
            ($island['money'] >= $cost)) {
        // 基地を見つけるまでループ
        while($count < $init->pointNumber) {
          $bx = $this->rpx[$count];
          $by = $this->rpy[$count];
          if(($land[$bx][$by] == $init->landBase) ||
             ($land[$bx][$by] == $init->landSbase)) {
            break;
          }
          $count++;
        }
        if($count >= $init->pointNumber) {
          // 見つからなかったらそこまで
          break;
        }
        // 最低一つ基地があったので、flagを立てる
        $flag = 1;
        // 基地のレベルを算出
        $level = Util::expToLevel($land[$bx][$by], $landValue[$bx][$by]);
        // 基地内でループ
        while(($level > 0) &&
              ($arg > 0) &&
              ($island['money'] > $cost)) {
          // 撃ったのが確定なので、各値を消耗させる
          $level--;
          $arg--;
          $island['money'] -= $cost;

          // 着弾点算出
          $r = Util::random($err);
          $tx = $x + $init->ax[$r];
          $ty = $y + $init->ay[$r];
          if((($ty % 2) == 0) && (($y % 2) == 1)) {
            $tx--;
          }

          // 着弾点範囲内外チェック
          if(($tx < 0) || ($tx >= $init->islandSize) ||
             ($ty < 0) || ($ty >= $init->islandSize)) {
            // 範囲外
            if($kind == $init->comMissileST) {
              // ステルス
              $this->log->msOutS($id, $target, $name, $tName, $comName, $point);
            } else {
              // 通常系
              $this->log->msOut($id, $target, $name, $tName, $comName, $point);
            }
            continue;
          }

          // 着弾点の地形等算出
          $tL  = $tLand[$tx][$ty];
          $tLv = $tLandValue[$tx][$ty];
          $tLname = $this->landName($tL, $tLv);
          $tPoint = "({$tx}, {$ty})";

          // 防衛施設判定
          $defence = 0;
          if($defenceHex[$id][$tx][$ty] == 1) {
            $defence = 1;
          } elseif($defenceHex[$id][$tx][$ty] == -1) {
            $defence = 0;
          } else {
            if($tL == $init->landDefence) {
              // 防衛施設に命中
              // フラグをクリア
              for($i = 0; $i < 19; $i++) {
                $sx = $tx + $init->ax[$i];
                $sy = $ty + $init->ay[$i];

                // 行による位置調整
                if((($sy % 2) == 0) && (($ty % 2) == 1)) {
                  $sx--;
                }

                if(($sx < 0) || ($sx >= $init->islandSize) ||
                   ($sy < 0) || ($sy >= $init->islandSize)) {
                  // 範囲外の場合何もしない
                } else {
                  // 範囲内の場合
                  $defenceHex[$id][$sx][$sy] = 0;
                }
              }
            } elseif(Turn::countAround($tLand, $tx, $ty, $init->landDefence, 19)) {
              $defenceHex[$id][$tx][$ty] = 1;
              $defence = 1;
            } else {
              $defenceHex[$id][$tx][$ty] = -1;
              $defence = 0;
            }
          }

          if($defence == 1) {
            // 空中爆破
            if($kind == $init->comMissileST) {
              // ステルス
              $this->log->msCaughtS($id, $target, $name, $tName,$comName, $point, $tPoint);
            } else {
              // 通常系
              $this->log->msCaught($id, $target, $name, $tName, $comName, $point, $tPoint);
            }
            continue;
          }

          // 「効果なし」hexを最初に判定
          if((($tL == $init->landSea) && ($tLv == 0))|| // 深い海
             ((($tL == $init->landSea) ||    // 海または・・・
               ($tL == $init->landSbase) ||  // 海底基地または・・・
               ($tL == $init->landMountain)) // 山で・・・
              && ($kind != $init->comMissileLD))) { // 陸破弾以外
            // 海底基地の場合、海のフリ
            if($tL == $init->landSbase) {
              $tL = $init->landSea;
            }
            $tLname = $this->landName($tL, $tLv);

            // 無効化
            if($kind == $init->comMissileST) {
              // ステルス
              $this->log->msNoDamageS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
            } else {
              // 通常系
              $this->log->msNoDamage($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
            }
            continue;
          }

          // 弾の種類で分岐
          if($kind == $init->comMissileLD) {
            // 陸地破壊弾
            switch($tL) {
            case $init->landMountain:
              // 山(荒地になる)
              $this->log->msLDMountain($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              // 荒地になる
              $tLand[$tx][$ty] = $init->landWaste;
              $tLandValue[$tx][$ty] = 0;
              continue 2;

            case $init->landSbase:
              // 海底基地
              $this->log->msLDSbase($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              break;
              
            case $init->landMonster:
              // 怪獣
              $this->log->msLDMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              break;
              
            case $init->landSea:
              // 浅瀬
              $this->log->msLDSea1($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              break;
              
            default:
              // その他
              $this->log->msLDLand($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
            }

            // 経験値
            if($tL == $init->landTown) {
              if(($land[$bx][$by] == $init->landBase) ||
                 ($land[$bx][$by] == $init->landSbase)) {
                // まだ基地の場合のみ
                $landValue[$bx][$by] += round($tLv / 20);
                if($landValue[$bx][$by] > $init->maxExpPoint) {
                  $landValue[$bx][$by] = $init->maxExpPoint;
                }
              }
            }

            // 浅瀬になる
            $tLand[$tx][$ty] = $init->landSea;
            $tIsland['area']--;
            $tLandValue[$tx][$ty] = 1;

            // でも油田、浅瀬、海底基地だったら海
            if(($tL == $init->landOil) ||
               ($tL == $init->landSea) ||
               ($tL == $init->landSbase)) {
              $tLandValue[$tx][$ty] = 0;
            }
          } else {
            // その他ミサイル
            if($tL == $init->landWaste) {
              // 荒地(被害なし)
              if($kind == $init->comMissileST) {
                // ステルス
                $this->log->msWasteS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              } else {
                // 通常
                $this->log->msWaste($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              }
            } elseif($tL == $init->landMonster) {
              // 怪獣
              $monsSpec = Util::monsterSpec($tLv);
              $special = $init->monsterSpecial[$monsSpec['kind']];

              // 硬化中?
              if((($special == 3) && (($hako->islandTurn % 2) == 1)) ||
                 (($special == 4) && (($hako->islandTurn % 2) == 0))) {
                // 硬化中
                if($kind == $init->comMissileST) {
                  // ステルス
                  $this->log->msMonNoDamageS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                } else {
                  // 通常弾
                  $this->log->msMonNoDamage($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                }
                continue;
              } else {
                // 硬化中じゃない
                if($monsSpec['hp'] == 1) {
                  // 怪獣しとめた
                  if(($land[$bx][$by] == $init->landBase) ||
                     ($land[$bx][$by] == $init->landSbase)) {
                    // 経験値
                    $landValue[$bx][$by] += $init->monsterExp[$monsSpec['kind']];
                    if($landValue[$bx][$by] > $init->maxExpPoint) {
                      $landValue[$bx][$by] = $init->maxExpPoint;
                    }
                  }

                  if($kind == $init->comMissileST) {
                    // ステルス
                    $this->log->msMonKillS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                  } else {
                    // 通常
                    $this->log->msMonKill($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                  }

                  // 収入
                  $value = $init->monsterValue[$monsSpec['kind']];
                  if($value > 0) {
                    $tIsland['money'] += $value;
                    $this->log->msMonMoney($target, $tLname, $value);
                  }

                  // 賞関係
//                  $prize = $island['prize'];
                  list($flags, $monsters, $turns) = explode(",", $prize, 3);
                  $v = 1 << $monsSpec['kind'];
                  $monsters |= $v;

                  $prize = "{$flags},{$monsters},{$turns}";
//                  $island['prize'] = "{$flags},{$monsters},{$turns}";
                } else {
                  // 怪獣生きてる
                  if($kind == $init->comMissileST) {
                    // ステルス
                    $this->log->msMonsterS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                  } else {
                    // 通常
                    $this->log->msMonster($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
                  }
                  // HPが1減る
                  $tLandValue[$tx][$ty]--;
                  continue;
                }

              }
            } else {
              // 通常地形
              if($kind == $init->comMissileST) {
                // ステルス
                $this->log->msNormalS($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              } else {
                // 通常
                $this->log->msNormal($id, $target, $name, $tName, $comName, $tLname, $point, $tPoint);
              }
            }
            // 経験値
            if($tL == $init->landTown) {
              if(($land[$bx][$by] == $init->landBase) ||
                 ($land[$bx][$by] == $init->landSbase)) {
                $landValue[$bx][$by] += round($tLv / 20);
                $boat += $tLv; // 通常ミサイルなので難民にプラス
                if($landValue[$bx][$by] > $init->maxExpPoint) {
                  $landValue[$bx][$by] = $init->maxExpPoint;
                }
              }
            }

            // 荒地になる
            $tLand[$tx][$ty] = $init->landWaste;
            $tLandValue[$tx][$ty] = 1; // 着弾点
            // でも油田だったら海
            if($tL == $init->landOil) {
              $tLand[$tx][$ty] = $init->landSea;
              $tLandValue[$tx][$ty] = 0;
            }
          }
        }

        // カウント増やしとく
        $count++;
      }


      if($flag == 0) {
        // 基地が一つも無かった場合
        $this->log->msNoBase($id, $name, $comName);

        $returnMode = 0;
        break;
      }
      
      $tIsland['land'] = $tLand;
      $tIsland['landValue'] = $tLandValue;
      unset($hako->islands[$tn]);
      $hako->islands[$tn] = $tIsland;
      
      
      // 難民判定
      $boat = round($boat / 2);
      if(($boat > 0) && ($id != $target) && ($kind != $init->comMissileST)) {
        // 難民漂着
        $achive = 0; // 到達難民
        for($i = 0; ($i < $init->pointNumber && $boat > 0); $i++) {
          $bx = $this->rpx[$i];
          $by = $this->rpy[$i];
          if($land[$bx][$by] == $init->landTown) {
            // 町の場合
            $lv = $landValue[$bx][$by];
            if($boat > 50) {
              $lv += 50;
              $boat -= 50;
              $achive += 50;
            } else {
              $lv += $boat;
              $achive += $boat;
              $boat = 0;
            }
            if($lv > 200) {
              $boat += ($lv - 200);
              $achive -= ($lv - 200);
              $lv = 200;
            }
            $landValue[$bx][$by] = $lv;
          } elseif($land[$bx][$by] == $init->landPlains) {
            // 平地の場合
            $land[$bx][$by] = $init->landTown;;
            if($boat > 10) {
              $landValue[$bx][$by] = 5;
              $boat -= 10;
              $achive += 10;
            } elseif($boat > 5) {
              $landValue[$bx][$by] = $boat - 5;
              $achive += $boat;
              $boat = 0;
            }
          }
          if($boat <= 0) {
            break;
          }
        }
        if($achive > 0) {
          // 少しでも到着した場合、ログを吐く
          $this->log->msBoatPeople($id, $name, $achive);

          // 難民の数が一定数以上なら、平和賞の可能性あり
          if($achive >= 200) {
            $prize = $island['prize'];
            list($flags, $monsters, $turns) = explode(",", $prize, 3);

            if((!($flags & 8)) &&  $achive >= 200){
              $flags |= 8;
              $this->log->prize($id, $name, $init->prizeName[4]);
            } elseif((!($flags & 16)) &&  $achive > 500){
              $flags |= 16;
              $this->log->prize($id, $name, $init->prizeName[5]);
            } elseif((!($flags & 32)) &&  $achive > 800){
              $flags |= 32;
              $this->log->prize($id, $name, $init->prizeName[6]);
            }
            $island['prize'] = "{$flags},{$monsters},{$turns}";
          }
        }
      }
      
      $returnMode = 1;
      break;

    case $init->comSendMonster:
      // 怪獣派遣
      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      $tIsland = $hako->islands[$tn];
      $tName = $tIsland['name'];
      
      if($tn != 0 && empty($tn)) {
        // ターゲットがすでにない
        $this->log->msNoTarget($id, $name, $comName);

        $returnMode = 0;
        break;
      }

      // メッセージ
      $this->log->monsSend($id, $target, $name, $tName);
      $tIsland['monstersend']++;
      $hako->islands[$tn] = $tIsland;

      $island['money'] -= $cost;
      $returnMode = 1;
      break;
    case $init->comSell:
      // 輸出量決定
      if($arg == 0) { $arg = 1; }
      $value = min($arg * (-$cost), $island['food']);

      // 輸出ログ
      $this->log->sell($id, $name, $comName, $value);
      $island['food'] -=  $value;
      $island['money'] += ($value / 10);

      $returnMode = 0;
      break;
      
    case $init->comFood:
    case $init->comMoney:
      // 援助系
      // ターゲット取得
      $tn = $hako->idToNumber[$target];
      $tIsland = &$hako->islands[$tn];
      $tName = $tIsland['name'];

      // 援助量決定
      if($arg == 0) { $arg = 1; }

      if($cost < 0) {
        $value = min($arg * (-$cost), $island['food']);
        $str = "{$value}{$init->unitFood}";
      } else {
        $value = min($arg * ($cost), $island['money']);
        $str = "{$value}{$init->unitMoney}";
      }

      // 援助ログ
      $this->log->aid($id, $target, $name, $tName, $comName, $str);

      if($cost < 0) {
        $island['food'] -= $value;
        $tIsland['food'] += $value;
      } else {
        $island['money'] -= $value;
        $tIsland['money'] += $value;
      }
      $hako->islands[$tn] = $tIsland;

      $returnMode = 0;
      break;
      
    case $init->comPropaganda:
      // 誘致活動
      $this->log->propaganda($id, $name, $comName);
      $island['propaganda'] = 1;
      $island['money'] -= $cost;

      $returnMode = 1;
      break;

    case $init->comGiveup:
      // 放棄
      $this->log->giveup($id, $name);
      $island['dead'] = 1;
      unlink("{$init->dirName}/island.{$id}");
      // DB削除処理
      $hako->deleteIsland($id);

      $returnMode = 1;
      break;
    }

    // 変更された可能性のある変数を書き戻す
//    $hako->islands[$hako->idToNumber[$id]] = $island;

    // 事後処理
    unset($island['prize']);
    unset($island['land']);
    unset($island['landValue']);
    unset($island['command']);
    $island['prize'] = $prize;
    $island['land'] = $land;
    $island['landValue'] = $landValue;
    $island['command'] = $comArray;

    return $returnMode;
  }
  //---------------------------------------------------
  // 成長および単ヘックス災害
  //---------------------------------------------------
  function doEachHex($hako, &$island) {
    global $init;
    // 導出値
    $name = $island['name'];
    $id = $island['id'];
    $land = $island['land'];
    $landValue = $island['landValue'];

    // 増える人口のタネ値
    $addpop  = 10;  // 村、町
    $addpop2 = 0; // 都市
    if($island['food'] < 0) {
      // 食料不足
      $addpop = -30;
    } elseif($island['propaganda'] == 1) {
      // 誘致活動中
      $addpop = 30;
      $addpop2 = 3;
    }
    $monsterMove = array();
    // ループ
    for($i = 0; $i < $init->pointNumber; $i++) {
      $x = $this->rpx[$i];
      $y = $this->rpy[$i];
      $landKind = $land[$x][$y];
      $lv = $landValue[$x][$y];

      switch($landKind) {
      case $init->landTown:
        // 町系
        if($addpop < 0) {
          // 不足
          $lv -= (Util::random(-$addpop) + 1);
          if($lv <= 0) {
            // 平地に戻す
            $land[$x][$y] = $init->landPlains;
            $landValue[$x][$y] = 0;
            continue;
          }
        } else {
          // 成長
          if($lv < 100) {
            $lv += Util::random($addpop) + 1;
            if($lv > 100) {
              $lv = 100;
            }
          } else {
            // 都市になると成長遅い
            if($addpop2 > 0) {
              $lv += Util::random($addpop2) + 1;
            }
          }
        }
        if($lv > 200) {
          $lv = 200;
        }
        $landValue[$x][$y] = $lv;
        break;
        
      case $init->landPlains:
        // 平地
        if(Util::random(5) == 0) {
          // 周りに農場、町があれば、ここも町になる
          if($this->countGrow($land, $landValue, $x, $y)){
            $land[$x][$y] = $init->landTown;
            $landValue[$x][$y] = 1;
          }
        }
        break;
        
      case $init->landForest:
        // 森
        if($lv < 200) {
          // 木を増やす
          $landValue[$x][$y]++;
        }
        break;
        
      case $init->landDefence:
        if($lv == 1) {
          // 防衛施設自爆
          $lName = $this->landName($landKind, $lv);
          $this->log->bombFire($id, $name, $lName, "($x, $y)");

          // 広域被害ルーチン
          $this->wideDamage($id, $name, $land, $landValue, $x, $y);
        }
        break;
        
      case $init->landOil:
        // 海底油田
        $lName = $this->landName($landKind, $lv);
        $value = $init->oilMoney;
        $island['money'] += $value;
        $str = "{$value}{$init->unitMoney}";

        // 収入ログ
        $this->log->oilMoney($id, $name, $lName, "($x, $y)", $str);

        // 枯渇判定
        if(Util::random(1000) < $init->oilRatio) {
          // 枯渇
          $this->log->oilEnd($id, $name, $lName, "($x, $y)");
          $land[$x][$y] = $init->landSea;
          $landValue[$x][$y] = 0;
        }
        break;
        
      case $init->landMonster:

        // 怪獣
        if($monsterMove[$x][$y] == 2) {
          // すでに動いた後
          break;
        }

        // 各要素の取り出し
        $monsSpec = Util::monsterSpec($landValue[$x][$y]);
        $special  = $init->monsterSpecial[$monsSpec['kind']];
        $mName    = $monsSpec['name'];
        // 硬化中?
        if((($special == 3) && (($hako->islandTurn % 2) == 1)) ||
           (($special == 4) && (($hako->islandTurn % 2) == 0))) {
          // 硬化中
          break;
        }

        // 動く方向を決定
        for($j = 0; $j < 3; $j++) {
          $d = Util::random(6) + 1;
          $sx = $x + $init->ax[$d];
          $sy = $y + $init->ay[$d];

          // 行による位置調整
          if((($sy % 2) == 0) && (($y % 2) == 1)) {
            $sx--;
          }

          // 範囲外判定
          if(($sx < 0) || ($sx >= $init->islandSize) ||
             ($sy < 0) || ($sy >= $init->islandSize)) {
            continue;
          }
          // 海、海基、油田、怪獣、山、記念碑以外
          if(($land[$sx][$sy] != $init->landSea) &&
             ($land[$sx][$sy] != $init->landSbase) &&
             ($land[$sx][$sy] != $init->landOil) &&
             ($land[$sx][$sy] != $init->landMountain) &&
             ($land[$sx][$sy] != $init->landMonument) &&
             ($land[$sx][$sy] != $init->landMonster)) {
            break;
          }
        }

        if($j == 3) {
          // 動かなかった
          break;
        }

        // 動いた先の地形によりメッセージ
        $l = $land[$sx][$sy];
        $lv = $landValue[$sx][$sy];
        $lName = $this->landName($l, $lv);
        $point = "({$sx}, {$sy})";

        // 移動
        $land[$sx][$sy] = $land[$x][$y];
        $landValue[$sx][$sy] = $landValue[$x][$y];

        // もと居た位置を荒地に
        $land[$x][$y] = $init->landWaste;
        $landValue[$x][$y] = 0;
      
        // 移動済みフラグ
        if($init->monsterSpecial[$monsSpec['kind']] == 2) {
          // 移動済みフラグは立てない
        } elseif($init->monsterSpecial[$monsSpec['kind']] == 1) {
          // 速い怪獣
          $monsterMove[$sx][$sy] = $monsterMove[$x][$y] + 1;
        } else {
          // 普通の怪獣
          $monsterMove[$sx][$sy] = 2;
        }
        if(($l == $init->landDefence) && ($init->dBaseAuto == 1)) {
          // 防衛施設を踏んだ
          $this->log->monsMoveDefence($id, $name, $lName, $point, $mName);

          // 広域被害ルーチン
          $this->wideDamage($id, $name, $land, $landValue, $sx, $sy);
        } else {
          // 行き先が荒地になる
          $this->log->monsMove($id, $name, $lName, $point, $mName);
        }
        break;
      }
      // すでに$init->landTownがcase文で使われているのでswitchを別に用意
      switch($landKind) {
      case $init->landTown:
      case $init->landHaribote:
      case $init->landFactory:
        // 火災判定
        if($landKind == $init->landTown && ($lv <= 30))
          break;
        
        if(Util::random(1000) < $init->disFire) {
          // 周囲の森と記念碑を数える
          if((Turn::countAround($land, $x, $y, $init->landForest, 7) +
              Turn::countAround($land, $x, $y, $init->landMonument, 7)) == 0) {
            // 無かった場合、火災で壊滅
            $l = $land[$x][$y];
            $lv = $landValue[$x][$y];
            $point = "({$x}, {$y})";
            $lName = $this->landName($l, $lv);
            $this->log->fire($id, $name, $lName, $point);
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          }
        }
        break;
      }
    }
    // 変更された可能性のある変数を書き戻す
    $island['land'] = $land;
    $island['landValue'] = $landValue;
  }

  //---------------------------------------------------
  // 島全体
  //---------------------------------------------------
  function doIslandProcess($hako, &$island) {
    global $init;
    
    // 導出値
    $name = $island['name'];
    $id = $island['id'];
    $land = $island['land'];
    $landValue = $island['landValue'];

    // 地震判定
    if(Util::random(1000) < (($island['prepare2'] + 1) * $init->disEarthquake)) {
      // 地震発生
      $this->log->earthquake($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if((($landKind == $init->landTown) && ($lv >= 100)) ||
           ($landKind == $init->landHaribote) ||
           ($landKind == $init->landFactory)) {
          // 1/4で壊滅
          if(Util::random(4) == 0) {
            $this->log->eQDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          }
        }
      }
    }

    // 食料不足
    if($island['food'] <= 0) {
      // 不足メッセージ
      $this->log->starve($id, $name);
      $island['food'] = 0;

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind == $init->landFarm) ||
           ($landKind == $init->landFactory) ||
           ($landKind == $init->landBase) ||
           ($landKind == $init->landDefence)) {
          // 1/4で壊滅
          if(Util::random(4) == 0) {
            $this->log->svDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          }
        }
      }
    }

    // 津波判定
    if(Util::random(1000) < $init->disTsunami) {
      // 津波発生
      $this->log->tsunami($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind == $init->landTown) ||
           ($landKind == $init->landFarm) ||
           ($landKind == $init->landFactory) ||
           ($landKind == $init->landBase) ||
           ($landKind == $init->landDefence) ||
           ($landKind == $init->landHaribote)) {
          // 1d12 <= (周囲の海 - 1) で崩壊
          if(Util::random(12) <
             (Turn::countAround($land, $x, $y, $init->landOil, 7) +
              Turn::countAround($land, $x, $y, $init->landSbase, 7) +
              Turn::countAround($land, $x, $y, $init->landSea, 7) - 1)) {
            $this->log->tsunamiDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            $land[$x][$y] = $init->landWaste;
            $landValue[$x][$y] = 0;
          }
        }

      }
    }

    // 怪獣判定
    $r = Util::random(10000);
    $pop = $island['pop'];
    do{
      if((($r < ($init->disMonster * $island['area'])) &&
          ($pop >= $init->disMonsBorder1)) ||
         ($island['monstersend'] > 0)) {
        // 怪獣出現
        // 種類を決める
        if($island['monstersend'] > 0) {
          // 人造
          $kind = 0;
          $island['monstersend']--;
        } elseif($pop >= $init->disMonsBorder3) {
          // level3まで
          $kind = Util::random($init->monsterLevel3) + 1;
        } elseif($pop >= $init->disMonsBorder2) {
          // level2まで
          $kind = Util::random($init->monsterLevel2) + 1;
        } else {
          // level1のみ
          $kind = Util::random($init->monsterLevel1) + 1;
        }

        // lvの値を決める
        $lv = $kind * 10
          + $init->monsterBHP[$kind] + Util::random($init->monsterDHP[$kind]);

        // どこに現れるか決める
        for($i = 0; $i < $init->pointNumber; $i++) {
          $bx = $this->rpx[$i];
          $by = $this->rpy[$i];
          if($land[$bx][$by] == $init->landTown) {

            // 地形名
            $lName = $this->landName($init->landTown, $landValue[$bx][$by]);

            // そのヘックスを怪獣に
            $land[$bx][$by] = $init->landMonster;
            $landValue[$bx][$by] = $lv;

            // 怪獣情報
            $monsSpec = Util::monsterSpec($lv);
            $mName    = $monsSpec['name'];

            // メッセージ
            $this->log->monsCome($id, $name, $mName, "({$bx}, {$by})", $lName);
            break;
          }
        }
      }
    } while($island['monstersend'] > 0);

    // 地盤沈下判定
    if(($island['area'] > $init->disFallBorder) &&
       (Util::random(1000) < $init->disFalldown)) {
      // 地盤沈下発生
      $this->log->falldown($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind != $init->landSea) &&
           ($landKind != $init->landSbase) &&
           ($landKind != $init->landOil) &&
           ($landKind != $init->landMountain)) {

          // 周囲に海があれば、値を-1に
          if(Turn::countAround($land, $x, $y, $init->landSea, 7) +
             Turn::countAround($land, $x, $y, $init->landSbase, 7)) {
            $this->log->falldownLand($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            $land[$x][$y] = -1;
            $landValue[$x][$y] = 0;
          }
        }
      }

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];

        if($landKind == -1) {
          // -1になっている所を浅瀬に
          $land[$x][$y] = $init->landSea;
          $landValue[$x][$y] = 1;
        } elseif ($landKind == $init->landSea) {
          // 浅瀬は海に
          $landValue[$x][$y] = 0;
        }

      }
    }

    // 台風判定
    if(Util::random(1000) < $init->disTyphoon) {
      // 台風発生
      $this->log->typhoon($id, $name);

      for($i = 0; $i < $init->pointNumber; $i++) {
        $x = $this->rpx[$i];
        $y = $this->rpy[$i];
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];

        if(($landKind == $init->landFarm) ||
           ($landKind == $init->landHaribote)) {

          // 1d12 <= (6 - 周囲の森) で崩壊
          if(Util::random(12) <
             (6
              - Turn::countAround($land, $x, $y, $init->landForest, 7)
              - Turn::countAround($land, $x, $y, $init->landMonument, 7))) {
            $this->log->typhoonDamage($id, $name, $this->landName($landKind, $lv), "({$x}, {$y})");
            $land[$x][$y] = $init->landPlains;
            $landValue[$x][$y] = 0;
          }
        }
      }
    }

    // 巨大隕石判定
    if(Util::random(1000) < $init->disHugeMeteo) {

      // 落下
      $x = Util::random($init->islandSize);
      $y = Util::random($init->islandSize);
      $landKind = $land[$x][$y];
      $lv = $landValue[$x][$y];
      $point = "({$x}, {$y})";

      // メッセージ
      $this->log->hugeMeteo($id, $name, $point);

      // 広域被害ルーチン
      $this->wideDamage($id, $name, $land, $landValue, $x, $y);
    }

    // 巨大ミサイル判定
    while($island['bigmissile'] > 0) {
      $island['bigmissile']--;

      // 落下
      $x = Util::random($init->islandSize);
      $y = Util::random($init->islandSize);
      $landKind = $land[$x][$y];
      $lv = $landValue[$x][$y];
      $point = "({$x}, {$y})";

      // メッセージ
      $this->log->monDamage($id, $name, $point);

      // 広域被害ルーチン
      $this->wideDamage($id, $name, $land, $landValue, $x, $y);
    }

    // 隕石判定
    if(Util::random(1000) < $init->disMeteo) {
      $first = 1;
      while((Util::random(2) == 0) || ($first == 1)) {
        $first = 0;

        // 落下
        $x = Util::random($init->islandSize);
        $y = Util::random($init->islandSize);
        $landKind = $land[$x][$y];
        $lv = $landValue[$x][$y];
        $point = "({$x}, {$y})";

        if(($landKind == $init->landSea) && ($lv == 0)){
          // 海ポチャ
          $this->log->meteoSea($id, $name, $this->landName($landKind, $lv), $point);
        } elseif($landKind == $init->landMountain) {
          // 山破壊
          $this->log->meteoMountain($id, $name, $this->landName($landKind, $lv), $point);
          $land[$x][$y] = $init->landWaste;
          $landValue[$x][$y] = 0;
          continue;
        } elseif($landKind == $init->landSbase) {
          $this->log->meteoSbase($id, $name, $this->landName($landKind, $lv), $point);
        } elseif($landKind == $init->landMonster) {
          $this->log->meteoMonster($id, $name, $this->landName($landKind, $lv), $point);
        } elseif($landKind == $init->landSea) {
          // 浅瀬
          $this->log->meteoSea1($id, $name, $this->landName($landKind, $lv), $point);
        } else {
          $this->log->meteoNormal($id, $name, $this->landName($landKind, $lv), $point);
        }
        $land[$x][$y] = $init->landSea;
        $landValue[$x][$y] = 0;
      }
    }

    // 噴火判定
    if(Util::random(1000) < $init->disEruption) {
      $x = Util::random($init->islandSize);
      $y = Util::random($init->islandSize);
      $landKind = $land[$x][$y];
      $lv = $landValue[$x][$y];
      $point = "({$x}, {$y})";
      $this->log->eruption($id, $name, $this->landName($landKind, $lv), $point);
      $land[$x][$y] = $init->landMountain;
      $landValue[$x][$y] = 0;

      for($i = 1; $i < 7; $i++) {
        $sx = $x + $init->ax[$i];
        $sy = $y + $init->ay[$i];

        // 行による位置調整
        if((($sy % 2) == 0) && (($y % 2) == 1)) {
          $sx--;
        }

        $landKind = $land[$sx][$sy];
        $lv = $landValue[$sx][$sy];
        $point = "({$sx}, {$sy})";

        if(($sx < 0) || ($sx >= $init->islandSize) ||
           ($sy < 0) || ($sy >= $init->islandSize)) {
        } else {
          // 範囲内の場合
          $landKind = $land[$sx][$sy];
          $lv = $landValue[$sx][$sy];
          $point = "({$sx}, {$sy})";
          if(($landKind == $init->landSea) ||
             ($landKind == $init->landOil) ||
             ($landKind == $init->landSbase)) {
            // 海の場合
            if($lv == 1) {
              // 浅瀬
              $this->log->eruptionSea1($id, $name, $this->landName($landKind, $lv), $point);
            } else {
              $this->log->eruptionSea($id, $name, $this->landName($landKind, $lv), $point);
              $land[$sx][$sy] = $init->landSea;
              $landValue[$sx][$sy] = 1;
              continue;
            }
          } elseif(($landKind == $init->landMountain) ||
                  ($landKind == $init->landMonster) ||
                  ($landKind == $init->landWaste)) {
            continue;
          } else {
            // それ以外の場合
            $this->log->eruptionNormal($id, $name, $this->landName($landKind, $lv), $point);
          }
          $land[$sx][$sy] = $init->landWaste;
          $landValue[$sx][$sy] = 0;
        }
      }
    }
    // 変更された可能性のある変数を書き戻す
    $island['land'] = $land;
    $island['landValue'] = $landValue;

    // 食料があふれてたら換金
    if($island['food'] > $init->maxFood) {
      $island['money'] += round(($island['food'] - $init->maxFood) / 10);
      $island['food'] = $init->maxFood;
    }

    // 金があふれてたら切り捨て
    if($island['money'] > $init->maxMoney) {
      $island['money'] = $init->maxMoney;
    }

    // 各種の値を計算
    Turn::estimate($island);

    // 繁栄、災難賞
    $pop = $island['pop'];
    $damage = $island['oldPop'] - $pop;
    $prize = $island['prize'];
    list($flags, $monsters, $turns) = explode(",", $prize, 3);


    // 繁栄賞
    if((!($flags & 1)) &&  $pop >= 3000){
      $flags |= 1;
      $this->log->prize($id, $name, $init->prizeName[1]);
    } elseif((!($flags & 2)) &&  $pop >= 5000){
      $flags |= 2;
      $this->log->prize($id, $name, $init->prizeName[2]);
    } elseif((!($flags & 4)) &&  $pop >= 10000){
      $flags |= 4;
      $this->log->prize($id, $name, $init->prizeName[3]);
    }

    // 災難賞
    if((!($flags & 64)) &&  $damage >= 500){
      $flags |= 64;
      $this->log->prize($id, $name, $init->prizeName[7]);
    } elseif((!($flags & 128)) &&  $damage >= 1000){
      $flags |= 128;
      $this->log->prize($id, $name, $init->prizeName[8]);
    } elseif((!($flags & 256)) &&  $damage >= 2000){
      $flags |= 256;
      $this->log->prize($id, $name, $init->prizeName[9]);
    }

    $island['prize'] = "{$flags},{$monsters},{$turns}";

  }

  //---------------------------------------------------
  // 周囲の町、農場があるか判定
  //---------------------------------------------------
  function countGrow($land, $landValue, $x, $y) {
    global $init;

    for($i = 1; $i < 7; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
      } else {
        // 範囲内の場合
        if(($land[$sx][$sy] == $init->landTown) ||
           ($land[$sx][$sy] == $init->landFarm)) {
          if($landValue[$sx][$sy] != 1) {
            return true;
          }
        }
      }
    }
    return false;
  }
  //---------------------------------------------------
  // 広域被害ルーチン
  //---------------------------------------------------
  function wideDamage($id, $name, $land, $landValue, $x, $y) {
    global $init;

    for($i = 0; $i < 19; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      $landKind = $land[$sx][$sy];
      $lv = $landValue[$sx][$sy];
      $landName = $this->landName($landKind, $lv);
      $point = "({$sx}, {$sy})";

      // 範囲外判定
      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
        continue;
      }

      // 範囲による分岐
      if($i < 7) {
        // 中心、および1ヘックス
        if($landKind == $init->landSea) {
          $landValue[$sx][$sy] = 0;
          continue;
        } elseif(($landKind == $init->landSbase) ||
                 ($landKind == $init->landOil)) {
          $this->log->wideDamageSea2($id, $name, $landName, $point);
          $land[$sx][$sy] = $init->landSea;
          $landValue[$sx][$sy] = 0;
        } else {
          if($landKind == $init->landMonster) {
            $this->log->wideDamageMonsterSea($id, $name, $landName, $point);
          } else {
            $this->log->wideDamageSea($id, $name, $landName, $point);
          }
          $land[$sx][$sy] = $init->landSea;
          if($i == 0) {
            // 海
            $landValue[$sx][$sy] = 0;
          } else {
            // 浅瀬
            $landValue[$sx][$sy] = 1;
          }
        }
      } else {
        // 2ヘックス
        if(($landKind == $init->landSea) ||
           ($landKind == $init->landOil) ||
           ($landKind == $init->landWaste) ||
           ($landKind == $init->landMountain) ||
           ($landKind == $init->landSbase)) {
          continue;
        } elseif($landKind == $init->landMonster) {
          $this->log->wideDamageMonster($id, $name, $landName, $point);
          $land[$sx][$sy] = $init->landWaste;
          $landValue[$sx][$sy] = 0;
        } else {
          $this->log->wideDamageWaste($id, $name, $landName, $point);
          $land[$sx][$sy] = $init->landWaste;
          $landValue[$sx][$sy] = 0;
        }
      }
    }
  }

  //---------------------------------------------------
  // 人口順でソート
  //---------------------------------------------------
  function islandSort(&$hako) {
    global $init;
    usort($hako->islands, 'popComp');
  }
  //---------------------------------------------------
  // 収入、消費フェイズ
  //---------------------------------------------------
  function income(&$island) {
    global $init;
    
    $pop = $island['pop'];
    $farm = $island['farm'] * 10;
    $factory = $island['factory'];
    $mountain =$island['mountain'];

    // 収入
    if($pop > $farm) {
      // 農業だけじゃ手が余る場合
      $island['food'] += $farm; // 農場フル稼働
      $island['money'] +=
        min(round(($pop - $farm) / 10),
              $factory + $mountain);
    } else {
      // 農業だけで手一杯の場合
      $island['food'] += $pop; // 全員野良仕事
    }

    // 食料消費
    $island['food'] = round($island['food'] - $pop * $init->eatenFood);
  }
  //---------------------------------------------------
  // 人口その他の値を算出
  //---------------------------------------------------
  function estimate(&$island) {
    // estimate(&$island) のように使用
    
    global $init;
    $land = $island['land'];
    $landValue = $island['landValue'];

    $area       = 0;
    $pop        = 0;
    $farm       = 0;
    $factory    = 0;
    $mountain   = 0;
    $monster    = 0;
    // 数える
    for($y = 0; $y < $init->islandSize; $y++) {
      for($x = 0; $x < $init->islandSize; $x++) {
        $kind = $land[$x][$y];
        $value = $landValue[$x][$y];
        if(($kind != $init->landSea) &&
           ($kind != $init->landSbase) &&
           ($kind != $init->landOil)){
          $area++;
          switch($kind) {
          case $init->landTown:
            // 町
            $pop += $value;
            break;
          case $init->landFarm:
            // 農場
            $farm += $value;
            break;
          case $init->landFactory:
            // 工場
            $factory += $value;
            break;
          case $init->landMountain:
            // 山
            $mountain += $value;
            break;
          case $init->landMonster:
            // 怪獣
            $monster++;
            break;
          }
        }
      }
    }
    // 代入
    $island['pop']      = $pop;
    $island['area']     = $area;
    $island['farm']     = $farm;
    $island['factory']  = $factory;
    $island['mountain'] = $mountain;
    $island['monster'] = $monster;
  }
  //---------------------------------------------------
  // 範囲内の地形を数える
  //---------------------------------------------------
  function countAround($land, $x, $y, $kind, $range) {
    global $init;
    // 範囲内の地形を数える
    $count = 0;
    for($i = 0; $i < $range; $i++) {
      $sx = $x + $init->ax[$i];
      $sy = $y + $init->ay[$i];

      // 行による位置調整
      if((($sy % 2) == 0) && (($y % 2) == 1)) {
        $sx--;
      }

      if(($sx < 0) || ($sx >= $init->islandSize) ||
         ($sy < 0) || ($sy >= $init->islandSize)) {
        // 範囲外の場合
        if($kind == $init->landSea) {
          // 海なら加算
          $count++;
        }
      } else {
        // 範囲内の場合
        if($land[$sx][$sy] == $kind) {
          $count++;
        }
      }
    }
    return $count;
  }
  //---------------------------------------------------
  // 地形の呼び方
  //---------------------------------------------------
  function landName($land, $lv) {
    global $init;
    switch($land) {
    case $init->landSea:
      if($lv == 1) {
        return '浅瀬';
      } else {
        return '海';
      }
      break;
    case $init->landWaste:
      return '荒地';
    case $init->landPlains:
      return '平地';
    case $init->landTown:
      if($lv < 30) {
        return '村';
      } elseif($lv < 100) {
        return '町';
      } else {
        return '都市';
      }
    case $init->landForest:
      return '森';
    case $init->landFarm:
      return '農場';
    case $init->landFactory:
      return '工場';
    case $init->landBase:
      return 'ミサイル基地';
    case $init->landDefence:
      return '防衛施設';
    case $init->landMountain:
      return '山';
    case $init->landMonster:
      $monsSpec = Util::monsterSpec($lv);
      return $monsSpec['name'];
    case $init->landSbase:
      return '海底基地';
    case $init->landOil:
      return '海底油田';
    case $init->landMonument:
      return $init->monumentName[$lv];
    case $init->landHaribote:
      return 'ハリボテ';

    }
  }
}
// 人口を比較
function popComp($x, $y) {
  if($x['pop'] == $y['pop']) return 0;
  return ($x['pop'] > $y['pop']) ? -1 : 1;
}


?>
