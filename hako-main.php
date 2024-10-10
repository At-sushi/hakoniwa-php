<?php
/*******************************************************************

  箱庭諸島２ for PHP

  
  $Id: hako-main.php,v 1.20 2004/12/14 12:24:56 Watson Exp $

*******************************************************************/

require 'jcode.phps';
require 'config.php';
require 'hako-html.php';
require 'hako-turn.php';
$init = new Init;

define("READ_LINE", 4096);
$THIS_FILE =  $init->baseDir . "/hako-main.php";
$BACK_TO_TOP = "<A HREF=\"{$THIS_FILE}?\">{$init->tagBig_}トップへ戻る{$init->_tagBig}</A>";
$ISLAND_TURN; // ターン数

$PRODUCT_VERSION = '20060612';

//--------------------------------------------------------------------
class Hako extends HakoIO {
  var $islandList;	// 島リスト
  var $targetList;	// ターゲットの島リスト
  var $defaultTarget;	// 目標補足用ターゲット
  
  function readIslands(&$cgi) {
    global $init;
    
    $m = $this->readIslandsFile($cgi);
    $this->islandList = $this->getIslandList($cgi->dataSet['defaultID']);
    if($init->targetIsland == 1) {
      // 目標の島 所有の島が選択されたリスト
      $this->targetList = $this->islandList;
    } else {
      // 順位がTOPの島が選択された状態のリスト
      $this->targetList = $this->getIslandList($cgi->dataSet['defaultTarget']);
    }
    return $m;
  }

  //---------------------------------------------------
  // 島リスト生成
  //---------------------------------------------------
  function getIslandList($select = 0) {
    $list = "";
    for($i = 0; $i < $this->islandNumber; $i++) {
      $name = $this->islands[$i]['name'];
      $id   = $this->islands[$i]['id'];

      // 攻撃目標をあらかじめ自分の島にする
      if(empty($this->defaultTarget)) {$this->defaultTarget = $id;}

      if($id == $select) {
        $s = "selected";
      } else {
        $s = "";
      }
      $list .= "<option value=\"$id\" $s>{$name}島</option>\n";
    }
    return $list;
  }
  //---------------------------------------------------
  // 賞に関するリストを生成
  //---------------------------------------------------
  function getPrizeList($prize) {
    global $init;
    list($flags, $monsters, $turns) = explode(",", $prize, 3);

    $turns = explode(",", $turns);
    $prizeList = "";
    // ターン杯
    $max = -1;
    $nameList = "";
    if($turns[0] != "") {
      for($k = 0; $k < count($turns) - 1; $k++) {
        $nameList .= "[{$turns[$k]}] ";
        $max = $k;
      }
    }
    if($max != -1) {
      $prizeList .= "<img src=\"prize0.gif\" alt=\"$nameList\" width=\"16\" height=\"16\"> ";
    }
    // 賞
    $f = 1;
    for($k = 1; $k < count($init->prizeName); $k++) {
      if($flags & $f) {
        $prizeList .= "<img src=\"prize{$k}.gif\" alt=\"{$init->prizeName[$k]}\" width=\"16\" height=\"16\"> ";
      }
      $f = $f << 1;
    }
    // 倒した怪獣リスト
    $f = 1;
    $max = -1;
    $nameList = "";
    for($k = 0; $k < $init->monsterNumber; $k++) {
      if($monsters & $f) {
        $nameList .= "[{$init->monsterName[$k]}] ";
        $max = $k;
      }
      $f = $f << 1;
    }
    if($max != -1) {
      $prizeList .= "<img src=\"{$init->monsterImage[$max]}\" alt=\"{$nameList}\" width=\"16\" height=\"16\"> ";
    }
    return $prizeList;
  }
  //------------------------------------------------------------------

  //---------------------------------------------------
  // 地形に関するデータ生成
  //---------------------------------------------------
  function landString($l, $lv, $x, $y, $mode, $comStr) {
    global $init;
    $point = "({$x},{$y})";
    $naviExp = "''";

    if($x < $init->islandSize / 2)
      $naviPos = 0;
    else
      $naviPos = 1;

    switch($l) {
    case $init->landSea:
      switch($lv) {
      case 1:
        // 浅瀬
        $image = 'land14.gif';
        $naviTitle = '浅瀬';
        break;
      default:
        // 海
        $image = 'land0.gif';
        $naviTitle = '海';
      }
      break;
    case $init->landWaste:
      // 荒地
      if($lv == 1) {
        $image = 'land13.gif'; // 着弾点
      } else {
        $image = 'land1.gif';
      }
      $naviTitle = '荒地';
      break;
    case $init->landPlains:
      // 平地
      $image = 'land2.gif';
      $naviTitle = '平地';
      break;
    case $init->landForest:
      // 森
      if($mode == 1) {
        $image = 'land6.gif';
        $naviText= "{$lv}{$init->unitTree}";
      } else {
        // 観光者の場合は木の本数隠す
        $image = 'land6.gif';
      }
      $naviTitle = '森';
      break;
    case $init->landTown:
      // 町
      $p; $n;
      if($lv < 30) {
        $p = 3;
        $naviTitle = '村';
      } else if($lv < 100) {
        $p = 4;
        $naviTitle = '町';
      } else {
        $p = 5;
        $naviTitle = '都市';
      }
      $image = "land{$p}.gif";
      $naviText = "{$lv}{$init->unitPop}";
      break;
    case $init->landFarm:
      // 農場
      $image = 'land7.gif';
      $naviTitle = '農場';
      $naviText = "{$lv}0{$init->unitPop}規模";
      break;
    case $init->landFactory:
      // 工場
      $image = 'land8.gif';
      $naviTitle = '工場';
      $naviText = "{$lv}0{$init->unitPop}規模";
      break;
    case $init->landBase:
      if($mode == 0 || $mode == 2) {
        // 観光者の場合は森のふり
        $image = 'land6.gif';
        $naviTitle = '森';
      } else {
        // ミサイル基地
        $level = Util::expToLevel($l, $lv);
        $image = 'land9.gif';
        $naviTitle = 'ミサイル基地';
        $naviText = "レベル {$level} / 経験値 {$lv}";
      }
      break;
    case $init->landSbase:
      // 海底基地
      if($mode == 0 || $mode == 2) {
        // 観光者の場合は海のふり
        $image = 'land0.gif';
        $naviTitle = '海';
      } else {
        $level = Util::expToLevel($l, $lv);
        $image = 'land12.gif';
        $naviTitle = '海底基地';
        $naviText = "レベル {$level} / 経験値 {$lv}";
      }
      break;
    case $init->landDefence:
      // 防衛施設
      $image = 'land10.gif';
      $naviTitle = '防衛施設';
      break;
    case $init->landHaribote:
      // ハリボテ
      $image = 'land10.gif';
      if($mode == 0 || $mode == 2) {
        // 観光者の場合は防衛施設のふり
        $naviTitle = '防衛施設';
      } else {
        $naviTitle = 'ハリボテ';
      }
      break;
    case $init->landOil:
      // 海底油田
      $image = 'land16.gif';
      $naviTitle = '海底油田';
      break;
    case $init->landMountain:
      // 山
      if($lv > 0) {
        $image = 'land15.gif';
        $naviTitle = '採掘場';
        $naviText = "{$lv}0{$init->unitPop}規模";
      } else {
        $image = 'land11.gif';
        $naviTitle = '山';
      }
      break;
    case $init->landMonument:
      // 記念碑
      $image = $init->monumentImage[$lv];
      $naviTitle = '記念碑';
      $naviText = $init->monumentName[$lv];
      break;
    case $init->landMonster:
      // 怪獣
      $monsSpec = Util::monsterSpec($lv);
      $special = $init->monsterSpecial[$monsSpec['kind']];
      $image = $init->monsterImage[$monsSpec['kind']];
      $naviTitle = '怪獣';

      // 硬化中?
      if((($special == 3) && (($this->islandTurn % 2) == 1)) ||
         (($special == 4) && (($this->islandTurn % 2) == 0))) {
        // 硬化中
        $image = $init->monsterImage2[$monsSpec['kind']];
      }
      $naviText = "怪獣{$monsSpec['name']}(体力{$monsSpec['hp']})";
    }

    if($mode == 1 || $mode == 2) {
      print "<a href=\"javascript: void(0);\" onclick=\"ps($x,$y)\" onkeypress=\"ps($x,$y)\">";
      $naviText = "{$comStr}\\n{$naviText}";
    }
    print "<img src=\"{$image}\" width=\"32\" height=\"32\" alt=\"{$point} {$naviTitle} {$comStr}\" onMouseOver=\"Navi({$naviPos},'{$image}', '{$naviTitle}', '{$point}', '{$naviText}', {$naviExp});\" onMouseOut=\"NaviClose(); return false\">";

    // 座標設定閉じ
    if($mode == 1 || $mode == 2)
      print "</a>";
  }
}
//--------------------------------------------------------------------
class HakoIO {
  var $islandTurn;	// ターン数
  var $islandLastTime;	// 最終更新時刻
  var $islandNumber;	// 島の総数
  var $islandNextID;	// 次に割り当てる島ID
  var $islands;		// 全島の情報を格納
  var $idToNumber;
  var $idToName;

  private $db_handle;

  function __construct()
  {
      global $init;

      $this->db_handle = new PDO(
          "pgsql:host=localhost;dbname=hakoniwa",
          $init->db_id,
          $init->db_pass,
          [
              PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
              PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
              PDO::ATTR_PERSISTENT => true,
          ]
      );
  }

  // テーブル初期化・作成
  public function createTable(int $now) {
    // 前処理
    $this->deleteTable();

    $this->db_handle->beginTransaction();

    // ゲーム全体用データ
    $this->db_handle->exec("CREATE TABLE games (
      islandTurn        INT,
      islandLastTime    INT,
      islandNumber      INT,
      islandNextID      INT
      )");

    $query = $this->db_handle->prepare("INSERT INTO games VALUES (1, :now, 0, 1)");
    $query->bindParam(":now", $now);
    $query->execute();

    // 島データ
    $this->db_handle->exec("CREATE TABLE islands ("
      ."id            INT,"
      ."name          VARCHAR(128),"
      ."owner         VARCHAR(128),"
      ."prize         VARCHAR(32),"
      ."absent        VARCHAR(32),"
      ."comment       VARCHAR(512),"
      ."comment_turn  VARCHAR(512),"
      ."password      VARCHAR(32),"
      ."money         INT,"
      ."food          INT,"
      ."pop           INT,"
      ."area          INT,"
      ."farm          INT,"
      ."factory       INT,"
      ."mountain      INT,"
      ."monster       INT,"
      ."land          JSONB,"
      ."landValue     JSONB,"
      ."CONSTRAINT id_key PRIMARY KEY(id))");

    // コマンド
    $this->db_handle->exec("CREATE TABLE commands (
      islandID  INT,
      line      INT,
      kind      INT,
      target	INT,
      x         INT,
      y         INT,
      arg       INT,
      CONSTRAINT command_id_key PRIMARY KEY(islandID, line)
      )");

    $this->db_handle->commit();
  }

  // 全削除
  public function deleteTable() {
    $this->db_handle->exec("DROP TABLE IF EXISTS games, islands, commands");
  }

  //---------------------------------------------------
  // 全島データを読み込む
  // 'mode'が変わる可能性があるので$cgiを参照で受け取る
  //---------------------------------------------------
  public function readIslandsFile(&$cgi) {
    global $init;
    $num = $cgi->dataSet['ISLANDID'];

    if ($this->db_handle == null) {
      return false;
    }

    $query = $this->db_handle->query("SELECT * FROM games LIMIT 1");
    [
      $this->islandTurn,
      $this->islandLastTime,
      $this->islandNumber,
      $this->islandNextID,
    ] = $query->fetch(PDO::FETCH_NUM);

    $GLOBALS['ISLAND_TURN'] = $this->islandTurn;

    // ターン処理判定
    $now = time();
    if((DEBUG && (strcmp($cgi->dataSet['mode'], 'debugTurn') == 0)) ||
       (($now - $this->islandLastTime) >= $init->unitTime)) {
      $cgi->mode = $data['mode'] = 'turn';
      $num = -1;
    }

    // 島データをフェッチ
    $query = $this->db_handle->prepare("SELECT "
          ."id,"
          ."name,"
          ."owner,"
          ."prize,"
          ."absent,"
          ."comment,"
          ."comment_turn,"
          ."password,"
          ."money,"
          ."food,"
          ."pop,"
          ."area,"
          ."farm,"
          ."factory,"
          ."mountain,"
          ."monster"
          ." FROM islands ORDER BY id LIMIT :num");
    $query->bindParam(":num", $this->islandNumber);
    $query->execute();

    for($i = 0; $i < $this->islandNumber; $i++) {
      $this->islands[$i] = $this->readIsland($query->fetch(PDO::FETCH_ASSOC), $num);
      $this->idToNumber[$this->islands[$i]['id']] = $i;
    }

    return true;
  }
  //---------------------------------------------------
  // 島ひとつ読み込む
  //---------------------------------------------------
  private function readIsland($row, $num) {
    global $init;

    $this->idToName[$id] = $name;

    if(($num == -1) || ($num == $row['id'])) {
      $query = $this->db_handle->prepare("SELECT land, landValue FROM islands WHERE id = :id");
      $query->bindParam(":id", $row['id']);
      $query->execute();

      // 地形
      $landData = $query->fetch(PDO::FETCH_ASSOC);
      $row['land'] = json_decode($landData['land'], true);
      $row['landValue'] = json_decode($landData['landvalue'], true);

      // コマンド
      $query = $this->db_handle->prepare("SELECT kind, target, x, y, arg FROM commands WHERE islandID = :id ORDER BY line LIMIT :num");
      $query->bindParam(":id", $row['id']);
      $query->bindParam(":num", $init->commandMax);
      $query->execute();
      $row['command'] = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    return $row;
  }
  //---------------------------------------------------
  // 全島データを書き込む
  //---------------------------------------------------
  function writeIslandsFile($num = 0, bool $create = false) {
    $this->db_handle->beginTransaction();

    $query = $this->db_handle->prepare("UPDATE games SET "
    ."islandTurn = :turn, islandLastTime = :lasttime, islandNumber = :number, islandNextID = :nextid");
    $query->bindParam(":turn", $this->islandTurn);
    $query->bindParam(":lasttime", $this->islandLastTime);
    $query->bindParam(":number", $this->islandNumber);
    $query->bindParam(":nextid", $this->islandNextID);
    $query->execute();

    if ($num <= 0) {
      // 全部更新する
      for ($i = 0; $i < $this->islandNumber; $i++) {
		$this->writeIsland($fp, $num, $this->islands[$i], $create);
      }
    }
    else {
      $this->writeIsland($fp, $num, $this->islands[$this->idToNumber[$num]], $create);
    }

    $this->db_handle->commit();
  }
  //---------------------------------------------------
  // 島ひとつ書き込む
  //---------------------------------------------------
  private function writeIsland($fp, $num, $island, bool $create = false) {
    global $init;

    // 暫定
    if ($create == true) {
      $query = $this->db_handle->prepare("INSERT INTO islands (id) VALUES (:id)");
      $query->bindParam(":id", $num);
      $query->execute();
    }

    $query = $this->db_handle->prepare("UPDATE islands SET "
          ."name = :name,"
          ."owner = :owner,"
          ."prize = :prize,"
          ."absent = :absent,"
          ."comment = :comment,"
          ."comment_turn = :comment_turn,"
          ."password = :password,"
          ."money = :money,"
          ."food = :food,"
          ."pop = :pop,"
          ."area = :area,"
          ."farm = :farm,"
          ."factory = :factory,"
          ."mountain = :mountain,"
          ."monster = :monster"
          ." WHERE id = :id");
    $tmpData = $island;
    unset($tmpData['land']);
    unset($tmpData['landValue']);
    unset($tmpData['command']);
    unset($tmpData['lbbs']);
    unset($tmpData['oldPop']);
    $query->execute($tmpData);

    // 地形
    if($num != 0) {
      $query = $this->db_handle->prepare("UPDATE islands SET land = :land, landValue = :landValue WHERE id = :id");
      $query->bindParam(':id', $island['id']);
      $query->bindParam(':land', json_encode($island['land'], JSON_NUMERIC_CHECK));
      $query->bindParam(':landValue', json_encode($island['landValue'], JSON_NUMERIC_CHECK));
      $query->execute();


      // コマンド
        // 暫定措置
        if ($create == true) {
          $query = $this->db_handle->prepare("INSERT INTO commands (islandID, line, kind, target, x, y, arg) VALUES (:id, :line, :kind, :target, :x, :y, :arg)");
        }
 	else { 
          $query = $this->db_handle->prepare("UPDATE commands SET kind = :kind, target = :target, x = :x, y = :y, arg = :arg WHERE islandID = :id AND line = :line");
	} 
      $command = $island['command'];
      for($i = 0; $i < $init->commandMax; $i++) {
	$command[$i]["id"] = $island['id'];
	$command[$i]["line"] = $i;
        $query->execute($command[$i]);
      }
    }
  }
  //---------------------------------------------------
  // 島データ削除処理
  //---------------------------------------------------
  public function deleteIsland(int $id) {
    $this->db_handle->beginTransaction();

    $query = $this->db_handle->prepare("DELETE FROM islands WHERE id = :id");
    $query->bindParam(":id", $id);
    $query->execute();

    $query = $this->db_handle->prepare("DELETE FROM commands WHERE islandID = :id");
    $query->bindParam(":id", $id);
    $query->execute();

    $this->db_handle->commit();
  }
  //---------------------------------------------------
  // データのバックアップ
  //---------------------------------------------------
  function backUp() {
    // unimplemented
  }
  //---------------------------------------------------
  // 不要なディレクトリとファイルを削除
  //---------------------------------------------------
  function rmTree($dirName) {
    // unimplemented
  }
}
//--------------------------------------------------------------------
class LogIO {
  var $logPool = array();
  var $secretLogPool = array();
  var $lateLogPool = array();
  
  //---------------------------------------------------
  // ログファイルを後ろにずらす
  //---------------------------------------------------
  function slideBackLogFile() {
    global $init;
    for($i = $init->logMax - 1; $i >= 0; $i--) {
      $j = $i + 1;
      $s = "{$init->dirName}/hakojima.log{$i}";
      $d = "{$init->dirName}/hakojima.log{$j}";
      if(is_file($s)) {
        if(is_file($d))
           unlink($d);
        rename($s, $d);
      }
    }
  }
  //---------------------------------------------------
  // 最近の出来事を出力
  //---------------------------------------------------
  function logFilePrint($num = 0, $id = 0, $mode = 0) {
    global $init;
    $fileName = $init->dirName . "/hakojima.log" . $num;
    if(!is_file($fileName)) {
      return;
    }
    $fp = fopen($fileName, "r");

    while($line = chop(fgets($fp, READ_LINE))) {
      list($m, $turn, $id1, $id2, $message) = explode(",", $line, 5);
      if($m == 1) {
        if(($mode == 0) || ($id1 != $id)) {
          continue;
        }
        $m = "<strong>(機密)</strong>";
      } else {
        $m = "";
      }
      if($id != 0) {
        if(($id != $id1) && ($id != $id2)) {
          continue;
        }
      }
      print "{$init->tagNumber_}ターン{$turn}{$m}{$init->_tagNumber}：{$message}<br>\n";
    }
    fclose($fp);
  }
  //---------------------------------------------------
  // 発見の記録を出力
  //---------------------------------------------------
  function historyPrint() {
    global $init;
    $fileName = $init->dirName . "/hakojima.his";
    if(!is_file($fileName)) {
      return;
    }
    $fp = fopen($fileName, "r");

    $history = array();
    $k = 0;
    while($line = chop(fgets($fp, READ_LINE))) {
      array_push($history, $line);
      $k++;
    }
    for($i = 0; $i < $k; $i++) {
      list($turn, $his) = explode(",", array_pop($history), 2);
      print "{$init->tagNumber_}ターン{$turn}{$init->_tagNumber}：$his<br>\n";
    }
  }
  //---------------------------------------------------
  // 発見の記録を保存
  //---------------------------------------------------
  function history($str) {
    global $init;
    $fileName = "{$init->dirName}/hakojima.his";

    if(!is_file($fileName))
      touch($fileName);

    $fp = fopen($fileName, "a");
    fputs($fp, "{$GLOBALS['ISLAND_TURN']},{$str}\n");
    fclose($fp);
//    chmod($fileName, 0666);
    
  }
  //---------------------------------------------------
  // 発見の記録ログ調整
  //---------------------------------------------------
  function historyTrim() {
    global $init;
    $fileName = "{$init->dirName}/hakojima.his";
    if(is_file($fileName)) {
      $fp = fopen($fileName, "r");

      $line = array();
      while($l = chop(fgets($fp, READ_LINE))) {
        array_push($line, $l);
        $count++;
      }
      fclose($fp);
      if($count > $init->historyMax) {

        if(!is_file($fileName))
          touch($fileName);

        $fp = fopen($fileName, "w");
        for($i = ($count - $init->historyMax); $i < $count; $i++) {
          fputs($fp, "{$line[$i]}\n");
        }
        fclose($fp);
//        chmod($fileName, 0666);
      }
    }
  }
  //---------------------------------------------------
  // ログ
  //---------------------------------------------------
  function out($str, $id = "", $tid = "") {
    array_push($this->logPool, "0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
  }
  //---------------------------------------------------
  // 機密ログ
  //---------------------------------------------------
  function secret($str, $id = "", $tid = "") {
    array_push($this->secretLogPool,"1,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
  }
  //---------------------------------------------------
  // 遅延ログ
  //---------------------------------------------------
  function late($str, $id = "", $tid = "") {
    array_push($this->lateLogPool,"0,{$GLOBALS['ISLAND_TURN']},{$id},{$tid},{$str}");
  }
  //---------------------------------------------------
  // ログ書き出し
  //---------------------------------------------------
  function flush() {
    global $init;
    $fileName = "{$init->dirName}/hakojima.log0";

    if(!is_file($fileName))
      touch($fileName);

    $fp = fopen($fileName, "w");

    // 全部逆順にして書き出す
    if(!empty($this->secretLogPool)) {
      for($i = count($this->secretLogPool) - 1; $i >= 0; $i--) {
        fputs($fp, "{$this->secretLogPool[$i]}\n");
      }
    }
    if(!empty($this->lateLogPool)) {
      for($i = count($this->lateLogPool) - 1; $i >= 0; $i--) {
        fputs($fp, "{$this->lateLogPool[$i]}\n");
      }
    }
    if(!empty($this->logPool)) {
      for($i = count($this->logPool) - 1; $i >= 0; $i--) {
        fputs($fp, "{$this->logPool[$i]}\n");
      }
    }
    fclose($fp);
//    chmod($fileName, 0666);
  }    
}

//--------------------------------------------------------------------
class Util {
  //---------------------------------------------------
  // 資金の表示
  //---------------------------------------------------
  function aboutMoney($money = 0) {
    global $init;
    if($init->moneyMode) {
      if($money < 500) {
        return "推定500{$init->unitMoney}未満";
      } else {
        return "推定" . round($money / 1000) . "000" . $init->unitMoney;
      }
    } else {
      return $money . $init->unitMoney;
    }
  }
  //---------------------------------------------------
  // 経験地からミサイル基地レベルを算出
  //---------------------------------------------------
  function expToLevel($kind, $exp) {
    global $init;
    if($kind == $init->landBase) {
      // ミサイル基地
      for($i = $init->maxBaseLevel; $i > 1; $i--) {
        if($exp >= $init->baseLevelUp[$i - 2]) {
          return $i;
        }
      }
      return 1;
    } else {
      // 海底基地
      for($i = $init->maxSBaseLevel; $i > 1; $i--) {
        if($exp >= $init->sBaseLevelUp[$i - 2]) {
          return $i;
        }
      }
      return 1;
    }
  }
  //---------------------------------------------------
  // 怪獣の種類・名前・体力を算出
  //---------------------------------------------------
  function monsterSpec($lv) {
    global $init;
    // 種類
    $kind = (int)($lv / 10);
    // 名前
    $name = $init->monsterName[$kind];
    // 体力
    $hp = $lv - ($kind * 10);
    return array ( 'kind' => $kind, 'name' => $name, 'hp' => $hp );
  }
  //---------------------------------------------------
  // 島の名前から番号を算出
  //---------------------------------------------------
  function  nameToNumber($hako, $name) {
    // 全島から探す
    for($i = 0; $i < $hako->islandNumber; $i++) {
      if(strcmp($name, "{$hako->islands[$i]['name']}") == 0) {
        return $i;
      }
    }
    // 見つからなかった場合
    return -1;
  }
  //---------------------------------------------------
  // パスワードチェック
  //---------------------------------------------------
  function checkPassword($p1 = "", $p2 = "") {
    global $init;

    // nullチェック
    if(empty($p2))
      return false;

    // マスターパスワードチェック
    if(strcmp($init->masterPassword, $p2) == 0)
      return true;

    if(strcmp($p1, Util::encode($p2)) == 0)
      return true;
    
    return false;
  }
  //---------------------------------------------------
  // パスワードのエンコード
  //---------------------------------------------------
  function encode($s) {
    global $init;
    if($init->cryptOn) {
      return crypt($s, 'h2');
    } else {
      return $s;
    }
  }
  //---------------------------------------------------
  // 0 〜 num -1 の乱数生成
  //---------------------------------------------------
  function random($num = 0) {
    if($num <= 1) return 0;
    return mt_rand(0, $num - 1);
  }
  //---------------------------------------------------
  // ローカル掲示板のメッセージを一つ前にずらす
  //---------------------------------------------------
  function slideBackLbbsMessage(&$lbbs, $num) {
    global $init;
    array_splice($lbbs, $num, 1);
    $lbbs[$init->lbbsMax - 1] = '0>>';
  }
  //---------------------------------------------------
  // ローカル掲示板のメッセージを一つ後ろにずらす
  //---------------------------------------------------
  function slideLbbsMessage(&$lbbs) {
    array_pop($lbbs);
    array_unshift($lbbs, $lbbs[0]);
  }
  //---------------------------------------------------
  // ランダムな座標を生成
  //---------------------------------------------------
  function makeRandomPointArray() {
    global $init;
    $rx = $ry = array();
    for($i = 0; $i < $init->islandSize; $i++)
      for($j = 0; $j < $init->islandSize; $j++)
        $rx[$i * $init->islandSize + $j] = $j;

    for($i = 0; $i < $init->islandSize; $i++)
      for($j = 0; $j < $init->islandSize; $j++)
        $ry[$j * $init->islandSize + $i] = $j;
    

    for($i = $init->pointNumber; --$i;) {
      $j = Util::random($i + 1);
      if($i != $j) {
        $tmp = $rx[$i];
        $rx[$i] = $rx[$j];
        $rx[$j] = $tmp;
          
        $tmp = $ry[$i];
        $ry[$i] = $ry[$j];
        $ry[$j] = $tmp;
      }
    }
    return array($rx, $ry);
  }
  //---------------------------------------------------
  // ランダムな島の順序を生成
  //---------------------------------------------------
  function randomArray($n = 1) {
    // 初期値
    for($i = 0; $i < $n; $i++) {
      $list[$i] = $i;
    }

    // シャッフル
    for($i = 0; $i < $n; $i++) {
      $j = Util::random($n - 1);
      if($i != $j) {
        $tmp = $list[$i];
        $list[$i] = $list[$j];
        $list[$j] = $tmp;
      }
    }
    return $list;
  }
  //---------------------------------------------------
  // コマンドを前にずらす
  //---------------------------------------------------
  function slideFront(&$command, $number = 0) {
    global $init;
    // それぞれずらす
    array_splice($command, $number, 1);

    // 最後に資金繰り
    $command[$init->commandMax - 1] = array (
      'kind'   => $init->comDoNothing,
      'target' => 0,
      'x'      => 0,
      'y'      => 0,
      'arg'    => 0
      );
  }
  //---------------------------------------------------
  // コマンドを後にずらす
  //---------------------------------------------------
  function slideBack(&$command, $number = 0) {
    global $init;
    // それぞれずらす
    if($number == count($command) - 1)
      return;

    for($i = $init->commandMax - 1; $i >= $number; $i--) {
      $command[$i] = $command[$i - 1];
    }
  }

  function euc_convert($arg) {
    // 文字コードをEUC-JPに変換して返す
    // 文字列の文字コードを判別
    $code = i18n_discover_encoding("$arg");
    // 非EUC-JPの場合のみEUC-JPに変換
    if ( $code != "EUC-JP" ) {
      $arg = i18n_convert("$arg","EUC-JP");
    }
    return $arg;
  }

  function sjis_convert($arg) {
    // 文字コードをSHIFT_JISに変換して返す
    // 文字列の文字コードを判別
    $code = i18n_discover_encoding("$arg");
    // 非SHIFT_JISの場合のみSHIFT_JISに変換
    if ( $code != "SJIS" ) {
      $arg = i18n_convert("$arg","SJIS");
    }
    return $arg;
  }
  //---------------------------------------------------
  // ファイルをロックする
  //---------------------------------------------------
  function lock() {
    global $init;

    $fp = fopen("{$init->dirName}/lock.dat", "w");

    for($count = 0; $count < LOCK_RETRY_COUNT; $count++) {
      if(flock($fp, LOCK_EX)) {
        // ロック成功
        return $fp;
      }
      // 一定時間sleepし、ロックが解除されるのを待つ
      // 乱数時間sleepすることで、ロックが何度も衝突しないようにする
      usleep((LOCK_RETRY_INTERVAL - mt_rand(0, 300)) * 1000);
    }
    // ロック失敗
    fclose($fp);
    HakoError::lockFail();
    return FALSE;
  }
  //---------------------------------------------------
  // ファイルをアンロックする
  //---------------------------------------------------
  function unlock($fp) {
    flock($fp, LOCK_UN);
    fclose($fp);
  }
}
class Cgi {
  var $mode = "";
  var $dataSet = array();
  //---------------------------------------------------
  // POST、GETのデータを取得
  //---------------------------------------------------
  function parseInputData() {
    global $init;

    $this->mode = $_POST['mode'];
    if(!empty($_POST)) {
      while(list($name, $value) = each($_POST)) {
//        $value = Util::sjis_convert($value);
        // 半角カナがあれば全角に変換して返す
//        $value = i18n_ja_jp_hantozen($value,"KHV");
        $value = str_replace(",", "", $value);
        $value = JcodeConvert($value, 0, 2);
        $value = HANtoZEN_SJIS($value);
        if($init->stripslashes == true) {
          $this->dataSet["{$name}"] = stripslashes($value);
        } else {
          $this->dataSet["{$name}"] = $value;
        }
      }
    }
    if(!empty($_GET['Sight'])) {
      $this->mode = "print";
      $this->dataSet['ISLANDID'] = $_GET['Sight'];
    }
    if(!empty($_GET['target'])) {
      $this->mode = "targetView";
      $this->dataSet['ISLANDID'] = $_GET['target'];
    }
    if($_GET['mode'] == "conf") {
      $this->mode = "conf";
    }
    if($this->mode == "turn") {
      // この段階で mode に turn がセットされるのは不正アクセスがある場合のみなのでクリアする
      $this->mode = '';
    }
    $this->dataSet["ISLANDNAME"] = jsubstr($this->dataSet["ISLANDNAME"], 0, 16);
    $this->dataSet["MESSAGE"] = jsubstr($this->dataSet["MESSAGE"], 0, 60);
    $this->dataSet["LBBSMESSAGE"] = jsubstr($this->dataSet["LBBSMESSAGE"], 0, 60);
  }
  function lastModified() {
    global $init;

    // Last Modifiedヘッダを出力
/*
    if($this->mode == "Sight") {
      $fileName = "{$init->dirName}/island.{$this->dataSet['ISLANDID']}";
    } else {
      $fileName = "{$init->dirName}/hakojima.dat";
    }
*/
/*    $fileName = "{$init->dirName}/hakojima.dat";
    $time_stamp = filemtime($fileName);
    $time = gmdate("D, d M Y G:i:s", $time_stamp);
    header ("Last-Modified: $time GMT");
    $this->modifiedSinces($time_stamp);
*/  }
  function modifiedSinces($time) {
    $modsince = $_SERVER{'HTTP_IF_MODIFIED_SINCE'};

    $ms = gmdate("D, d M Y G:i:s", $time) . " GMT";
    if($modsince == $ms)
      // RFC 822
      header ("HTTP/1.1 304 Not Modified\n");

    $ms = gmdate("l, d-M-y G:i:s", $time) . " GMT";
    if($modsince == $ms)
      // RFC 850
      header ("HTTP/1.1 304 Not Modified\n");

    $ms = gmdate("D M j G:i:s Y", $time);
    if($modsince == $ms)
      // ANSI C's asctime() format
      header ("HTTP/1.1 304 Not Modified\n");
  }
  //---------------------------------------------------
  // COOKIEを取得
  //---------------------------------------------------
  function getCookies() {
    if(!empty($_COOKIE)) {
      while(list($name, $value) = each($_COOKIE)) {
        switch($name) {
        case "OWNISLANDID":
          $this->dataSet['defaultID'] = $value;
          break;
        case "OWNISLANDPASSWORD":
          $this->dataSet['defaultPassword'] = $value;
          break;
        case "TARGETISLANDID":
          $this->dataSet['defaultTarget'] = $value;
          break;
        case "LBBSNAME":
          $this->dataSet['defaultName'] = $value;
          break;
        case "POINTX":
          $this->dataSet['defaultX'] = $value;
          break;
        case "POINTY":
          $this->dataSet['defaultY'] = $value;
          break;
        case "COMMAND":
          $this->dataSet['defaultKind'] = $value;
          break;
        case "DEVELOPEMODE":
          $this->dataSet['defaultDevelopeMode'] = $value;
          break;
        case "SKIN":
          $this->dataSet['defaultSkin'] = $value;
          break;
        }
      }
    }
  }
  //---------------------------------------------------
  // COOKIEを生成
  //---------------------------------------------------
  function setCookies() {
    $time = time() + 30 * 86400; // 現在 + 30日有効

    // Cookieの設定 & POSTで入力されたデータで、Cookieから取得したデータを更新
    if($this->dataSet['ISLANDID'] && $this->mode == "owner") {
      setcookie("OWNISLANDID",$this->dataSet['ISLANDID'], $time);
      $this->dataSet['defaultID'] = $this->dataSet['ISLANDID'];
    }
    if($this->dataSet['PASSWORD']) {
      setcookie("OWNISLANDPASSWORD",$this->dataSet['PASSWORD'], $time);
      $this->dataSet['defaultPassword'] = $this->dataSet['PASSWORD'];
    }
    if($this->dataSet['TARGETID']) {
      setcookie("TARGETISLANDID",$this->dataSet['TARGETID'], $time);
      $this->dataSet['defaultTarget'] = $this->dataSet['TARGETID'];
    }
    if($this->dataSet['LBBSNAME']) {
      setcookie("LBBSNAME",$this->dataSet['LBBSNAME'], $time);
      $this->dataSet['defaultName'] = $this->dataSet['LBBSNAME'];
    }
    if($this->dataSet['POINTX']) {
      setcookie("POINTX",$this->dataSet['POINTX'], $time);
      $this->dataSet['defaultX'] = $this->dataSet['POINTX'];
    }
    if($this->dataSet['POINTY']) {
      setcookie("POINTY",$this->dataSet['POINTY'], $time);
      $this->dataSet['defaultY'] = $this->dataSet['POINTY'];
    }
    if($this->dataSet['COMMAND']) {
      setcookie("COMMAND",$this->dataSet['COMMAND'], $time);
      $this->dataSet['defaultKind'] = $this->dataSet['COMMAND'];
    }
    if($this->dataSet['DEVELOPEMODE']) {
      setcookie("DEVELOPEMODE",$this->dataSet['DEVELOPEMODE'], $time);
      $this->dataSet['defaultDevelopeMode'] = $this->dataSet['DEVELOPEMODE'];
    }
    if($this->dataSet['SKIN']) {
      setcookie("SKIN",$this->dataSet['SKIN'], $time);
      $this->dataSet['defaultSkin'] = $this->dataSet['SKIN'];
    }
  }
}


//--------------------------------------------------------------------
class Main {

  function execute() {
    $hako = new Hako;
    $cgi = new Cgi;
    
    $cgi->parseInputData();
    $cgi->getCookies();

    $lock = Util::lock($fp);
    if(FALSE == $lock) {
      exit;
    }

    if(!$hako->readIslands($cgi)) {
      HTML::header($cgi->dataSet);
      HakoError::noDataFile();
      HTML::footer();
      Util::unlock($lock);
      exit();
    }
    $cgi->setCookies();
    $cgi->lastModified();

    if($cgi->dataSet['DEVELOPEMODE'] == "java") {
      $html = new HtmlJS;
      $com = new MakeJS;
    } else {
      $html = new HtmlMap;
      $com = new Make;
    }
    switch($cgi->mode) {
    case "turn":
      $turn = new Turn;
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $turn->turnMain($hako, $cgi->dataSet); 
      $html->main($hako, $cgi->dataSet); // ターン処理後、TOPページopen
      $html->footer();
      break;
    case "owner":
      $html->header($cgi->dataSet);
      $html->owner($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "command":
      $html->header($cgi->dataSet);
      $com->commandMain($hako, $cgi->dataSet);
      $html->footer();
      break;
      
    case "new":
      $html->header($cgi->dataSet);
      $com->newIsland($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "comment":
      $html->header($cgi->dataSet);
      $com->commentMain($hako, $cgi->dataSet);
      $html->footer();
      break;
      
    case "print":
      $html->header($cgi->dataSet);
      $html->visitor($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "targetView":
      $html->header($cgi->dataSet);
      $html->printTarget($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "change":
      $html->header($cgi->dataSet);
      $com->changeMain($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "ChangeOwnerName":
      $html->header($cgi->dataSet);
      $com->changeOwnerName($hako, $cgi->dataSet);
      $html->footer();
      break;
    case "lbbs":
      $lbbs = new Make;
      $html->header($cgi->dataSet);
      $lbbs->localBbsMain($hako, $cgi->dataSet);
      $html->footer();
      break;
      
    case "skin":
      $html = new HtmlSetted;
      $html->header($cgi->dataSet);
      $html->setSkin();
      $html->footer();
      break;
    case "conf":
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $html->regist($hako);
      $html->footer();
      break;
      
    default: 
      $html = new HtmlTop;
      $html->header($cgi->dataSet);
      $html->main($hako, $cgi->dataSet);
      $html->footer();
    }
    Util::unlock($lock);
    exit();
  }
}
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    $start = new Main;
    $start->execute();
}
?>
