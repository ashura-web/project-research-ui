<?php
/* index.php - the front controller: every page of the mock goes through this one file.
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.
   Adapted from the real project's index.php (page map, built-in server branch, private-folder guard). */
declare(strict_types=1);
define('MOCK', true);

// ---- 0. Built-in PHP server (php -S localhost:8000 index.php): serve real files itself, never app/ etc.
if (PHP_SAPI === 'cli-server') {
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $private = (bool) preg_match('#^/(app|views)(/|$)#', $p);
    if (!$private && is_file(__DIR__ . $p) && !str_ends_with($p, '.php')) return false;
}

require __DIR__ . '/app/config.php';
require __DIR__ . '/app/core.php';
require __DIR__ . '/app/data.php';
require __DIR__ . '/app/pages.php';
require __DIR__ . '/app/governance-rules.php';

if (!defined('STATIC_EXPORT')) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_name('mock_session');
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: SAMEORIGIN');
}

// ---- 1. Which page? ?page=about, or /about (rewrite / built-in server), or /about.php
function current_page(): string {
    static $page = null;
    if ($page !== null) return $page;
    if (defined('STATIC_PAGE')) return $page = STATIC_PAGE;
    $p = (string) ($_GET['page'] ?? '');
    if ($p === '') {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
        if ($base !== '' && strncasecmp($path, $base, strlen($base)) === 0) $path = substr($path, strlen($base));
        $p = preg_replace('/\.(php|html)$/', '', trim($path, '/'));
        if ($p === '' || $p === 'index') $p = 'home';
    }
    $GLOBALS['asked_page'] = $p;
    return $page = (isset(PAGES[$p]) || in_array($p, ['logout', 'robots.txt', 'sitemap.xml'], true)) ? $p : '404';
}
$page = current_page();

// ---- 2. Search-engine files (the real site generated these too). The mock asks not to be indexed.
if ($page === 'robots.txt') { header('Content-Type: text/plain'); echo "User-agent: *\nDisallow: /\n# Research mock: nothing here should be indexed.\n"; exit; }
if ($page === 'sitemap.xml') {
    header('Content-Type: application/xml');
    $host = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\') . '/';
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach (PAGES as $slug => [$t, $min]) if ($min === 'guest' && !in_array($slug, ['404', 'denied'], true)) echo '  <url><loc>' . e($host . html_entity_decode(url($slug))) . "</loc></url>\n";
    echo "</urlset>\n"; exit;
}

// ---- 3. POST: every form is CSRF-checked (reused Csrf class), then handled as a mock
$errors = []; $old = $_POST;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !defined('STATIC_EXPORT')) {
    if (!Csrf::check()) { Flash::set('error', 'Your session expired. Please try again.'); redirect($page); }
    $act = (string) ($_POST['mock_action'] ?? '');
    if ($act === 'preview_role') {                                   // the "Preview as" switcher
        $r = (string) ($_POST['role'] ?? 'guest');
        $_SESSION['preview_role'] = isset(ROLES[$r]) ? $r : 'guest';
        Flash::set('info', 'Now previewing as ' . role_title($_SESSION['preview_role']) . '.');
        $back = (string) ($_POST['back'] ?? 'home');
        redirect(isset(PAGES[$back]) ? $back : 'home');
    }
    if ($page === 'logout') {
        $_SESSION['preview_role'] = 'guest';
        session_regenerate_id(true);
        Flash::set('success', 'Signed out (mock).');
        redirect('home');
    }
    // Server-side validation demo: the same Validator rules the real site used.
    $v = new Validator($_POST);
    if ($act === 'login') {
        $v->required('username', 'Username')->required('password', 'Password');
    } elseif ($act === 'signup') {
        $v->required('username', 'Username')->pattern('username', 'username', 'Use 3-50 letters, numbers, dashes or underscores.')
          ->required('email', 'Email')->email('email')
          ->required('password', 'Password')->pattern('password', 'password', 'At least 8 characters, with a letter and a number.')
          ->matches('password_confirm', 'password', 'The passwords do not match.');
    } elseif ($act === 'two-step') {
        $v->required('code', 'Code')->pattern('code', 'code6', 'Enter the 6 digits.');
    } elseif ($act === 'contact') {
        $v->in('inquiry_type', ['ACCOUNT', 'BUG', 'CONTENT', 'OTHER'], 'Choose what your message is about.')
          ->required('message', 'Message')->length('message', 10, 5000, 'Message');
        if (!signed_in()) $v->required('full_name', 'Name')->pattern('full_name', 'callsign', 'Use 2-50 letters, numbers, spaces or . \' _ -')->required('email', 'Email')->email('email');
    } elseif ($act === 'write-note') {
        $v->required('title', 'Title')->length('title', 5, 150, 'Title')->in('type', ['NOTE', 'IDEA', 'FIELD_NOTE'], 'Choose a type.')
          ->required('body', 'The write-up')->length('body', 50, 10000, 'The write-up');
    } elseif ($act === 'media-submit') {
        $v->in('media_type', ['IMAGE', 'VIDEO', 'AUDIO'], 'Choose a type.')->required('title', 'Title')->length('title', 3, 200, 'Title');
        if (trim((string) ($_POST['external_url'] ?? '')) !== '') $v->url('external_url');
    } elseif ($act === 'contribute' && ($_POST['intent'] ?? '') !== 'draft') {
        $v->required('title', 'Title')->length('title', 3, 150, 'Title')->required('summary', 'Summary')->length('summary', 20, 500, 'Summary')
          ->required('body', 'Article')->length('body', 30, 60000, 'Article');
        if (trim((string) ($_POST['code'] ?? '')) !== '') $v->pattern('code', 'designation', 'Use letters, numbers, "/", "-" and ".".');
    } elseif ($act === 'complaint') {
        $v->required('subject', 'Accused Admin')->in('category', array_keys(Governance::CATEGORIES), 'Choose a category.')
          ->required('subject_line', 'Subject')->length('subject_line', 10, 180, 'Subject')
          ->required('description', 'Description')->length('description', 50, 20000, 'Description');
        if (($_POST['evidence_kind'] ?? '') === 'URL') $v->required('evidence_url', 'Evidence link')->url('evidence_url');
    } elseif ($act === 'petition_start') {
        $v->in('type', array_keys(Governance::PETITION_TYPES), 'Choose a petition type.')->required('title', 'Title')->length('title', 10, 180, 'Title')
          ->required('body', 'Reason')->length('body', 30, 5000, 'Reason');
    } elseif ($act === 'bulletin') {
        $v->required('title', 'Title')->length('title', 5, 150, 'Title')->required('body', 'Message')->length('body', 20, 5000, 'Message')
          ->in('priority', ['INFO', 'IMPORTANT', 'URGENT'], 'Choose a priority.')->in('min_role', ['member', 'contributor', 'reviewer', 'admin'], 'Choose an audience.');
    } elseif ($act === 'reply' || $act === 'case_response') {
        $v->required($act === 'reply' ? 'reply' : 'response', 'Reply')->length($act === 'reply' ? 'reply' : 'response', 5, 4000, 'Reply');
    } elseif ($act === 'vote') {
        $v->in('choice', array_keys(Governance::POLL_CHOICES), 'Choose one answer.');
    } elseif ($act === 'setup') {
        $v->required('username', 'Username')->pattern('username', 'username', 'Use 3-50 letters, numbers, dashes or underscores.')->required('email', 'Email')->email('email')
          ->required('password', 'Password')->pattern('password', 'password', 'At least 8 characters, with a letter and a number.')
          ->matches('password_confirm', 'password', 'The passwords do not match.')->required('note', 'Appointment note')->length('note', 10, 500, 'Appointment note');
    } elseif (in_array($act, ['appoint', 'remove', 'transfer'], true)) {
        $v->required('target', 'Member')->required('reason', 'Reason')->length('reason', 10, 500, 'Reason')->required('confirm', 'Confirmation');
    } elseif ($act === 'forgot-password') {
        $v->required('identifier', 'Username or email');
    } elseif ($act === 'reset-password') {
        $v->required('new_password', 'New password')->pattern('new_password', 'password', 'At least 8 characters, with a letter and a number.')
          ->matches('confirm_password', 'new_password', 'The passwords do not match.');
    } elseif ($act === 'choose-username') {
        $v->required('username', 'Username')->pattern('username', 'username', 'Use 3-50 letters, numbers, dashes or underscores.')->required('agree', 'Agreement');
    }
    if ($v->fails()) {
        $errors = $v->errors();              // fall through and re-render the page with the errors
    } else {
        unset($_SESSION['drafts']);
        if ($act === 'login')    { redirect('two-step'); }
        if ($act === 'two-step') { $_SESSION['preview_role'] = 'member'; session_regenerate_id(true); Flash::set('success', 'Signed in as the demo Member (mock, no real account).'); redirect('dashboard'); }
        if ($act === 'signup')   { $_SESSION['preview_role'] = 'member'; session_regenerate_id(true); Flash::set('success', 'Account created (mock). You are now previewing as a Member.'); redirect('dashboard'); }
        if ($act === 'forgot-password') { redirect('forgot-password', ['sent' => 1]); }
        if ($act === 'reset-password')  { Flash::set('success', 'Mock only: password "changed". Log in with any password.'); redirect('login'); }
        if ($act === 'choose-username') { $_SESSION['preview_role'] = 'member'; session_regenerate_id(true); Flash::set('success', 'Account created (mock).'); redirect('dashboard'); }
        if ($act === 'contact')  { Flash::set('success', 'Mock only: the message was checked but not stored. Reference IC-MSG-0004.'); redirect('contact', ['sent' => 'IC-MSG-0004']); }
        // Remember a signature / vote for this browser session only, so the page can show "You signed".
        if ($act === 'sign') { $_SESSION['mock']['signed'][(int) ($_GET['id'] ?? 0)] = true; Flash::set('success', 'Signed (mock, kept in this browser session only).'); redirect($page, ['id' => (int) ($_GET['id'] ?? 0)]); }
        if ($act === 'vote') { $_SESSION['mock']['vote'][(int) ($_GET['id'] ?? 0)] = (string) $_POST['choice']; Flash::set('success', 'Vote recorded (mock, kept in this browser session only).'); redirect($page, ['id' => (int) ($_GET['id'] ?? 0)]); }
        Flash::set('success', 'Mock only: the form passed its checks, but nothing was saved.');
        redirect($page, array_intersect_key($_GET, array_flip(['slug', 'id', 'u', 'tab'])));
    }
}
if ($page === 'logout') redirect('home');

// ---- 4. Random article (the real random.php picked a published dossier)
if ($page === 'random') {
    $pub = published('articles');
    redirect('article', ['slug' => $pub[array_rand($pub)]['slug']]);
}

// ---- 5. Access control: pages above your preview role show the 403 panel (the real site did the same)
[$pageTitle, $minRole, $parent] = PAGES[$page];
$deniedFor = null;
if (!at_least($minRole)) { $deniedFor = $minRole; $page = 'denied'; [$pageTitle, , $parent] = PAGES['denied']; }
if (!defined('STATIC_EXPORT')) {
    if ($page === '404') http_response_code(404);
    if ($page === 'denied') http_response_code(403);
}
$me = me();

// ---- 6. Draw it
require __DIR__ . '/app/layout.php';
