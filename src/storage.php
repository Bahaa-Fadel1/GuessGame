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
