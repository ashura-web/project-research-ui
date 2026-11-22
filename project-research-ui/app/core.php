<?php
/* app/core.php - shared helpers for the research mock.
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.

   Most of this file is ADAPTED FROM THE REAL PROJECT (marked "reused"). Only code that has nothing to do
   with real data was carried over: escaping, icons, the wordmark, CSRF tokens, flash messages, the form
   validator and the field-error helpers. Everything that touched accounts, passwords, email, OAuth, uploads
   or the production database was left out on purpose. */
defined('MOCK') || exit;

/* XAMPP ships mbstring, but some minimal PHP builds do not. These two fallbacks keep the mock running anyway. */
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $s): int { return (int) preg_match_all('/./us', $s); }
}
if (!function_exists('mb_strimwidth')) {
    function mb_strimwidth(string $s, int $start, int $width, string $marker = ''): string {
        preg_match_all('/./us', $s, $m); $c = $m[0];
        return count($c) <= $width ? $s : implode('', array_slice($c, $start, $width - (int) preg_match_all('/./us', $marker))) . $marker;
    }
}

// ---- 1. ROLES AND CAPABILITIES ---------------------------------------------------------------
/* key => [title, badge colour, rank]. Colours reused from the real role_color() ("spectral class":
   a star gets hotter as it climbs). The real titles were themed; the mock uses plain ones. */
const ROLES = [
    'guest'       => ['Visitor',     '#555a63', 0],
    'member'      => ['Member',      '#a3402b', 1],
    'contributor' => ['Contributor', '#b25a0e', 2],
    'reviewer'    => ['Reviewer',    '#8a6a00', 3],
    'admin'       => ['Admin',       '#2f6690', 4],
    'owner'       => ['Owner',       '#7b4bb3', 5],
];
/* What each rank unlocks (shown on signup, rank request and about). Reused idea: role_unlocks(). */
const ROLE_UNLOCKS = [
    'member'      => 'Read everything, comment, write short notes, upload media, sign petitions, buy cosmetics.',
    'contributor' => 'Everything above, plus full articles through the step-by-step editor and a custom avatar.',
    'reviewer'    => 'Everything above, plus the review queue, Contributor rank requests and bulletins.',
    'admin'       => 'Users, ranks, suspensions, the support inbox, the audit log and the email outbox.',
    'owner'       => 'Everything above, plus Admin appointments, governance decisions and removing any post.',
];

function role_key(): string {
    $r = $_SESSION['preview_role'] ?? DEFAULT_ROLE;
    return isset(ROLES[$r]) ? $r : 'guest';
}
function role_rank(?string $r = null): int { return ROLES[$r ?? role_key()][2]; }
function role_title(string $r): string { return ROLES[$r][0] ?? 'Visitor'; }
function role_color(string $r): string { return ROLES[$r][1] ?? '#555a63'; }
function signed_in(): bool { return role_rank() > 0; }
function at_least(string $r): bool { return role_rank() >= ROLES[$r][2]; }

// ---- 2. ESCAPING, TIME, FORM ERRORS (reused) --------------------------------------------------
function e(?string $s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

/* "3 h ago" formatter. Mock dates are stored as "minutes before now", so the mock never looks stale. */
function ago(int $minutes): string {
    if ($minutes < 1)     return 'just now';
    if ($minutes < 60)    return $minutes . ' min ago';
    if ($minutes < 1440)  return floor($minutes / 60) . ' h ago';
    if ($minutes < 10080) return floor($minutes / 1440) . ' d ago';
    return date('M j, Y', time() - $minutes * 60);
}
function when(int $minutes, string $fmt = 'M j, Y g:ia'): string { return date($fmt, time() - $minutes * 60); }

function invalid_attr(array $errors, string $key, string $id): string {
    return isset($errors[$key]) ? ' aria-invalid="true" aria-describedby="' . e($id) . '-error"' : '';
}
function invalid_class(array $errors, string $key): string { return isset($errors[$key]) ? ' is-invalid' : ''; }
function field_error(array $errors, string $key, string $id): string {
    return isset($errors[$key])
        ? '<p class="field-error" id="' . e($id) . '-error" data-server-error>' . e($errors[$key]) . '</p>' : '';
}

// ---- 3. URLS --------------------------------------------------------------------------------
/* Links work three ways without changing any view:
   - Apache with mod_rewrite (XAMPP default): about, article?slug=x   (.htaccess sets MOCK_REWRITE)
   - Apache without mod_rewrite:              index.php?page=about&slug=x
   - tools/export-static.php:                 about.html (for GitHub Pages, query strings dropped) */
function pretty_urls(): bool {
    static $p = null;
    return $p ??= PHP_SAPI === 'cli-server' || getenv('MOCK_REWRITE') || getenv('REDIRECT_MOCK_REWRITE')
                  || !empty($_SERVER['MOCK_REWRITE']) || !empty($_SERVER['REDIRECT_MOCK_REWRITE']);
}
function url(string $page, array $q = []): string {
    if (defined('STATIC_EXPORT')) return ($page === 'home' ? 'index' : $page) . '.html';
    if (pretty_urls()) {
        $u = $page === 'home' ? './' : $page;
        return $q ? $u . '?' . http_build_query($q, '', '&amp;') : $u;
    }
    if ($page === 'home' && !$q) return 'index.php';
    return 'index.php?' . http_build_query(['page' => $page] + $q, '', '&amp;');
}
function redirect(string $page, array $q = []): never {
    header('Location: ' . html_entity_decode(url($page, $q)), true, 303);
    exit;
}

// ---- 4. CSRF + FLASH (reused unchanged from the real Backend/classes.php) ----------------------
final class Csrf {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }
    public static function field(): string {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }
    public static function check(): bool {
        $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return is_string($sent) && $sent !== '' && hash_equals(self::token(), $sent);
    }
}
final class Flash {
    public static function set(string $type, string $message): void { $_SESSION['flash'][] = ['type' => $type, 'message' => $message]; }
    public static function pull(): array { $all = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $all; }
}

// ---- 5. VALIDATOR (reused; the same rules the real site ran on the server) ---------------------
class Validator {
    public const PATTERNS = [
        'username' => '/^[A-Za-z0-9\-_]{3,50}$/',
        'callsign' => "/^[A-Za-z0-9 .'_\\-]{2,50}$/",
        'password' => '/^(?=.*[A-Za-z])(?=.*\d).{8,}$/',
        'code6'    => '/^\d{6}$/',
        'designation' => '/^[A-Za-z0-9\/\-. ]{1,50}$/',
    ];
    protected array $errors = [];
    public function __construct(protected array $data) {}
    public function value(string $f): string { return trim((string) ($this->data[$f] ?? '')); }
    protected function add(string $f, string $m): static { $this->errors[$f] ??= $m; return $this; }
    public function required(string $f, string $label): static { return $this->value($f) === '' ? $this->add($f, "$label is required.") : $this; }
    public function length(string $f, int $min, int $max, string $label): static {
        $n = mb_strlen($this->value($f));
        if ($n < $min) return $this->add($f, "$label needs at least $min characters (currently $n).");
        if ($n > $max) return $this->add($f, "$label is limited to $max characters (currently $n).");
        return $this;
    }
    public function email(string $f, string $label = 'Email'): static {
        return filter_var($this->value($f), FILTER_VALIDATE_EMAIL) ? $this : $this->add($f, "A valid $label address is required.");
    }
    public function url(string $f, string $label = 'Link'): static {
        $v = $this->value($f);
        if ($v === '') return $this;
        return (filter_var($v, FILTER_VALIDATE_URL) && preg_match('~^https?://~i', $v)) ? $this : $this->add($f, "$label must be a valid http(s) URL.");
    }
    public function pattern(string $f, string $key, string $m): static {
        $v = $this->value($f);
        return $v === '' || preg_match(self::PATTERNS[$key], $v) ? $this : $this->add($f, $m);
    }
    public function in(string $f, array $allowed, string $m): static { return in_array($this->value($f), $allowed, true) ? $this : $this->add($f, $m); }
    public function matches(string $f, string $other, string $m): static { return ($this->data[$f] ?? '') === ($this->data[$other] ?? '') ? $this : $this->add($f, $m); }
    public function fails(): bool { return $this->errors !== []; }
    public function errors(): array { return $this->errors; }
}

// ---- 6. BRAND + ICONS (reused) -----------------------------------------------------------------
/* One span per letter so style.css can light the letters in sequence (reused from brand_wordmark()). */
function brand_wordmark(): string {
    $out = '';
    foreach (preg_split('//u', APP_NAME, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $i => $ch) {
        $out .= '<span class="bn-l" aria-hidden="true" style="--i:' . $i . '">' . ($ch === ' ' ? '&nbsp;' : e($ch)) . '</span>';
    }
    return $out;
}
/* Neutral placeholder mark (the real logo is not part of the research repo). */
function brand_mark(int $size = 30): string {
    return '<svg viewBox="0 0 32 32" width="' . $size . '" height="' . $size . '" aria-hidden="true"><rect x="3" y="3" width="26" height="26" rx="8" fill="none" stroke="#c9c4be" stroke-width="2"/><circle cx="16" cy="16" r="5" fill="#8fb0e8"/></svg>';
}

/* The real icon set, copied path for path from the real nav_icon(). 'sliders' is the real Display
   settings icon (three vertical sliders); the first research build wrongly used a sun. */
function nav_icon(string $name): string {
    static $icons = [
        'home'     => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
        'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5"/><path d="M12 8h.01"/>',
        'image'    => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M4 19l6-6 4 4 3-3 3 3"/>',
        'audio'    => '<path d="M3 12h2"/><path d="M7 8v8"/><path d="M11 5v14"/><path d="M15 9v6"/><path d="M19 7v10"/><path d="M21 12h0"/>',
        'video'    => '<rect x="2" y="5" width="13" height="14" rx="2"/><path d="M15 10l7-4v12l-7-4"/>',
        'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'inbox'    => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/>',
        'plus'     => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8"/><path d="M8 12h8"/>',
        'check'    => '<circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/>',
        'trash'    => '<path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/>',
        'grid'     => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
        'users'    => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M16 3a4 4 0 0 1 0 8"/><path d="M22 21c0-3-1.5-5-4-5.7"/>',
        'enter'    => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/>',
        'userplus' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-4 3-6 7-6s7 2 7 6"/><path d="M19 8v6"/><path d="M16 11h6"/>',
        'exit'     => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'shield'   => '<path d="M12 3l7 3v5c0 4.6-3 7.6-7 9-4-1.4-7-4.4-7-9V6z"/><path d="M12 8v4"/><path d="M12 15h.01"/>',
        'search'   => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'book'     => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M9 7h7"/><path d="M9 11h5"/>',
        'bell'     => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'sliders'  => '<path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/>',
        'scales'   => '<path d="M12 3v18"/><path d="M5 7h14"/><path d="M5 7l-3 6a3.5 3.5 0 0 0 6 0z"/><path d="M19 7l3 6a3.5 3.5 0 0 1-6 0z"/><path d="M8 21h8"/>',
        'gavel'    => '<path d="M14 4l6 6"/><path d="M10 8l6 6"/><path d="M3 21h9"/><path d="M6 13l5 5"/><path d="M14 4l-4-2 -2 2 4 4 2-4z"/>',
        'succession' => '<path d="M4 13l1.6-7 4.2 3.6L12 4l2.2 5.6L18.4 6 20 13z"/><path d="M4 16h16"/><path d="M3 20h14"/><path d="M14 17.5l3 2.5-3 2.5"/>',
        'credits'  => '<rect x="2.6" y="6.4" width="18.8" height="11.2" rx="2.2"/><circle cx="12" cy="12" r="2.7"/><path d="M6.2 12h.01"/><path d="M17.8 12h.01"/>',
        'alert'    => '<path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'chat'     => '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
        'chevron'  => '<path d="M6 9l6 6 6-6"/>',
        'shuffle'  => '<path d="M16 3h5v5"/><path d="M4 20 21 3"/><path d="M21 16v5h-5"/><path d="M15 15l6 6"/><path d="M4 4l5 5"/>',
        'pen'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>',
        'star'     => '<path d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1 6.2L12 17.3 6.5 20.2l1-6.2L3 9.6l6.2-.9z"/>',
        'keyboard' => '<rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'bag'      => '<path d="M6 7h12l1 13H5z"/><path d="M9 7V5a3 3 0 0 1 6 0v2"/>',
        'link'     => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'megaphone' => '<path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M15.5 8.5a5 5 0 0 1 0 7"/><path d="M19 5a9 9 0 0 1 0 14"/>',
        'eye'      => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'database' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
    ];
    $d = $icons[$name] ?? $icons['home'];
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"'
         . ' stroke-linecap="round" stroke-linejoin="round" class="nav-icon" aria-hidden="true">' . $d . '</svg>';
}

// ---- 7. MEMBER CHIP (the identity block shown next to every name) ------------------------------
function initials(string $name): string {
    $p = preg_split('/[_\-. ]+/', $name) ?: [$name];
    $s = strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 2) : substr($p[0], 1, 1)));
    return $s !== '' ? $s : '?';
}
function avatar_html(?array $u, string $size = 'sm'): string {
    $name = $u['username'] ?? 'Guest';
    $hue = $u ? (crc32($name) % 360) : 225;
    return '<span class="av av-' . $size . '" aria-hidden="true"><span class="av-disc" style="background:hsl(' . $hue . ' 28% 40%)"><span class="av-initials">'
         . e(initials($name)) . '</span></span></span>';
}
function badge_html(string $role): string {
    return '<span class="badge-level' . ($role === 'owner' ? ' badge-owner' : '') . '" style="background:' . role_color($role) . '">' . e(role_title($role)) . '</span>';
}
/* <a class="member-chip"> with avatar, name, rank badge and an optional label ("Written by"). */
function member_chip(?array $u, string $label = '', string $size = 'sm'): string {
    if (!$u) return '<span class="text-xs text-gray-500">removed member</span>';
    $txt = '<span class="ac-text">' . ($label !== '' ? '<span class="ac-label">' . e($label) . '</span>' : '')
         . '<span class="ac-line"><span class="ac-name">' . e($u['username']) . '</span>' . badge_html($u['role']) . '</span></span>';
    return '<a href="' . url('member', ['u' => $u['username']]) . '" class="member-chip ac-' . $size . '" title="' . e($u['username']) . '">'
         . avatar_html($u, $size) . $txt . '</a>';
}

// ---- 8. SMALL VIEW HELPERS -----------------------------------------------------------------
/* Score 1-10 -> bar colour class (low / mid / high), the same thresholds the real gauge used. */
function score_class(int $s): string { return $s >= 7 ? 'hf-high' : ($s >= 4 ? 'hf-mid' : 'hf-low'); }
function score_bar(int $s, string $label = 'Score'): string {
    return '<div class="score-meter" role="img" aria-label="' . e($label) . ' ' . $s . ' out of 10"><div class="score-fill ' . score_class($s) . '" style="width:' . ($s * 10) . '%"></div></div>';
}
function cover(string $title, int $hue, string $class = ''): string {
    return '<span class="g-cover ' . $class . '"><span class="g-cover-fallback" style="--h:' . $hue . '"><span>' . e($title) . '</span></span></span>';
}
function empty_state(string $icon, string $title, string $text): string {
    return '<div class="empty-state"><div class="es-icon">' . nav_icon($icon) . '</div><h4>' . e($title) . '</h4><p>' . e($text) . '</p></div>';
}
/* A POST form that only shows a "mock" toast: CSRF-protected like the real forms, but nothing is saved. */
function mock_form_open(string $action, string $extra = ''): string {
    return '<form method="POST" action="' . url(current_page(), array_intersect_key($_GET, array_flip(['slug', 'id', 'u', 'tab']))) . '" ' . $extra . '>' . Csrf::field()
         . '<input type="hidden" name="mock_action" value="' . e($action) . '">';
}

/* One notification row (reused markup from the real Notification Centre). */
function notif_item(array $n): string {
    return '<li><a class="notif-item ' . ($n['unread'] ? 'is-unread' : '') . ' is-' . e($n['side']) . '" href="' . url('notifications') . '">'
         . '<span class="notif-icon" aria-hidden="true">' . nav_icon($n['icon']) . '</span><span class="notif-text">'
         . '<span class="notif-meta"><span class="notif-label">' . e($n['label']) . '</span><span class="notif-ago">' . e(ago($n['created_min'])) . '</span></span>'
         . '<span class="notif-title">' . e($n['title']) . '</span>' . ($n['body'] !== '' ? '<span class="notif-body">' . e($n['body']) . '</span>' : '') . '</span>'
         . ($n['unread'] ? '<span class="notif-dot" aria-label="Unread"></span>' : '') . '</a></li>';
}
/* Trim text to $n characters with an ellipsis (works without the mbstring extension). */
function short(string $s, int $n): string {
    $s = trim(preg_replace('/\s+/u', ' ', $s));
    if (function_exists('mb_strimwidth')) return mb_strimwidth($s, 0, $n, '…');
    return strlen($s) > $n ? rtrim(substr($s, 0, $n - 1)) . '…' : $s;
}

/* Review progress dots for the dashboard tables (reused as-is from the real Backend/pages/workspace.php). */
function progress_steps(string $status): array {
    if ($status === 'PUBLISHED' || $status === 'APPROVED') return [['Submitted', 'done'], ['Under review', 'done'], ['Published', 'done']];
    if ($status === 'PENDING')  return [['Submitted', 'done'], ['Under review', 'active'], ['Published', 'todo']];
    if ($status === 'RETURNED' || $status === 'REJECTED') return [['Submitted', 'done'], ['Reviewed', 'done'], ['Returned', 'fail']];
    return [['Draft', 'active'], ['Under review', 'todo'], ['Published', 'todo']];
}
function render_steps(array $steps): string {
    $h = '<ol class="mini-steps" aria-label="Review progress">';
    foreach ($steps as [$label, $state]) {
        $h .= '<li class="mini-step is-' . $state . '"><span class="mini-dot" aria-hidden="true"></span>' . e($label) . '<span class="sr-only"> (' . $state . ')</span></li>';
    }
    return $h . '</ol>';
}
/* Coloured status pill (the real site used the same Tailwind colours per status). */
function status_pill(string $s): string {
    $c = ['PUBLISHED' => 'bg-green-600', 'APPROVED' => 'bg-green-600', 'PENDING' => 'bg-amber-600', 'DRAFT' => 'bg-gray-600', 'RETURNED' => 'bg-red-600',
          'REJECTED' => 'bg-red-600', 'DENIED' => 'bg-red-600', 'NEW' => 'bg-blue-600', 'READ' => 'bg-slate-600', 'RESOLVED' => 'bg-green-600',
          'OPEN' => 'bg-blue-600', 'UNDER_REVIEW' => 'bg-amber-600', 'DECIDED' => 'bg-green-600', 'THRESHOLD_REACHED' => 'bg-violet-600',
          'SENT' => 'bg-green-600', 'LOGGED' => 'bg-slate-600', 'QUEUED' => 'bg-amber-600', 'FAILED' => 'bg-red-600', 'ACTIVE' => 'bg-green-600', 'SUSPENDED' => 'bg-red-600'][$s] ?? 'bg-gray-600';
    return '<span class="px-2 py-0.5 rounded text-xs font-bold text-white ' . $c . '">' . e(str_replace('_', ' ', $s)) . '</span>';
}
