<?php
declare(strict_types=1);

function db_path(): string {
  return __DIR__ . "/scores.json";
}

function ensure_db_exists(): void {
  if (!file_exists(db_path())) {
    file_put_contents(db_path(), json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
  }
}

function read_scores(): array {
  ensure_db_exists();
  $data = json_decode(file_get_contents(db_path()), true);
  return is_array($data) ? $data : [];
}

function write_scores(array $scores): void {
  $scores = array_slice($scores, 0, 200);
  file_put_contents(db_path(), json_encode($scores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function add_score(string $name, string $level, int $attempts, int $seconds): void {
  $scores = read_scores();
  $scores[] = [
    "name" => $name,
    "level" => $level,
    "attempts" => $attempts,
    "seconds" => $seconds,
    "at" => date("Y-m-d H:i:s"),
  ];

  usort($scores, fn($a,$b) =>
    $a['attempts'] === $b['attempts']
      ? $a['seconds'] <=> $b['seconds']
      : $a['attempts'] <=> $b['attempts']
  );

  write_scores($scores);
}

function top_scores(int $limit = 10): array {
  return array_slice(read_scores(), 0, $limit);
}
