<?php
/**
 * OFTK – Read/write content JSON (news, documents, events, gallery, competitions)
 */

declare(strict_types=1);

if (!defined('OFTK_APP')) {
    die('Direct access not permitted.');
}

function oftk_read_json(string $path): array {
    if (!is_file($path)) {
        return [];
    }
    $raw = @file_get_contents($path);
    if ($raw === false) {
        return [];
    }
    $data = @json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function oftk_write_json(string $path, array $data): bool {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    return @file_put_contents($path, $json) !== false;
}

function oftk_get_news(): array { return oftk_read_json(OFTK_NEWS_FILE); }
function oftk_save_news(array $data): bool { return oftk_write_json(OFTK_NEWS_FILE, $data); }

function oftk_get_documents(): array { return oftk_read_json(OFTK_DOCUMENTS_FILE); }
function oftk_save_documents(array $data): bool { return oftk_write_json(OFTK_DOCUMENTS_FILE, $data); }

function oftk_get_events(): array { return oftk_read_json(OFTK_EVENTS_FILE); }
function oftk_save_events(array $data): bool { return oftk_write_json(OFTK_EVENTS_FILE, $data); }

function oftk_get_gallery(): array { return oftk_read_json(OFTK_GALLERY_FILE); }
function oftk_save_gallery(array $data): bool { return oftk_write_json(OFTK_GALLERY_FILE, $data); }

function oftk_get_competitions(): array { return oftk_read_json(OFTK_COMPETITIONS_FILE); }
function oftk_save_competitions(array $data): bool { return oftk_write_json(OFTK_COMPETITIONS_FILE, $data); }

function oftk_get_physiotherapists(): array { return oftk_read_json(OFTK_PHYSIOTHERAPISTS_FILE); }
function oftk_save_physiotherapists(array $data): bool { return oftk_write_json(OFTK_PHYSIOTHERAPISTS_FILE, $data); }

/** Generate next id (max id + 1) for a list of items with 'id' key. */
function oftk_next_id(array $items): string {
    $max = 0;
    foreach ($items as $item) {
        $id = isset($item['id']) ? (int) $item['id'] : 0;
        if ($id > $max) $max = $id;
    }
    return (string) ($max + 1);
}

/**
 * Next photo id across gallery (flat list or { albums: [...] }).
 */
function oftk_gallery_next_photo_id(array $data): string {
    $max = 0;
    if (isset($data['albums']) && is_array($data['albums'])) {
        foreach ($data['albums'] as $al) {
            if (!is_array($al)) {
                continue;
            }
            foreach ($al['photos'] ?? [] as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $id = isset($p['id']) ? (int) $p['id'] : 0;
                if ($id > $max) {
                    $max = $id;
                }
            }
        }
        return (string) ($max + 1);
    }
    foreach ($data as $item) {
        if (!is_array($item) || isset($item['albums'])) {
            continue;
        }
        $id = isset($item['id']) ? (int) $item['id'] : 0;
        if ($id > $max) {
            $max = $id;
        }
    }
    return (string) ($max + 1);
}
