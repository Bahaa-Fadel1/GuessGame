<?php
declare(strict_types=1);
require_once _DIR_ . "/storage.php";

if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='clear_scores'){
    clear_scores();
    header("Location: leaderboard.php");
    exit;
}

$scores = top_scores(10);
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>GuessGame • Leaderboard</title>
<link rel="stylesheet" href="/style.css"/>
</head>
<body>
<div class="wrap"><div class="shell">
<section class="card">
<div class="hd">
<div class="badge">
<div class="logo"></div>
<div><h1>Leaderboard 🏆</h1><p class="sub">Top 10 best runs (lowest attempts, then fastest time).</p></div>
</div>
<div style="display:flex;gap:10px; flex-wrap:wrap">
<a class="btn secondary" href="/" style="text-decoration:none;">Back to Game</a>
<form method="post">
<input type="hidden" name="action" value="clear_scores">
<button class="btn danger" style="font-size:12px; padding:6px 10px;">Clear Scores</button>
</form>
</div>
</div>

<table class="table">
<thead>
<tr><th>#</th><th>Name</th><th>Level</th><th>Attempts</th><th>Time</th><th>Date</th></tr>
</thead>
<tbody>
<?php if(count($scores)===0): ?>
<tr><td colspan="6" style="text-align:center; opacity:.8;">No scores yet — be the first 🎯</td></tr>
<?php else: ?>
<?php foreach($scores as $i=>$s): ?>
<tr>
<td><?= $i+1 ?></td>
<td><?= h((string)$s["name"]) ?></td>
<td><span class="pill"><?= h((string)$s["level"]) ?></span></td>
<td><b><?= (int)$s["attempts"] ?></b></td>
<td><?= (int)$s["seconds"] ?>s</td>
<td><?= h((string)$s["at"]) ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</section>
</div></div>
</body>
</html>
