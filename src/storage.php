<?php

function db_path() {
  return __DIR__ . "/scores.json";
}

function ensure_db_exists() {
  $path = db_path();
  if (!file_exists($path)) {
    file_put_contents($path, json_encode(array(), JSON_PRETTY_PRINT));
  }
}

function read_scores() {
  ensure_db_exists();
  $raw = file_get_contents(db_path());
  $data = json_decode($raw ? $raw : "[]", true);
  return is_array($data) ? $data : array();
}

function write_scores($scores) {
  $scores = array_slice($scores, 0, 200);
  file_put_contents(db_path(), json_encode($scores, JSON_PRETTY_PRINT));
}

function add_score($name, $level, $attempts, $seconds) {
  $name = trim($name);
  if ($name === "") $name = "Player";
  if (function_exists('mb_substr')) $name = mb_substr($name, 0, 20);
  else $name = substr($name, 0, 20);

  $scores = read_scores();
  $scores[] = array(
    "name" => $name,
    "level" => $level,
    "attempts" => (int)$attempts,
    "seconds" => (int)$seconds,
    "at" => date("Y-m-d H:i:s"),
  );

  // sort: fewer attempts then less time (PHP5 compatible, no <=>)
  usort($scores, function($a, $b) {
    $aAtt = isset($a["attempts"]) ? (int)$a["attempts"] : 0;
    $bAtt = isset($b["attempts"]) ? (int)$b["attempts"] : 0;

    if ($aAtt === $bAtt) {
      $aSec = isset($a["seconds"]) ? (int)$a["seconds"] : 0;
      $bSec = isset($b["seconds"]) ? (int)$b["seconds"] : 0;
      if ($aSec === $bSec) return 0;
      return ($aSec < $bSec) ? -1 : 1;
    }

    return ($aAtt < $bAtt) ? -1 : 1;
  });

  write_scores($scores);
}

function top_scores($limit) {
  $scores = read_scores();
  return array_slice($scores, 0, (int)$limit);
}
