<?php
/* app/layout.php - the shell around every page: head, sidebar, mobile ball menu, dialogs, top bar,
   breadcrumb, the page view, footer and back-to-top.
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.
   Adapted from the real Frontend/layout.php. Removed: the real logo, Google One Tap, the mail queue
   tick, the ambience player, jQuery/GSAP and every AJAX endpoint. Added: the "Preview as" switcher. */
defined('MOCK') || exit;

$asset = fn (string $p): string => defined('STATIC_EXPORT') ? $p : $p . '?v=2';
$navGroups = array_values(array_filter(NAV, fn ($g) => at_least($g[1])));
$navAll = array_merge(...array_map(fn ($g) => $g[2], $navGroups));
$navOk = fn (string $slug) => at_least(PAGES[$slug][1]);
$myNotifs = signed_in() ? array_values(array_filter(rows('notifications'), fn ($n) => $n['side'] !== 'admin' || at_least('reviewer'))) : [];
$bellCount = count(array_filter($myNotifs, fn ($n) => $n['unread']));
$navBadges = [
    'review-queue' => count(array_filter(rows('articles'), fn ($a) => $a['status'] === 'PENDING')) + count(array_filter(rows('notes'), fn ($a) => $a['status'] === 'PENDING')) + count(array_filter(rows('media'), fn ($a) => $a['status'] === 'PENDING')),
    'requests' => count(array_filter(rows('rank_requests'), fn ($r) => $r['status'] === 'PENDING')),
    'inbox' => count(array_filter(rows('support_messages'), fn ($m) => $m['status'] === 'NEW')),
];
$active = $page === 'denied' ? '' : $page;
$back = $parent && $page !== 'home' ? [$parent, PAGES[$parent][0]] : null;
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="color-scheme" content="dark">
<meta name="robots" content="noindex">
<meta name="description" content="<?= e(APP_NAME) ?> mock UI: a reviewed, rank-based community wiki. All content is placeholder text.">
<title><?= e($pageTitle) ?> - <?= e(APP_NAME) ?> (mock)</title>
<!-- Copyright (c) <?= APP_YEAR ?> <?= e(APP_OWNER) ?>. All rights reserved. Research mock; see LICENSE. -->
<style>body{background:#141416}</style>
<script>
/* Apply saved display settings before first paint, so there is no flash (reused from the real layout). */
(function () {
  try {
    var p = JSON.parse(localStorage.getItem('demo-prefs') || '{}'), c = document.documentElement.classList;
    if (p.motion === false) c.add('reduce-motion');
    if (p.text === 'large') c.add('text-large');
    if (p.text === 'xlarge') c.add('text-xlarge');
    if (p.contrast === true) c.add('high-contrast');
    if (p.spacing === true) c.add('text-spaced');
    if (p.font === true) c.add('font-readable');
    if (p.links === true) c.add('links-underline');
    if (p.focus === true) c.add('focus-strong');
    if (p.guide === true) c.add('reading-guide-on');
  } catch (e) {}
  document.documentElement.classList.add('js', 'no-video');
})();
</script>
<link rel="icon" href="<?= $asset('assets/img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Lora:wght@600;700&display=swap">
<link rel="stylesheet" href="<?= $asset('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= $asset('assets/css/tailwind.css') ?>">
<link rel="stylesheet" href="<?= $asset('assets/css/mock.css') ?>">
</head>
<body id="top" data-page="<?= e($page) ?>" data-auth="<?= signed_in() ? 1 : 0 ?>" data-role="<?= e(role_key()) ?>">
<script>try { if (localStorage.getItem('demo-sidebar') === 'hidden') document.body.classList.add('sidebar-hidden'); } catch (e) {}</script>
<a href="#main-content" class="skip-link">Skip to main content</a>
<div class="bg-overlay" style="--bg-overlay: 0.8;"></div>

<?php /* ---- Research banner + "Preview as" switcher (not part of the real site) ---- */ ?>
<div class="mock-banner" role="note">
  <span>Research mock. Every name, number and text is placeholder content; forms save nothing.</span>
  <?php if (!defined('STATIC_EXPORT')): ?>
  <form method="POST" action="<?= url($page === 'denied' ? current_page() : $page) ?>" class="preview-as" data-preview-as>
    <?= Csrf::field() ?><input type="hidden" name="mock_action" value="preview_role"><input type="hidden" name="back" value="<?= e(current_page()) ?>">
    <label for="preview-role"><?= nav_icon('eye') ?><span>Preview as</span></label>
    <select id="preview-role" name="role">
      <?php foreach (ROLES as $rk => [$rt]): ?><option value="<?= $rk ?>"<?= $rk === role_key() ? ' selected' : '' ?>><?= e($rt) ?></option><?php endforeach; ?>
    </select>
    <button type="submit" class="preview-go">Switch</button>
  </form>
  <?php endif; ?>
</div>

<?php /* ---- SIDEBAR (desktop) ---- */ ?>
<aside class="site-sidebar">
  <a href="<?= url('home') ?>" class="sidebar-brand"><span class="brand-icon"><?= brand_mark(30) ?></span><span class="brand-name" aria-label="<?= e(APP_NAME) ?>"><?= brand_wordmark() ?></span></a>
  <form action="<?= url('explore') ?>" method="GET" role="search" class="sidebar-search">
    <?php if (!pretty_urls() && !defined('STATIC_EXPORT')): ?><input type="hidden" name="page" value="explore"><?php endif; ?>
    <label for="global-search" class="sr-only">Search</label><?= nav_icon('search') ?>
    <input type="search" id="global-search" name="q" placeholder="Search…" autocomplete="off" value="<?= e($page === 'explore' ? (string) ($_GET['q'] ?? '') : '') ?>">
    <kbd class="search-kbd" aria-hidden="true">/</kbd>
  </form>
  <nav class="sidebar-nav" aria-label="Main">
    <?php foreach ($navGroups as $gi => [$gLabel, , $links]): ?>
      <p class="nav-group-label"<?= $gi === 0 ? ' style="margin-top:0"' : '' ?>><?= e($gLabel) ?></p>
      <?php foreach ($links as [$slug, $label, $icon]): if (!$navOk($slug)) continue; ?>
        <a href="<?= url($slug) ?>" class="sidebar-link <?= $active === $slug ? 'active' : '' ?>"<?= $active === $slug ? ' aria-current="page"' : '' ?>>
          <?= nav_icon($icon) ?><span><?= e($label) ?></span>
          <?php if (!empty($navBadges[$slug])): ?><span class="nav-badge" aria-label="<?= (int) $navBadges[$slug] ?> new"><?= (int) $navBadges[$slug] ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    <?php if ($me): ?>
      <a href="<?= url('profile') ?>" class="sidebar-identity" title="Open my profile"><?= avatar_html($me, 'md') ?><span class="si-text"><span class="ac-name"><?= e($me['username']) ?></span><?= badge_html($me['role']) ?></span></a>
    <?php else: ?>
      <a href="<?= url('login') ?>" class="sidebar-link <?= $active === 'login' ? 'active' : '' ?>"><?= nav_icon('enter') ?><span>Log in</span></a>
      <a href="<?= url('signup') ?>" class="sidebar-cta"><?= nav_icon('userplus') ?><span>Create an account</span></a>
    <?php endif; ?>
  </div>
</aside>
<button id="sidebar-toggle" type="button" title="Hide sidebar" aria-label="Hide sidebar" aria-expanded="true">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
</button>

<?php /* ---- MOBILE: draggable ball + chip navbar ---- */ ?>
<button type="button" id="nav-ball" aria-label="Open menu" aria-controls="mobile-menu" aria-expanded="false">
  <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
  <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12"/><path d="M18 6L6 18"/></svg>
</button>
<div id="mobile-menu" aria-hidden="true">
  <div class="mm-sheet" role="dialog" aria-label="Site menu">
    <div class="mm-head">
      <?php if ($me): ?>
        <a href="<?= url('profile') ?>" class="mm-user mobile-menu-link" title="My profile"><?= avatar_html($me, 'md') ?><span class="si-text"><span class="ac-name"><?= e($me['username']) ?></span><?= badge_html($me['role']) ?></span></a>
        <form method="post" action="<?= url('logout') ?>" class="logout-form"><?= Csrf::field() ?><button type="submit" class="mm-logout mobile-menu-link"><?= nav_icon('exit') ?><span>Log out</span></button></form>
      <?php else: ?>
        <span class="mm-user"><?= e(APP_NAME) ?> (mock)</span>
      <?php endif; ?>
    </div>
    <form action="<?= url('explore') ?>" method="GET" role="search" class="mobile-search">
      <?php if (!pretty_urls() && !defined('STATIC_EXPORT')): ?><input type="hidden" name="page" value="explore"><?php endif; ?>
      <label for="mobile-search" class="sr-only">Search</label>
      <input type="search" id="mobile-search" name="q" placeholder="Search…" class="form-input">
    </form>
    <nav class="chip-nav" aria-label="Mobile">
      <?php foreach ($navAll as [$slug, $label, $icon]): if (!$navOk($slug)) continue; ?>
        <a href="<?= url($slug) ?>" class="chip mobile-menu-link <?= $active === $slug ? 'active' : '' ?>"<?= $active === $slug ? ' aria-current="page"' : '' ?>><?= nav_icon($icon) ?><span><?= e($label) ?></span><?php if (!empty($navBadges[$slug])): ?><span class="nav-badge"><?= (int) $navBadges[$slug] ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <?php if (!$me): ?>
        <a href="<?= url('login') ?>" class="chip mobile-menu-link"><?= nav_icon('enter') ?><span>Log in</span></a>
        <a href="<?= url('signup') ?>" class="chip mobile-menu-link"><?= nav_icon('userplus') ?><span>Sign up</span></a>
      <?php endif; ?>
    </nav>
    <p class="mm-hint">Swipe the strip to see more &middot; drag the ball anywhere</p>
  </div>
</div>

<?php /* ---- DISPLAY SETTINGS dialog (same options as the real site, minus background video) ---- */ ?>
<div id="settings-dialog" class="modal-backdrop" hidden>
  <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="settings-title">
    <h2 id="settings-title" class="text-xl font-bold text-white mb-1">Display settings</h2>
    <p class="text-sm text-gray-400 mb-5">Saved in this browser only (localStorage).</p>
    <div class="settings-groups">
      <fieldset class="settings-group"><legend>Motion</legend>
        <label class="setting-row"><span><strong>Animations</strong><small>Scroll reveals and motion effects.</small></span><input type="checkbox" class="toggle" data-pref="motion" checked></label>
      </fieldset>
      <fieldset class="settings-group"><legend>Text and reading</legend>
        <div class="setting-row"><span><strong id="ts-size-l">Text size</strong><small>Makes every page larger.</small></span>
          <span class="seg" role="radiogroup" aria-labelledby="ts-size-l">
            <label><input type="radio" name="pref-text" value="normal" data-pref="text"><span>A</span><span class="sr-only">Normal</span></label>
            <label><input type="radio" name="pref-text" value="large" data-pref="text"><span class="seg-l">A</span><span class="sr-only">Large</span></label>
            <label><input type="radio" name="pref-text" value="xlarge" data-pref="text"><span class="seg-xl">A</span><span class="sr-only">Extra large</span></label>
          </span></div>
        <label class="setting-row"><span><strong>Wider spacing</strong><small>More space between lines, words and letters.</small></span><input type="checkbox" class="toggle" data-pref="spacing"></label>
        <label class="setting-row"><span><strong>Plain reading font</strong><small>Swaps the serif headings for a simple sans-serif font.</small></span><input type="checkbox" class="toggle" data-pref="font"></label>
        <label class="setting-row no-touch"><span><strong>Reading guide</strong><small>A soft bar follows your pointer to keep your place in long text.</small></span><input type="checkbox" class="toggle" data-pref="guide"></label>
      </fieldset>
      <fieldset class="settings-group"><legend>Contrast and navigation</legend>
        <label class="setting-row"><span><strong>High contrast</strong><small>Solid panels, brighter text.</small></span><input type="checkbox" class="toggle" data-pref="contrast"></label>
        <label class="setting-row"><span><strong>Underline links</strong><small>Links are always underlined, not only on hover.</small></span><input type="checkbox" class="toggle" data-pref="links"></label>
        <label class="setting-row"><span><strong>Strong focus ring</strong><small>A thick outline shows where the keyboard is.</small></span><input type="checkbox" class="toggle" data-pref="focus"></label>
      </fieldset>
    </div>
    <?php if ($me): ?>
    <p class="settings-mail-link"><a href="<?= url('profile') ?>#email"><?= nav_icon('mail') ?><span><strong>Email notifications</strong><small>Choose which emails reach your inbox (saved to your account in the real site).</small></span></a></p>
    <?php endif; ?>
    <p class="text-xs text-gray-400 mt-4">Press <kbd>?</kbd> on any page for keyboard shortcuts. More help: <a href="<?= url('help') ?>" class="text-indigo-300 hover:underline">Help centre</a>.</p>
    <div class="flex justify-between items-center gap-3 mt-6">
      <button type="button" class="btn-outline" data-reset-prefs>Reset</button>
      <button type="button" class="form-button" data-close-modal>Done</button>
    </div>
  </div>
</div>
<div id="shortcuts-dialog" class="modal-backdrop" hidden>
  <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="sc-title">
    <h2 id="sc-title" class="text-xl font-bold text-white mb-1">Keyboard shortcuts</h2>
    <p class="text-sm text-gray-400 mb-4">Shortcuts are ignored while you type in a field.</p>
    <dl class="shortcut-list">
      <div><dt><kbd>/</kbd></dt><dd>Search</dd></div>
      <div><dt><kbd>?</kbd></dt><dd>Show this list</dd></div>
      <div><dt><kbd>g</kbd> then <kbd>h</kbd></dt><dd>Home</dd></div>
      <div><dt><kbd>g</kbd> then <kbd>g</kbd></dt><dd>Gallery</dd></div>
      <div><dt><kbd>g</kbd> then <kbd>e</kbd></dt><dd>Explore</dd></div>
      <div><dt><kbd>g</kbd> then <kbd>m</kbd></dt><dd>Media</dd></div>
      <div><dt><kbd>g</kbd> then <kbd>d</kbd></dt><dd>Dashboard (when signed in)</dd></div>
      <div><dt><kbd>r</kbd></dt><dd>A random article</dd></div>
      <div><dt><kbd>t</kbd></dt><dd>Back to the top</dd></div>
      <div><dt><kbd>&larr;</kbd> <kbd>&rarr;</kbd></dt><dd>Previous / next item in the media carousel</dd></div>
      <div><dt><kbd>Esc</kbd></dt><dd>Close a dialog or menu</dd></div>
    </dl>
    <div class="flex justify-end mt-6"><button type="button" class="form-button" data-close-modal>Close</button></div>
  </div>
</div>
<div class="reading-guide" aria-hidden="true" hidden></div>
<div id="toast-region" class="toast-region" aria-live="polite" aria-atomic="false"></div>
<script type="application/json" id="flash-data"><?= json_encode(defined('STATIC_EXPORT') ? [] : Flash::pull(), JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<div class="site-main" id="main-content" tabindex="-1">
  <?php /* ---- TOP UTILITY BAR ---- */ ?>
  <div class="topbar" role="toolbar" aria-label="Quick tools">
    <a href="<?= url('home') ?>" class="topbar-brand" aria-label="<?= e(APP_NAME) ?> home"><?= brand_mark(26) ?><span class="brand-name brand-name-sm" aria-label="<?= e(APP_NAME) ?>"><?= brand_wordmark() ?></span></a>
    <div class="topbar-tools">
      <?php if ($me): ?>
        <div class="notif" data-notif>
          <a href="<?= url('notifications') ?>" class="topbar-btn <?= $active === 'notifications' ? 'is-active' : '' ?>" data-notif-toggle title="Notifications" aria-label="Notifications<?= $bellCount ? ' (' . $bellCount . ' new)' : '' ?>" aria-haspopup="true" aria-expanded="false" aria-controls="notif-panel">
            <?= nav_icon('bell') ?><?php if ($bellCount): ?><span class="nav-badge" data-notif-badge><?= $bellCount ?></span><?php endif; ?>
          </a>
          <div class="notif-panel" id="notif-panel" role="dialog" aria-label="Notifications" hidden>
            <div class="notif-head"><strong>Notifications</strong><button type="button" class="notif-markall" data-notif-readall>Mark all read</button></div>
            <ul class="notif-list" data-notif-list>
              <?php foreach (array_slice($myNotifs, 0, 5) as $n) echo notif_item($n); ?>
            </ul>
            <div class="notif-foot"><a href="<?= url('notifications') ?>">Open Notification centre&nbsp;&rarr;</a></div>
          </div>
        </div>
      <?php endif; ?>
      <?php if (at_least('admin')): ?>
        <a href="<?= url('inbox') ?>" class="topbar-btn <?= $active === 'inbox' ? 'is-active' : '' ?>" title="Support inbox" aria-label="Support inbox<?= $navBadges['inbox'] ? ' (' . $navBadges['inbox'] . ' new)' : '' ?>"><?= nav_icon('inbox') ?><?php if ($navBadges['inbox']): ?><span class="nav-badge"><?= $navBadges['inbox'] ?></span><?php endif; ?></a>
      <?php endif; ?>
      <a href="<?= url('contact') ?>" class="topbar-btn <?= $active === 'contact' ? 'is-active' : '' ?>" title="Contact" aria-label="Contact"><?= nav_icon('mail') ?></a>
      <button type="button" class="topbar-btn" data-open-settings title="Display settings" aria-label="Display settings"><?= nav_icon('sliders') ?></button>
      <?php if ($me): ?>
        <div class="topbar-account" data-account-menu>
          <button type="button" class="topbar-avatar" aria-haspopup="true" aria-expanded="false" aria-controls="account-menu" title="My account"><?= avatar_html($me, 'sm') ?><?= nav_icon('chevron') ?></button>
          <div class="account-menu" id="account-menu" role="menu" hidden>
            <div class="am-head"><span class="ac-name"><?= e($me['username']) ?></span><?= badge_html($me['role']) ?></div>
            <?php foreach (ACCOUNT_MENU as [$slug, $label, $icon]): ?>
              <a href="<?= url($slug) ?>" role="menuitem" class="<?= $active === $slug ? 'is-active' : '' ?>"><?= nav_icon($icon) ?><span><?= e($label) ?></span></a>
            <?php endforeach; ?>
            <form method="post" action="<?= url('logout') ?>" class="logout-form"><?= Csrf::field() ?><button type="submit" role="menuitem" class="am-logout"><?= nav_icon('exit') ?><span>Log out</span></button></form>
          </div>
        </div>
      <?php elseif ($page !== 'login'): ?>
        <a href="<?= url('login') ?>" class="topbar-login">Log in</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($back): ?>
  <nav class="page-back" aria-label="Breadcrumb">
    <a href="<?= url($back[0]) ?>" class="pb-btn" data-back title="Go back to the previous page">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg><span>Back</span>
    </a>
    <ol class="pb-trail"><li><a href="<?= url($back[0]) ?>"><?= e($back[1]) ?></a></li><li aria-current="page"><?= e($pageTitle) ?></li></ol>
  </nav>
  <?php endif; ?>

  <?php require dirname(__DIR__) . '/views/' . $page . '.php'; ?>

  <footer class="mock-footer">
    <p>Copyright &copy; <?= APP_YEAR ?> <?= e(APP_OWNER) ?>. All rights reserved. Research mock for UI/UX study and portfolio viewing only.</p>
    <p class="mock-source"><?= nav_icon('database') ?><span>Data: <?= e(defined('STATIC_EXPORT') ? 'PHP arrays (static export)' : data_source_label()) ?></span> &middot; <a href="<?= url('pages') ?>">All pages</a></p>
  </footer>
</div>

<a href="#top" class="back-to-top" title="Back to top" aria-label="Back to top">
  <svg class="btt-ring" viewBox="0 0 48 48" aria-hidden="true"><circle class="btt-track" cx="24" cy="24" r="21"/><circle class="btt-fill" cx="24" cy="24" r="21" pathLength="100"/></svg>
  <svg class="btt-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V6"/><path d="M6 11.5 12 5.5l6 6"/></svg>
</a>
<script>window.MOCK = {random: <?= json_encode(html_entity_decode(url('random'))) ?>, home: <?= json_encode(html_entity_decode(url('home'))) ?>, gallery: <?= json_encode(html_entity_decode(url('gallery'))) ?>, explore: <?= json_encode(html_entity_decode(url('explore'))) ?>, media: <?= json_encode(html_entity_decode(url('media'))) ?>, dashboard: <?= json_encode(html_entity_decode(url('dashboard'))) ?>};</script>
<script src="<?= $asset('assets/js/validator.js') ?>"></script>
<script src="<?= $asset('assets/js/mock-ui.js') ?>"></script>
</body>
</html>
