<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once dirname(__DIR__) . '/lib/repository.php';
require_once __DIR__ . '/partials/public_ui.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, max-age=0');

$ids = [];
foreach (explode(',', trim((string)($_GET['ids'] ?? ''))) as $value) {
    $id = filter_var(trim($value), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id !== false && !in_array((int)$id, $ids, true)) {
        $ids[] = (int)$id;
    }
    if (count($ids) >= 20) {
        break;
    }
}

if ($ids === []) {
    echo json_encode(['items' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$params = [];
$placeholders = [];
foreach ($ids as $index => $id) {
    $key = ':item_' . $index;
    $params[$key] = $id;
    $placeholders[] = $key;
}

$rowsById = [];
try {
    $stmt = db()->prepare(
        'SELECT items.* FROM items' .
        ' WHERE items.id IN (' . implode(',', $placeholders) . ')' .
        ' AND ' . items_front_release_where('items')
    );
    $stmt->execute($params);
    foreach ($stmt->fetchAll() ?: [] as $row) {
        $rowId = (int)($row['id'] ?? 0);
        if ($rowId > 0) {
            $rowsById[$rowId] = $row;
        }
    }
} catch (Throwable) {
    $rowsById = [];
}

$items = [];
foreach ($ids as $id) {
    $row = $rowsById[$id] ?? null;
    if (!is_array($row)) {
        continue;
    }

    $imageCandidates = pcf_item_image_candidates($row);
    $image = (string)($imageCandidates[0] ?? '');
    $items[] = [
        'id' => $id,
        'title' => pcf_item_title($row),
        'image' => $image,
        'image_fallbacks' => array_values(array_filter(
            array_slice($imageCandidates, 1),
            static fn($url): bool => trim((string)$url) !== '' && trim((string)$url) !== $image
        )),
        'url' => public_url('item.php?id=' . $id),
    ];
}

echo json_encode(
    ['items' => $items],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
