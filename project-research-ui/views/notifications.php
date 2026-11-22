<?php /* views/notifications.php - Notification centre: "For you" + "Bulletins" streams. Markup reused from workspace.php.
   ?type=all|you|board and ?filter=unread are plain links, so every tab works without JavaScript. */
defined('MOCK') || exit;
$type = in_array($_GET['type'] ?? '', ['you', 'board'], true) ? $_GET['type'] : 'all';
$filter = ($_GET['filter'] ?? '') === 'unread' ? 'unread' : 'all';
$items = array_values(array_filter(rows('notifications'), fn ($n) => ($n['side'] !== 'admin' || at_least('reviewer'))
    && ($type === 'all' || ($type === 'board' ? $n['side'] === 'board' : $n['side'] !== 'board')) && ($filter === 'all' || $n['unread'])));
$unread = count(array_filter(rows('notifications'), fn ($n) => $n['unread'] && ($n['side'] !== 'admin' || at_least('reviewer'))));
$bulletins = $type === 'board' ? array_values(array_filter(rows('bulletins'), fn ($b) => at_least($b['min_role']) && ($filter === 'all' || $b['unread']))) : [];
$canPost = at_least('reviewer');
$tab = fn (string $label, array $q, bool $on, string $extra = '') => '<a href="' . url('notifications', $q) . '" class="' . ($on ? 'is-active' : '') . '" aria-current="' . ($on ? 'true' : 'false') . '">' . $label . $extra . '</a>';
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mb-6">
    <p class="kicker mb-1">Your activity</p>
    <h1 class="text-3xl font-bold page-title">Notification centre</h1>
    <p class="text-gray-400 mt-2">One inbox, two streams: <strong class="text-gray-200">For you</strong> holds replies and personal notices, <strong class="text-gray-200">Bulletins</strong> holds announcements for everyone at your rank.</p>
  </header>
  <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="segmented-btns" role="group" aria-label="Filter notifications">
      <?= $tab('All', [], $type === 'all' && $filter === 'all') ?><?= $tab('For you', ['type' => 'you'], $type === 'you' && $filter === 'all') ?><?= $tab('Bulletins', ['type' => 'board'], $type === 'board' && $filter === 'all') ?><?= $tab('Unread', ['filter' => 'unread'], $filter === 'unread', ' <span class="count-pill">' . $unread . '</span>') ?>
    </div>
    <?= mock_form_open('read_all') ?><button type="submit" class="btn-outline !py-1.5 text-sm" <?= $unread ? '' : 'disabled' ?>>Mark all read</button></form>
  </div>
  <?php if ($type === 'board'): ?>
    <p class="text-sm text-gray-400 mb-4 flex flex-wrap items-center justify-between gap-3"><span>Official announcements and personal notices. Open one to read it in full.</span><?php if ($canPost): ?><a href="<?= url('bulletins') ?>#compose-title" class="form-button" style="width:auto">Post a bulletin</a><?php endif; ?></p>
    <?php if ($bulletins) require __DIR__ . '/_bulletin-list.php'; ?>
  <?php endif; ?>
  <?php if ($type === 'board' && $bulletins): ?>
  <?php elseif (!$items): ?>
    <?= empty_state($type === 'board' ? 'megaphone' : 'bell', $filter === 'unread' ? "You're all caught up" : 'Nothing here yet', 'Replies to your messages and talk on your articles will show up here.') ?>
  <?php else: ?>
    <ul class="notif-list notif-list-page"><?php foreach ($items as $n) echo notif_item($n); ?></ul>
  <?php endif; ?>
</main>
