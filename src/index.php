<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . "/storage.php";

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$levels = [
  'easy'   => ['label'=>'Easy',   'min'=>1, 'max'=>20,  'tries'=>8],
  'medium' => ['label'=>'Medium', 'min'=>1, 'max'=>50,  'tries'=>8],
  'hard'   => ['label'=>'Hard',   'min'=>1, 'max'=>100, 'tries'=>7],
  'insane' => ['label'=>'Insane', 'min'=>1, 'max'=>300, 'tries'=>7],
];

function start_new_game(string $levelKey, array $levels, array &$game): void {
  $lv = $levels[$levelKey];
  $game = [
    'level' => $levelKey,
    'min' => $lv['min'],
    'max' => $lv['max'],
    'secret' => random_int($lv['min'], $lv['max']),
    'tries_left' => $lv['tries'],
    'attempts' => 0,
    'last_diff' => null,
    'won' => false,
    'lost' => false,
    'started_at' => time(),
  ];
}

// init
if (!isset($_SESSION['game'])) start_new_game('easy', $levels, $_SESSION['game']);
if (!isset($_SESSION['stats'])) $_SESSION['stats'] = ['wins'=>0,'streak'=>0,'best_attempts'=>null,'best_time'=>null];

$game =& $_SESSION['game'];
$stats =& $_SESSION['stats'];

$msg = null; $msgClass = null;

$action = $_POST['action'] ?? null;

if ($action === 'new') {
  $level = $_POST['level'] ?? 'easy';
  if (!isset($levels[$level])) $level = 'easy';
  start_new_game($level, $levels, $game);
  $msg = "New game started on {$levels[$level]['label']} 🎲";
  $msgClass = "ok";
}

if ($action === 'reset_all') {
  session_destroy();
  header("Location: /");
  exit;
}

if ($action === 'guess' && !$game['won'] && !$game['lost']) {
  $name = trim((string)($_POST['name'] ?? ''));
  if ($name === '') $name = 'Player';
  $name = mb_substr($name, 0, 20);

  $guessRaw = $_POST['guess'] ?? '';
  if ($guessRaw === '' || !is_numeric($guessRaw)) {
    $msg = "Please enter a valid number 🙂";
    $msgClass = "warn";
  } else {
    $guess = (int)$guessRaw;

    if ($guess < $game['min'] || $guess > $game['max']) {
      $msg = "Your guess must be between {$game['min']} and {$game['max']} 🙃";
      $msgClass = "warn";
    } else {
      $game['attempts'] += 1;
      $game['tries_left'] -= 1;

      $diff = abs($guess - $game['secret']);
      $lastDiff = $game['last_diff'];
      $game['last_diff'] = $diff;

      if ($guess === $game['secret']) {
        $game['won'] = true;
        $stats['wins'] += 1;
        $stats['streak'] += 1;

        $timeSpent = max(1, time() - $game['started_at']);

        if ($stats['best_attempts'] === null || $game['attempts'] < $stats['best_attempts']) {
          $stats['best_attempts'] = $game['attempts'];
        }
        if ($stats['best_time'] === null || $timeSpent < $stats['best_time']) {
          $stats['best_time'] = $timeSpent;
        }

        // Save score to JSON leaderboard
        $levelLabel = $levels[$game['level']]['label'];
        add_score($name, $levelLabel, $game['attempts'], $timeSpent);

        $msg = "🎉 " . h($name) . " WON! Number: {$game['secret']} — Attempts: {$game['attempts']} — Time: {$timeSpent}s";
        $msgClass = "ok";
      } else {
        if ($game['tries_left'] <= 0) {
          $game['lost'] = true;
          $stats['streak'] = 0;
          $msg = "💥 Game over! The number was {$game['secret']}. Start a new game and try again!";
          $msgClass = "bad";
        } else {
          $hotCold = '';
          if ($diff <= 2) $hotCold = "🔥 Very hot";
          else if ($diff <= 6) $hotCold = "♨️ Hot";
          else if ($diff <= 12) $hotCold = "🌤️ Warm";
          else if ($diff <= 25) $hotCold = "❄️ Cold";
          else $hotCold = "🧊 Very cold";

          $trend = '';
          if ($lastDiff !== null) {
            if ($diff < $lastDiff) $trend = " — getting warmer ✅";
            else if ($diff > $lastDiff) $trend = " — getting colder 🥶";
            else $trend = " — same distance 😅";
          }

          $highLow = $guess < $game['secret'] ? "Try higher ⬆️" : "Try lower ⬇️";
          $msg = "{$hotCold}{$trend}. {$highLow} — Tries left: {$game['tries_left']}.";
          $msgClass = "warn";
        }
      }
    }
  }
}

$levelKey = $game['level'];
$lv = $levels[$levelKey];
$rangeText = "{$game['min']} - {$game['max']}";
$bestAttempts = $stats['best_attempts'] ?? "—";
$bestTime = $stats['best_time'] ?? "—";
$timeNow = max(0, time() - $game['started_at']);

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
<div class="wrap">
  <div class="shell">

    <section class="card">
      <div class="hd" style="z-index:2;">
        <div class="badge">
          <div class="logo"></div>
          <div>
            <h1>GuessGame</h1>
            <p class="sub">Pick a difficulty, guess smart, and climb the leaderboard.</p>
          </div>
        </div>

        <div style="display:flex; gap:10px; align-items:center; z-index:2;">
          <a class="btn secondary" href="/leaderboard.php" style="text-decoration:none;">Leaderboard</a>
          <form method="post" style="margin:0;">
            <input type="hidden" name="action" value="reset_all"/>
            <button class="btn danger" type="submit" title="Reset everything">Reset</button>
          </form>
        </div>
      </div>

      <div class="kpis" style="position:relative; z-index:2;">
        <div class="kpi"><div class="t">Difficulty</div><div class="v"><?=h($lv['label'])?></div></div>
        <div class="kpi"><div class="t">Range</div><div class="v"><?=h($rangeText)?></div></div>
        <div class="kpi"><div class="t">Tries Left</div><div class="v"><?= (int)$game['tries_left'] ?></div></div>
        <div class="kpi"><div class="t">Time</div><div class="v"><?= (int)$timeNow ?>s</div></div>
      </div>

      <?php if ($msg): ?>
        <div class="msg <?=h($msgClass ?? '')?>" style="position:relative; z-index:2;">
          <?= h($msg) ?>
        </div>
      <?php endif; ?>

      <div class="form" style="position:relative; z-index:2;">
        <form method="post" class="row" autocomplete="off">
          <input type="hidden" name="action" value="guess"/>
          <input class="inp" name="name" placeholder="Your name (for scoreboard)" maxlength="20"/>
          <input class="inp" name="guess" type="number" min="<?=$game['min']?>" max="<?=$game['max']?>" placeholder="Enter your guess…"/>
          <div class="actions" style="grid-column:1/-1;">
            <button class="btn" type="submit" <?=($game['won']||$game['lost'])?'disabled':''?>>Guess</button>
          </div>
        </form>

        <form method="post" class="row" style="margin-top:10px;">
          <input type="hidden" name="action" value="new"/>
          <select name="level">
            <?php foreach ($levels as $k=>$info): ?>
              <option value="<?=h($k)?>" <?= $k===$levelKey ? 'selected' : '' ?>>
                <?=h($info['label'])?> (<?=h((string)$info['min'])?>-<?=h((string)$info['max'])?>, <?=h((string)$info['tries'])?> tries)
              </option>
            <?php endforeach; ?>
          </select>
          <div class="actions">
            <button class="btn secondary" type="submit">New Game</button>
          </div>
        </form>

        <div class="small">
          Attempts: <b><?= (int)$game['attempts'] ?></b> • Wins: <b><?= (int)$stats['wins'] ?></b> • Streak: <b><?= (int)$stats['streak'] ?></b> • Best Attempts: <b><?= h((string)$bestAttempts) ?></b> • Best Time: <b><?= h((string)$bestTime) ?>s</b>
        </div>
      </div>
    </section>

    <aside class="card">
      <div class="hd" style="position:relative; z-index:2;">
        <div>
          <h1>How to Win</h1>
          <p class="sub">Use hints: hot/cold + higher/lower + warmer/colder.</p>
        </div>
        <span class="pill">Fancier UI</span>
      </div>

      <div class="msg ok" style="position:relative; z-index:2;">
        🏆 Winning saves your score automatically to the leaderboard.
      </div>

      <div class="msg" style="position:relative; z-index:2;">
        ✅ If you see “Unable to connect”, start Docker and container:
        <div class="small" style="margin-top:8px;">
          <code>sudo systemctl start docker</code><br/>
          <code>sudo docker start guessgame_app</code>
        </div>
      </div>

      <div class="small" style="position:relative; z-index:2;">
        Tip: On “Insane”, small improvements matter. Try to beat your best attempts/time.
      </div>
    </aside>

  </div>
</div>
</body>
</html>
