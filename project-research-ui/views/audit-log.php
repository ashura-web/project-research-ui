<?php /* views/audit-log.php - Append-only audit timeline with GET filters. Markup reused from admin.php (audit-log view). */
defined('MOCK') || exit;
$actionFilters = ['review_approve' => 'Review approved', 'user_suspend' => 'Member suspended', 'rank_approve' => 'Rank approved', 'rank_deny' => 'Rank denied', 'admin_appoint' => 'Admin appointed', 'store_purchase' => 'Store purchase'];
$tone = ['user_suspend' => 'text-red-300', 'rank_deny' => 'text-red-300', 'rank_approve' => 'text-green-300', 'review_approve' => 'text-green-300', 'admin_appoint' => 'text-amber-300'];
$fa = array_key_exists($_GET['action'] ?? '', $actionFilters) ? $_GET['action'] : '';
$fu = trim((string) ($_GET['u'] ?? ''));
$all = rows('audit_log');
$entries = array_values(array_filter($all, function ($a) use ($fa, $fu) {
    if ($fa && $a['action'] !== $fa) return false;
    if ($fu === '') return true;
    foreach ([$a['actor_id'], $a['target_id']] as $id) if (stripos(user($id)['username'] ?? '', $fu) !== false) return true;
    return false;
}));
?>
<main class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Admin console</p>
  <h1 class="text-3xl font-bold mb-2 page-title">Audit log</h1>
  <p class="text-gray-400 mb-8">Append-only record of every privileged action: who acted, on whom, what was decided, from where. <?= count($all) ?> entries on file. IP addresses are masked.</p>
  <form method="GET" action="<?= url('audit-log') ?>" class="archive-filters mb-8 flex flex-wrap items-end gap-3" role="search">
    <?php if (!pretty_urls() && !defined('STATIC_EXPORT')): ?><input type="hidden" name="page" value="audit-log"><?php endif; ?>
    <div><label for="f-action" class="filter-label">Action</label><select id="f-action" name="action" class="form-input !py-1.5 !w-56 text-sm"><option value="">All actions</option><?php foreach ($actionFilters as $k => $l): ?><option value="<?= $k ?>" <?= $fa === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    <div><label for="f-user" class="filter-label">Username</label><input type="search" id="f-user" name="u" class="form-input !py-1.5 !w-44 text-sm" placeholder="e.g. mira_s" value="<?= e($fu) ?>"></div>
    <button type="submit" class="form-button !py-1.5" style="width:auto">Filter</button>
    <?php if ($fa || $fu): ?><a href="<?= url('audit-log') ?>" class="btn-outline !py-1.5">Clear</a><?php endif; ?>
  </form>
  <?php if (!$entries): ?>
    <?= empty_state('book', 'No audit entries in this view', 'Promotions, rank decisions and moderation verdicts are recorded here the moment they happen.') ?>
  <?php else: ?>
    <ol class="audit-timeline">
      <?php foreach ($entries as $a): $t = user($a['target_id']); ?>
        <li class="audit-entry">
          <div class="audit-when"><span class="text-gray-200 font-semibold"><?= when($a['created_min'], 'M j, g:ia') ?></span> <span class="text-gray-500 text-xs"><?= e($a['ip']) ?></span></div>
          <div class="audit-body">
            <p class="text-sm"><strong class="text-white"><?= e(user($a['actor_id'])['username']) ?></strong> <span class="font-mono text-xs <?= $tone[$a['action']] ?? 'text-indigo-300' ?>"><?= e($a['action']) ?></span><?php if ($t): ?> &rarr; <strong class="text-white"><?= e($t['username']) ?></strong><?php endif; ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= e($a['details']) ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</main>
