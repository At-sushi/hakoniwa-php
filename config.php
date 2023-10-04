<?php
/*******************************************************************

  箱庭諸島２ for PHP

  - 初期設定用ファイル -
  
  $Id: config.php,v 1.7 2003/09/29 11:54:19 Watson Exp $

*******************************************************************/
define("GZIP", false);	// true: GZIP 圧縮転送を使用  false: 使用しない
define("DEBUG", true);	// true: デバッグ false: 通常
define("LOCK_RETRY_COUNT", 10);		// ファイルロック処理のリトライ回数
define("LOCK_RETRY_INTERVAL", 1000);// 再ロック処理実施までの時間(ミリ秒)。最低でも500くらいを指定

class Init {
  //----------------------------------------
  // 各種設定値
  //----------------------------------------
  // プログラムを置くディレクトリ
  var $baseDir		= "http://34.168.36.77/hakoniwa-php";

  // 画像を置くディレクトリ
  var $imgDir		= "http://34.168.36.77/hako_image";

  // CSSファイルを置くディレクトリ
  var $cssDir		= "http://34.168.36.77/hakoniwa-php/css";

  // CSSリスト
  var $cssList		= array('SkyBlue.css', 'Autumn.css');
  
  //パスワードの暗号化 true: 暗号化、false: 暗号化しない
  var $cryptOn		= true; 
  // マスターパスワード
  var $masterPassword	= "hogehoge";
  var $specialPassword	= "hogehogehoge";

  // DBログイン情報
  var $db_id = "postgres";
  var $db_pass = "";
  
  // データディレクトリの名前
  var $dirName		= "data";

  // ゲームタイトル
  var $title		= "箱庭諸島２";

  var $adminName	= "xxx";
  var $adminEmail	= "xxx@xxx.ne.jp";
  var $urlBbs		= "http://xxx.xxx.xxx/";
  var $urlTopPage	= "http://xxx.xxx.xxx/";

  // ディレクトリ作成時のパーミション
  var $dirMode		= 0755;
  
  // 1ターンが何秒か
  var $unitTime		= 60; // 6時間

  // 島の最大数
  var $maxIsland	= 100;

  // 資金表示モード
  var $moneyMode	= true; // true: 100の位で四捨五入, false: そのまま
  // トップページに表示するログのターン数
  var $logTopTurn	= 1;
  // ログファイル保持ターン数
  var $logMax		= 8;

  // バックアップを何ターンおきに取るか
  var $backupTurn	= 12;
  // バックアップを何回分残すか
  var $backupTimes	= 4;

  // 発見ログ保持行数
  var $historyMax	= 10;

  // 放棄コマンド自動入力ターン数
  var $giveupTurn	= 100;

  // コマンド入力限界数
  var $commandMax	= 20;

  // ローカル掲示板行数を使用するかどうか(false:使用しない、true:使用する)
  var $useBbs		= false;
  // ローカル掲示板行数
  var $lbbsMax		= 5;

  // 島の大きさ
  var $islandSize	= 12;

  // 初期資金
  var $initialMoney	= 100;
  // 初期食料
  var $initialFood	= 100;

  // 資金最大値
  var $maxMoney		= 9999;
  // 食料最大値
  var $maxFood		= 9999;

  // 人口の単位
  var $unitPop		= "00人";
  // 食料の単位
  var $unitFood		= "00トン";
  // 広さの単位
  var $unitArea		= "00万坪";
  // 木の数の単位
  var $unitTree		= "00本";
  // お金の単位
  var $unitMoney	= "億円";

  // 木の単位当たりの売値
  var $treeValue	= 5;

  // 名前変更のコスト
  var $costChangeName	= 500;

  // 人口1単位あたりの食料消費料
  var $eatenFood	= 0.2;

  // 油田の収入
  var $oilMoney		= 1000;
  // 油田の枯渇確率
  var $oilRatio		= 40;


  // ミサイル基地
  // 経験値の最大値
  var $maxExpPoint	= 200; // ただし、最大でも255まで

  // レベルの最大値
  var $maxBaseLevel	= 5; // ミサイル基地
  var $maxSBaseLevel	= 3; // 海底基地

  // 経験値がいくつでレベルアップか
  var $baseLevelUp	= array(20, 60, 120, 200); // ミサイル基地
  var $sBaseLevelUp	= array(50, 200);          // 海底基地

  // 怪獣に踏まれた時自爆するなら1、しないなら0
  var $dBaseAuto = 1;

  // 目標の島 所有の島が選択された状態でリストを生成 1、順位がTOPの島なら0
  // ミサイルの誤射が多い場合などに使用すると良いかもしれない
  var $targetIsland = 1;
  
  var $disEarthquake = 5;  // 地震
  var $disTsunami    = 15; // 津波
  var $disTyphoon    = 20; // 台風
  var $disMeteo      = 15; // 隕石
  var $disHugeMeteo  = 5;  // 巨大隕石
  var $disEruption   = 10; // 噴火
  var $disFire       = 10; // 火災
  var $disMaizo      = 10; // 埋蔵金

  // 地盤沈下
  var $disFallBorder = 90; // 安全限界の広さ(Hex数)
  var $disFalldown   = 30; // その広さを超えた場合の確率

  // 怪獣
  var $disMonsBorder1 = 1000; // 人口基準1(怪獣レベル1)
  var $disMonsBorder2 = 2500; // 人口基準2(怪獣レベル2)
  var $disMonsBorder3 = 4000; // 人口基準3(怪獣レベル3)
  var $disMonster     = 3;    // 単位面積あたりの出現率(0.01%単位)

  var $monsterLevel1  = 2; // サンジラまで    
  var $monsterLevel2  = 5; // いのらゴーストまで
  var $monsterLevel3  = 7; // キングいのらまで(全部)

  var $monsterNumber	= 8; // 怪獣の種類
  // 怪獣の名前
  var $monsterName	= array (
    'メカいのら',
    'いのら',
    'サンジラ',
    'レッドいのら',
    'ダークいのら',
    'いのらゴースト',
    'クジラ',
    'キングいのら'
    );
  // 怪獣の画像
  var $monsterImage	= array (
    'monster7.gif',
    'monster0.gif',
    'monster5.gif',
    'monster1.gif',
    'monster2.gif',
    'monster8.gif',
    'monster6.gif',
    'monster3.gif',
    );
  // 画像ファイルその2(硬化中)
  var $monsterImage2	= array ('', '', 'monster4.gif', '', '', '', 'monster4.gif', '');

  // 最低体力、体力の幅、特殊能力、経験値、死体の値段
  var $monsterBHP	= array( 2,   1,   1,    3,   2,   1,    4,    5,   3,   2,   3,    5,    6);
  var $monsterDHP	= array( 0,   2,   2,    2,   2,   0,    2,    2,   1,   2,   2,    2,    3);
  var $monsterSpecial	= array( 0,   0,   3,    6,   1,   2,    4,    7,   5,   1,   2,    9,   10);
  var $monsterExp	= array( 5,   5,   7,   12,  15,  10,   20,   30,  20,  15,  20,   50,  100);
  var $monsterValue	= array( 0, 400, 500, 1000, 800, 300, 1500, 2000, 500, 600, 800, 3000, 3500);

  // ターン杯を何ターン毎に出すか
  var $turnPrizeUnit	= 100;

  // 賞の名前
  var $prizeName	= array (
    'ターン杯',
    '繁栄賞',
    '超繁栄賞',
    '究極繁栄賞',
    '平和賞',
    '超平和賞',
    '究極平和賞',
    '災難賞',
    '超災難賞',
    '究極災難賞',
    );

  // 記念碑
  var $monumentNumber	= 1;
  var $monumentName	= array (
    'モノリス',
    );
  // 画像ファイル
  var $monumentImage = array (
    'monument0.gif',
    );

  /********************
      外見関係
   ********************/
  // 大きい文字
  var $tagBig_ = '<span class="big">';
  var $_tagBig = '</span>';
  // 島の名前など
  var $tagName_ = '<span class="islName">';
  var $_tagName = '</span>';
  // 薄くなった島の名前
  var $tagName2_ = '<span class="islName2">';
  var $_tagName2 = '</span>';
  // 順位の番号など
  var $tagNumber_ = '<span class="number">';
  var $_tagNumber = '</span>';
  // 順位表における見だし
  var $tagTH_ = '<span class="head">';
  var $_tagTH = '</span>';
  // 開発計画の名前
  var $tagComName_ = '<span class="command">';
  var $_tagComName = '</span>';
  // 災害
  var $tagDisaster_ = '<span class="disaster">';
  var $_tagDisaster = '</span>';
  // ローカル掲示板、観光者の書いた文字
  var $tagLbbsSS_ = '<span class="lbbsSS">';
  var $_tagLbbsSS = '</span>';
  // ローカル掲示板、島主の書いた文字
  var $tagLbbsOW_ = '<span class="lbbsOW">';
  var $_tagLbbsOW = '</span>';
  // 順位表、セルの属性
  var $bgTitleCell   = 'class="TitleCell"';   // 順位表見出し
  var $bgNumberCell  = 'class="NumberCell"';  // 順位表順位
  var $bgNameCell    = 'class="NameCell"';    // 順位表島の名前
  var $bgInfoCell    = 'class="InfoCell"';    // 順位表島の情報
  var $bgCommentCell = 'class="CommentCell"'; // 順位表コメント欄
  var $bgInputCell   = 'class="InputCell"';   // 開発計画フォーム
  var $bgMapCell     = 'class="MapCell"';     // 開発計画地図
  var $bgCommandCell = 'class="CommandCell"'; // 開発計画入力済み計画

  /********************
      地形番号
   ********************/

  var $landSea		=  0; // 海
  var $landWaste	=  1; // 荒地
  var $landPlains	=  2; // 平地
  var $landTown		=  3; // 町系
  var $landForest	=  4; // 森
  var $landFarm		=  5; // 農場
  var $landFactory	=  6; // 工場
  var $landBase		=  7; // ミサイル基地
  var $landDefence	=  8; // 防衛施設
  var $landMountain	=  9; // 山
  var $landMonster	= 10; // 怪獣
  var $landSbase	= 11; // 海底基地
  var $landOil		= 12; // 海底油田
  var $landMonument	= 13; // 記念碑
  var $landHaribote	= 14; // ハリボテ
  /********************
       コマンド
   ********************/
  // コマンド分割
  // このコマンド分割だけは、自動入力系のコマンドは設定しないで下さい。
  var $commandDivido = 
	array(
	'開発,0,10',  // 計画番号00〜10
	'建設,11,30', // 計画番号11〜30
	'攻撃,31,40', // 計画番号31〜40
	'運営,41,60'  // 計画番号41〜60
	);
  // 注意：スペースは入れないように
  // ○→	'開発,0,10',  # 計画番号00〜10
  // ×→	'開発, 0  ,10  ',  # 計画番号00〜10

  var $commandTotal	= 28; // コマンドの種類
  // 順序
  var $comList;
  // 整地系
  var $comPrepare	= 01; // 整地
  var $comPrepare2	= 02; // 地ならし
  var $comReclaim	= 03; // 埋め立て
  var $comDestroy	= 04; // 掘削
  var $comSellTree	= 05; // 伐採

  // 作る系
  var $comPlant		= 11; // 植林
  var $comFarm		= 12; // 農場整備
  var $comFactory	= 13; // 工場建設
  var $comMountain	= 14; // 採掘場整備
  var $comBase		= 15; // ミサイル基地建設
  var $comDbase		= 16; // 防衛施設建設
  var $comSbase		= 17; // 海底基地建設
  var $comMonument	= 18; // 記念碑建造
  var $comHaribote	= 19; // ハリボテ設置

  // 発射系
  var $comMissileNM	= 31; // ミサイル発射
  var $comMissilePP	= 32; // PPミサイル発射
  var $comMissileST	= 33; // STミサイル発射
  var $comMissileLD	= 34; // 陸地破壊弾発射
  var $comSendMonster	= 35; // 怪獣派遣

  // 運営系
  var $comDoNothing	= 41; // 資金繰り
  var $comSell		= 42; // 食料輸出
  var $comMoney		= 43; // 資金援助
  var $comFood		= 44; // 食料援助
  var $comPropaganda	= 45; // 誘致活動
  var $comGiveup	= 46; // 島の放棄

  // 自動入力系
  var $comAutoPrepare	= 61; // フル整地
  var $comAutoPrepare2	= 62; // フル地ならし
  var $comAutoDelete	= 63; // 全コマンド消去

  var $comName;
  var $comCost;

  // 島の座標数
  var $pointNumber;

  // 周囲2ヘックスの座標
  var $ax = array(0, 1, 1, 1, 0,-1, 0, 1, 2, 2, 2, 1, 0,-1,-1,-2,-1,-1, 0);
  var $ay = array(0,-1, 0, 1, 1, 0,-1,-2,-1, 0, 1, 2, 2, 2, 1, 0,-1,-2,-2);

  // コメントなどに、予¥定のように¥が勝手に追加される
  var $stripslashes;

  function setVariable() {
    $this->pointNumber = $this->islandSize * $this->islandSize;

    $this->comList	= array(
      $this->comPrepare,
      $this->comSell,
      $this->comPrepare2,
      $this->comReclaim,
      $this->comDestroy,
      $this->comSellTree,
      $this->comPlant,
      $this->comFarm,
      $this->comFactory,
      $this->comMountain,
      $this->comBase,
      $this->comDbase,
      $this->comSbase,
      $this->comMonument,
      $this->comHaribote,
      $this->comMissileNM,
      $this->comMissilePP,
      $this->comMissileST,
      $this->comMissileLD,
      $this->comSendMonster,
      $this->comDoNothing,
      $this->comMoney,
      $this->comFood,
      $this->comPropaganda,
      $this->comGiveup,
      $this->comAutoPrepare,
      $this->comAutoPrepare2,
      $this->comAutoDelete,
      );
    // 計画の名前と値段
    $this->comName[$this->comPrepare]      = '整地';
    $this->comCost[$this->comPrepare]      = 5;
    $this->comName[$this->comPrepare2]     = '地ならし';
    $this->comCost[$this->comPrepare2]     = 100;
    $this->comName[$this->comReclaim]      = '埋め立て';
    $this->comCost[$this->comReclaim]      = 150;
    $this->comName[$this->comDestroy]      = '掘削';
    $this->comCost[$this->comDestroy]      = 200;
    $this->comName[$this->comSellTree]     = '伐採';
    $this->comCost[$this->comSellTree]     = 0;
    $this->comName[$this->comPlant]        = '植林';
    $this->comCost[$this->comPlant]        = 50;
    $this->comName[$this->comFarm]         = '農場整備';
    $this->comCost[$this->comFarm]         = 20;
    $this->comName[$this->comFactory]      = '工場建設';
    $this->comCost[$this->comFactory]      = 100;
    $this->comName[$this->comMountain]     = '採掘場整備';
    $this->comCost[$this->comMountain]     = 300;
    $this->comName[$this->comBase]         = 'ミサイル基地建設';
    $this->comCost[$this->comBase]         = 300;
    $this->comName[$this->comDbase]        = '防衛施設建設';
    $this->comCost[$this->comDbase]        = 800;
    $this->comName[$this->comSbase]        = '海底基地建設';
    $this->comCost[$this->comSbase]        = 8000;
    $this->comName[$this->comMonument]     = '記念碑建造';
    $this->comCost[$this->comMonument]     = 9999;
    $this->comName[$this->comHaribote]     = 'ハリボテ設置';
    $this->comCost[$this->comHaribote]     = 1;
    $this->comName[$this->comMissileNM]    = 'ミサイル発射';
    $this->comCost[$this->comMissileNM]    = 20;
    $this->comName[$this->comMissilePP]    = 'PPミサイル発射';
    $this->comCost[$this->comMissilePP]    = 50;
    $this->comName[$this->comMissileST]    = 'STミサイル発射';
    $this->comCost[$this->comMissileST]    = 50;
    $this->comName[$this->comMissileLD]    = '陸地破壊弾発射';
    $this->comCost[$this->comMissileLD]    = 100;
    $this->comName[$this->comSendMonster]  = '怪獣派遣';
    $this->comCost[$this->comSendMonster]  = 3000;
    $this->comName[$this->comDoNothing]    = '資金繰り';
    $this->comCost[$this->comDoNothing]    = 0;
    $this->comName[$this->comSell]         = '食料輸出';
    $this->comCost[$this->comSell]         = -100;
    $this->comName[$this->comMoney]        = '資金援助';
    $this->comCost[$this->comMoney]        = 100;
    $this->comName[$this->comFood]         = '食料援助';
    $this->comCost[$this->comFood]         = -100;
    $this->comName[$this->comPropaganda]   = '誘致活動';
    $this->comCost[$this->comPropaganda]   = 1000;
    $this->comName[$this->comGiveup]       = '島の放棄';
    $this->comCost[$this->comGiveup]       = 0;
    $this->comName[$this->comAutoPrepare]  = '整地自動入力';
    $this->comCost[$this->comAutoPrepare]  = 0;
    $this->comName[$this->comAutoPrepare2] = '地ならし自動入力';
    $this->comCost[$this->comAutoPrepare2] = 0;
    $this->comName[$this->comAutoDelete]   = '全計画を白紙撤回';
    $this->comCost[$this->comAutoDelete]   = 0;

  }
  function Init() {
    $this->setVariable();
    mt_srand(time());
    // 日本時間にあわせる
    // 海外のサーバに設置する場合は次の行にある//をはずす。
    // putenv("TZ=JST-9");

    // 予¥定のように¥が勝手に追加される
    $this->stripslashes	= get_magic_quotes_gpc();
  }
}
?>
