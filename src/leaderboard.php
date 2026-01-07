<?php
declare(strict_types=1);
require_once __DIR__ . "/storage.php";
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
<div class="wrap">
  <div class="shell" style="grid-template-columns:1fr">
    <section class="card">
      <div class="hd" style="position:relative; z-index:2;">
        <div class="badge">
          <div class="logo"></div>
          <div>
            <h1>Leaderboard 🏆</h1>
            <p class="sub">Top 10 best runs (lowest attempts, then fastest time).</p>
          </div>
        </div>
        <a class="btn secondary" href="/" style="text-decoration:none;">Back to Game</a>
      </div>

      <table class="table" style="position:relative; z-index:2;">
        <thead>
          <tr>
            <th>#</th><th>Name</th><th>Level</th><th>Attempts</th><th>Time</th><th>Date</th>
          </tr>
        </thead>
        <tbody>
        <?php if (count($scores) === 0): ?>
          <tr>
            <td colspan="6" style="border-radius:16px; text-align:center; opacity:.8;">
              No scores yet — be the first 🎯
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($scores as $i => $s): ?>
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
  </div>
</div>
</body>
</html>
