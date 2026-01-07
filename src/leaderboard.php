<?php
declare(strict_types=1);

require_once _DIR_ . "/storage.php";

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$top = top_scores(10);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>GuessGame - Leaderboard</title>
  <link rel="stylesheet" href="/style.css"/>
</head>
<body>
<div class="wrap">
  <div class="shell">
    <section class="card">
      <div class="hd">
        <div>
          <h1>Leaderboard</h1>
          <p class="sub">Top 10 scores (fewest attempts, then fastest time).</p>
        </div>
        <a class="btn secondary" href="/" style="text-decoration:none;">Back</a>
      </div>

      <div style="margin-top:14px;">
        <?php if (!$top): ?>
          <div class="msg warn">No scores yet. Win a game to appear here 🎯</div>
        <?php else: ?>
          <table style="width:100%; border-collapse:collapse;">
            <thead>
              <tr>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(0,0,0,.15);">#</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(0,0,0,.15);">Name</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(0,0,0,.15);">Level</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(0,0,0,.15);">Attempts</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(0,0,0,.15);">Time (s)</th>
                <th style="text-align:left; padding:10px; border-bottom:1px solid rgba(0,0,0,.15);">Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($top as $i=>$row): ?>
                <tr>
                  <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);"><?= (int)($i+1) ?></td>
                  <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);"><?= h((string)($row['name'] ?? '')) ?></td>
                  <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);"><?= h((string)($row['level'] ?? '')) ?></td>
                  <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);"><?= (int)($row['attempts'] ?? 0) ?></td>
                  <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);"><?= (int)($row['seconds'] ?? 0) ?></td>
                  <td style="padding:10px; border-bottom:1px solid rgba(0,0,0,.08);"><?= h((string)($row['at'] ?? '')) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    </section>
  </div>
</div>
</body>
</html>
