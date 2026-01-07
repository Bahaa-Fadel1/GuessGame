<?php
declare(strict_types=1);

function db_path(): string {
  return _DIR_ . "/scores.json";
}

function ensure_db_exists(): void {
  $path = db_path();
  if (!file_exists($path)) {
    file_put_contents(
      $path,
      json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
  }
}

function read_scores(): array {
  ensure_db_exists();
  $raw = file_get_contents(db_path());
  $data = json_decode($raw ?: "[]", true);
  return is_array($data) ? $data : [];
}

function write_scores(array $scores): void {
  $scores = array_slice($scores, 0, 200);
  file_put_contents(
    db_path(),
    json_encode($scores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
  );
}

function add_score(string $name, string $level, int $attempts, int $seconds): void {
  $name = trim($name);
  if ($name === "") $name = "Player";
  $name = mb_substr($name, 0, 20);

  $scores = read_scores();
  $scores[] = [
    "name" => $name,
    "level" => $level,
    "attempts" => $attempts,
    "seconds" => $seconds,
    "at" => date("Y-m-d H:i:s"),
  ];

  usort($scores, function ($a, $b) {
    if ($a["attempts"] === $b["attempts"]) {
      return $a["seconds"] <=> $b["seconds"];
    }
    return $a["attempts"] <=> $b["attempts"];
  });

  write_scores($scores);
}

function top_scores(int $limit = 10): array {
  $scores = read_scores();
  return array_slice($scores, 0, $limit);
}
Basma
<?php
declare(strict_types=1);
session_start();

require_once _DIR_ . "/storage.php";

function h(string $s): string {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

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

if (!isset($_SESSION['game'])) {
  start_new_game('easy', $levels, $_SESSION['game']);
}
if (!isset($_SESSION['stats'])) {
  $_SESSION['stats'] = [
    'wins'=>0,
    'streak'=>0,
    'best_attempts'=>null,
    'best_time'=>null
  ];
}

$game  =& $_SESSION['game'];
$stats =& $_SESSION['stats'];

$msg = null;
$msgClass = null;

$action = $_POST['action'] ?? null;

if ($action === 'new') {
  $level = $_POST['level'] ?? 'easy';
  if (!isset($levels[$level])) $level = 'easy';
  start_new_game($level, $levels, $game);
  $msg = "New game started 🎲";
  $msgClass = "ok";
}

if ($action === 'reset_all') {
  session_destroy();
  header("Location: /");
  exit;
}

if ($action === 'guess' && !$game['won'] && !$game['lost']) {
  $name = trim($_POST['name'] ?? 'Player');
  $name = mb_substr($name ?: 'Player', 0, 20);

  $guessRaw = $_POST['guess'] ?? '';

  if (!is_numeric($guessRaw)) {
    $msg = "Enter a valid number";
    $msgClass = "warn";
  } else {
    $guess = (int)$guessRaw;
    $game['attempts']++;
    $game['tries_left']--;

    if ($guess === $game['secret']) {
      $game['won'] = true;
      $stats['wins']++;
      $stats['streak']++;

      $timeSpent = time() - $game['started_at'];
      add_score($name, $levels[$game['level']]['label'], $game['attempts'], $timeSpent);

      $msg = "🎉 You won! Number was {$game['secret']}";
      $msgClass = "ok";
    } elseif ($game['tries_left'] <= 0) {
      $game['lost'] = true;
      $stats['streak'] = 0;
      $msg = "💥 Game over! Number was {$game['secret']}";
      $msgClass = "bad";
    } else {
      $msg = $guess < $game['secret'] ? "Try higher ⬆️" : "Try lower ⬇️";
      $msgClass = "warn";
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>GuessGame</title>
</head>
<body>
<h1>GuessGame</h1>

<?php if ($msg): ?>
  <p><b><?=h($msg)?></b></p>
<?php endif; ?>

<form method="post">
  <input type="hidden" name="action" value="guess">
  <input name="name" placeholder="Your name">
  <input name="guess" type="number">
  <button>Guess</button>
</form>

<form method="post">
  <input type="hidden" name="action" value="new">
  <button>New Game</button>
</form>

</body>
</html>
