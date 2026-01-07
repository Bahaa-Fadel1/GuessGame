<?php
declare(strict_types=1);
session_start();
require_once DIR."/storage.php";
function h(string $s):string{return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}

$levels=[
 'easy'=>['label'=>'Easy','min'=>1,'max'=>20,'tries'=>8],
 'medium'=>['label'=>'Medium','min'=>1,'max'=>50,'tries'=>8],
 'hard'=>['label'=>'Hard','min'=>1,'max'=>100,'tries'=>7],
 'insane'=>['label'=>'Insane','min'=>1,'max'=>300,'tries'=>7],
];

function start_new_game(string $k,array $levels,array &$g):void{
 $lv=$levels[$k];
 $g=['level'=>$k,'min'=>$lv['min'],'max'=>$lv['max'],
 'secret'=>random_int($lv['min'],$lv['max']),
 'tries_left'=>$lv['tries'],'attempts'=>0,'last_diff'=>null,
 'won'=>false,'lost'=>false,'started_at'=>time()];
}

if(!isset($_SESSION['game'])) start_new_game('easy',$levels,$_SESSION['game']);
if(!isset($_SESSION['stats'])) $_SESSION['stats']=['wins'=>0,'streak'=>0,'best_attempts'=>null,'best_time'=>null];
$game=&$_SESSION['game']; $stats=&$_SESSION['stats'];

$msg=null;$msgClass=null;
$action=$_POST['action']??null;

if($action==='new'){
 $l=$_POST['level']??'easy';
 if(!isset($levels[$l]))$l='easy';
 start_new_game($l,$levels,$game);
 $msg="New game started 🎲"; $msgClass="ok";
}

if($action==='reset_all'){
 $_SESSION=[]; session_destroy();
 header("Location: index.php"); exit;
}

if($action==='guess' && !$game['won'] && !$game['lost']){
 $name=mb_substr(trim($_POST['name']??'Player'),0,20);
 if(!isset($_POST['guess'])||!is_numeric($_POST['guess'])){
  $msg="Enter a valid number"; $msgClass="warn";
 }else{
  $guess=(int)$_POST['guess'];
  if($guess<$game['min']||$guess>$game['max']){
   $msg="Guess between {$game['min']} and {$game['max']}"; $msgClass="warn";
  }else{
   $game['attempts']++; $game['tries_left']--;
   $diff=abs($guess-$game['secret']); $last=$game['last_diff']; $game['last_diff']=$diff;
   if($guess===$game['secret']){
    $game['won']=true; $stats['wins']++; $stats['streak']++;
    $time=max(1,time()-$game['started_at']);
    add_score($name,$levels[$game['level']]['label'],$game['attempts'],$time);
    $msg="🎉 {$name} WON! Attempts: {$game['attempts']} Time: {$time}s"; $msgClass="ok";
   }elseif($game['tries_left']<=0){
    $game['lost']=true; $stats['streak']=0;
    $msg="💥 Game over! Number was {$game['secret']}"; $msgClass="bad";
   }else{
    $trend='';
    if($last!==null){
     if($diff<$last)$trend=" getting warmer 🔥";
     elseif($diff>$last)$trend=" getting colder ❄️";
    }
    $msg=($guess<$game['secret']?'Higher ⬆️':'Lower ⬇️')." — Tries left: {$game['tries_left']}{$trend}";
    $msgClass="warn";
   }
  }
 }
}
$lv=$levels[$game['level']];
$rangeText="{$game['min']} - {$game['max']}";
$timeNow=max(0,time()-$game['started_at']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>GuessGame</title>
<link rel="stylesheet" href="/style.css"/>
</head>
<body>
<div class="wrap"><div class="shell">
<section class="card">
<div class="hd">
<div class="badge"><div class="logo"></div>
<div><h1>GuessGame</h1><p class="sub">Pick a difficulty & guess smart</p></div></div>
<div style="display:flex;gap:10px">
<a class="btn secondary" href="/leaderboard.php">Leaderboard</a>
<form method="post"><input type="hidden" name="action" value="reset_all">
<button class="btn danger">Reset</button></form></div></div>

<div class="kpis">
<div class="kpi"><div class="t">Difficulty</div><div class="v"><?=h($lv['label'])?></div></div>
<div class="kpi"><div class="t">Range</div><div class="v"><?=h($rangeText)?></div></div>
<div class="kpi"><div class="t">Tries</div><div class="v"><?=$game['tries_left']?></div></div>
<div class="kpi"><div class="t">Time</div><div class="v"><?=$timeNow?>s</div></div>
</div>

<?php if($msg): ?><div class="msg <?=h($msgClass)?>"><?=h($msg)?></div><?php endif; ?>

B B, [7/1/2026 4:12 م]
<form method="post" class="form row">
<input type="hidden" name="action" value="guess">
<input class="inp" name="name" placeholder="Your name">
<input class="inp" name="guess" type="number" min="<?=$game['min']?>" max="<?=$game['max']?>" placeholder="Your guess">
<button class="btn" <?=($game['won']||$game['lost'])?'disabled':''?>>Guess</button>
</form>

<form method="post" class="form row">
<input type="hidden" name="action" value="new">
<select name="level">
<?php foreach($levels as $k=>$i): ?>
<option value="<?=$k?>" <?=$k===$game['level']?'selected':''?>><?=$i['label']?></option>
<?php endforeach; ?>
</select>
<button class="btn secondary">New Game</button>
</form>
</section>
</div></div>
</body>
</html>
