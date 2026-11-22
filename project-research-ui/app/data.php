<?php
/* app/data.php - where page data comes from: the PHP arrays in app/mock-data.php. There is no database.
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.

   Views only call rows(), find(), user() and me(), so the pages never touch storage directly. A table
   whitelist keeps the lookups predictable. The mock never writes: every form shows a "mock only"
   message and saves nothing. */
defined('MOCK') || exit;

const DATA_TABLES = ['users', 'categories', 'ladder', 'articles', 'notes', 'media', 'comments', 'credit_ledger',
    'notifications', 'bulletins', 'rank_requests', 'support_messages', 'audit_log', 'email_outbox', 'store_items',
    'owned_items', 'cases', 'petitions', 'polls', 'glossary', 'faq'];

function mock_arrays(): array {
    static $a = null;
    return $a ??= require __DIR__ . '/mock-data.php';
}

function data_source_label(): string { return 'PHP arrays (no database)'; }

/* Every row of one table. */
function rows(string $table): array {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];
    if (!in_array($table, DATA_TABLES, true)) throw new InvalidArgumentException("Unknown mock table: $table");
    $list = mock_arrays()[$table] ?? [];
    if ($table === 'owned_items') {                // the array keeps it compact: user id => [item ids]
        $flat = [];
        foreach ($list as $uid => $items) foreach ($items as $i => $it) $flat[] = ['user_id' => $uid, 'item_id' => $it, 'equipped' => $i === 0 ? 1 : 0];
        $list = $flat;
    }
    return $cache[$table] = $list;
}
/* One row by a column value (default: id), or null. */
function find(string $table, $value, string $col = 'id'): ?array {
    if (!in_array($table, DATA_TABLES, true)) return null;
    foreach (rows($table) as $r) if ((string) ($r[$col] ?? '') === (string) $value) return $r;
    return null;
}
/* Rows matching a callback, newest first when the table has created_min. */
function where(string $table, ?callable $fn = null, int $limit = 0): array {
    $out = array_values($fn ? array_filter(rows($table), $fn) : rows($table));
    if ($out && array_key_exists('created_min', $out[0])) usort($out, fn ($a, $b) => $a['created_min'] <=> $b['created_min']);
    return $limit ? array_slice($out, 0, $limit) : $out;
}
function user(?int $id): ?array { return $id ? find('users', $id) : null; }
/* The mock member you are previewing as (null for a visitor). */
function me(): ?array {
    $id = mock_arrays()['demo_identity'][role_key()] ?? null;
    return $id ? user($id) : null;
}
function category_name(int $id): string { return find('categories', $id)['name'] ?? 'Uncategorised'; }
function published(string $table): array { return where($table, fn ($r) => $r['status'] === 'PUBLISHED'); }
