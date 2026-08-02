<?php

declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/repository.php';
require_once __DIR__ . '/../lib/crawler_guard.php';

pcf_crawler_guard_check();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: private, max-age=300');

function actress_profile_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{"success":false}';
    exit;
}

function actress_profile_display_values(array $profile): array
{
    $display = [];
    foreach (['ruby', 'prefectures', 'hobby', 'bust', 'cup', 'waist', 'hip', 'height', 'blood_type'] as $key) {
        $value = trim((string)($profile[$key] ?? ''));
        $display[$key] = $value !== '' ? $value : '未登録';
    }
    $birthday = trim((string)($profile['birthday'] ?? ''));
    $display['birthday'] = $birthday !== '' ? format_date($birthday) : '未登録';
    return $display;
}

function actress_profile_image_url(array $profile): string
{
    foreach (['image_large', 'image_small', 'image_url'] as $key) {
        $value = trim((string)($profile[$key] ?? ''));
        if ($value !== '') {
            return $value;
        }
    }
    return '';
}

function actress_profile_success(array $profile): void
{
    actress_profile_json_response([
        'success' => true,
        'display' => actress_profile_display_values($profile),
        'image_url' => actress_profile_image_url($profile),
    ]);
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!is_int($id) || $id <= 0) {
    actress_profile_json_response(['success' => false], 400);
}

try {
    $row = fetch_actress($id);
} catch (Throwable) {
    $row = null;
}
if (!is_array($row)) {
    actress_profile_json_response(['success' => false], 404);
}

$dmmId = trim((string)($row['duga_id'] ?? ''));
$name = trim((string)($row['name'] ?? ''));
if ($name === '' || !ctype_digit($dmmId)) {
    actress_profile_json_response(['success' => false], 404);
}

$profile = [
    'duga_id' => $dmmId,
    'name' => $name,
    'ruby' => (string)($row['ruby'] ?? ''),
    'birthday' => (string)($row['birthday'] ?? ''),
    'prefectures' => (string)($row['prefectures'] ?? ''),
    'image_url' => (string)($row['image_url'] ?? ''),
    'image_small' => (string)($row['image_small'] ?? ''),
    'image_large' => (string)($row['image_large'] ?? ''),
    'bust' => '',
    'cup' => '',
    'waist' => '',
    'hip' => '',
    'height' => '',
    'blood_type' => '',
    'hobby' => '',
];

$cacheKey = 'public.actress.profile.v1.' . $id;
$cacheTtl = 7 * 24 * 60 * 60;
try {
    $cached = json_decode((string)(setting_get($cacheKey, '') ?? ''), true);
    $cachedAt = is_array($cached) ? (int)($cached['cached_at'] ?? 0) : 0;
    $cachedProfile = is_array($cached) ? ($cached['profile'] ?? null) : null;
    if ($cachedAt >= time() - $cacheTtl && is_array($cachedProfile)) {
        foreach (array_keys($profile) as $key) {
            if (array_key_exists($key, $cachedProfile)) {
                $profile[$key] = (string)$cachedProfile[$key];
            }
        }
        actress_profile_success($profile);
    }
} catch (Throwable) {
}

actress_profile_success($profile);
