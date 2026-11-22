<?php /* views/requests.php - Rank-request board with evidence panels. Markup reused from admin.php (requests view).
   Evidence is counted from the mock articles/notes/media rows. Reviewers decide Contributor requests;
   Reviewer promotions need an Admin, exactly like the real rule. */
defined('MOCK') || exit;
$isAdmin = at_least('admin');
$pending = where('rank_requests', fn ($r) => $r['status'] === 'PENDING');
$recent  = where('rank_requests', fn ($r) => $r['status'] !== 'PENDING');
$ev = function (int $uid): array {
    $o = [];
    foreach (['articles' => 'PUBLISHED', 'notes' => 'APPROVED', 'media' => 'APPROVED'] as $t => $ok) {
        $mine = where($t, fn ($x) => $x['author_id'] === $uid);
        $o[$t] = [count(array_filter($mine, fn ($x) => $x['status'] === $ok)), count($mine)];
    }
    return $o;
};
?>
<main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mb-8">
    <p class="kicker mb-1">Admin console</p>
    <h1 class="text-3xl font-bold page-title">Rank requests</h1>
    <p class="text-gray-400 mt-2"><?= !$pending ? 'All clear: no requests waiting.' : count($pending) . ' request' . (count($pending) === 1 ? '' : 's') . ' waiting for a decision.' ?><?= !$isAdmin ? ' You decide Contributor requests; Reviewer promotions are Admin-only.' : '' ?></p>
  </header>
  <?php if (!$pending): ?><div class="mb-10"><?= empty_state('gavel', 'Nothing on the board', 'When a member files a rank request it lands here with their contribution evidence attached.') ?></div><?php endif; ?>
  <?php foreach ($pending as $r): $u = user($r['user_id']); $x = $ev($r['user_id']);
        $ok = $x['articles'][0] + $x['notes'][0] + $x['media'][0]; $tot = $x['articles'][1] + $x['notes'][1] + $x['media'][1];
        $rate = $tot ? round($ok / $tot * 100) : null; $days = max(1, intdiv($u['joined_min'], 1440));
        $tasks = [['Approved notes', $x['notes'][0], 1], ['Days as a member', $days, 14], ['Credits earned', $u['credits'], $r['requested_role'] === 'reviewer' ? 500 : 100], ['Published articles', $x['articles'][0], $r['requested_role'] === 'reviewer' ? 2 : 0]];
        $done = count(array_filter($tasks, fn ($t) => $t[1] >= $t[2])); $needsAdmin = $r['requested_role'] === 'reviewer' && !$isAdmin; ?>
    <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
      <div class="flex flex-wrap justify-between items-start gap-3 mb-4">
        <div>
          <h3 class="text-lg font-bold text-white"><a href="<?= url('member', ['u' => $u['username']]) ?>" class="hover:text-violet-300"><?= e($u['username']) ?></a> <span class="text-xs text-gray-400 font-normal">(currently <?= role_title($u['role']) ?>)</span></h3>
          <p class="text-sm text-gray-400"><?= e($u['email']) ?> &middot; filed <?= ago($r['created_min']) ?></p>
        </div>
        <div class="flex items-center gap-2"><span class="text-sm text-gray-300">requests</span><?= badge_html($r['requested_role']) ?></div>
      </div>
      <div class="evidence-panel grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4" aria-label="Applicant evidence">
        <div class="stat-tile"><span class="stat-num"><?= $x['articles'][0] ?>/<?= $x['articles'][1] ?></span><span class="stat-label">Articles published</span></div>
        <div class="stat-tile"><span class="stat-num"><?= $rate === null ? '&mdash;' : $rate . '%' ?></span><span class="stat-label">Acceptance rate</span></div>
        <div class="stat-tile"><span class="stat-num"><?= $days ?></span><span class="stat-label">Days as a member</span></div>
        <div class="stat-tile"><span class="stat-num"><?= number_format($u['credits']) ?></span><span class="stat-label">Credits</span></div>
      </div>
      <p class="text-xs text-gray-500 mb-4"><?= $x['notes'][1] + $x['media'][1] ?> note and media submissions on record (<?= $x['notes'][0] ?> approved notes, <?= $x['media'][0] ?> approved media).</p>
      <div class="mb-4">
        <p class="text-sm text-gray-300 mb-2"><strong class="text-white">Rank tasks:</strong> <?= $done ?> of <?= count($tasks) ?> done</p>
        <ul class="task-list"><?php foreach ($tasks as [$label, $have, $need]): $isOk = $have >= $need; ?><li class="<?= $isOk ? 'is-done' : '' ?>"><span class="ds-tick" aria-hidden="true"><?= $isOk ? nav_icon('check') : '' ?></span><span><?= $label ?></span><b><?= number_format($have) ?> / <?= number_format($need) ?></b></li><?php endforeach; ?></ul>
      </div>
      <?php if ($needsAdmin): ?>
        <div class="cta-panel p-4 text-sm text-gray-400">Promotion to <strong class="text-white">Reviewer</strong> is reserved to Admins. Review the evidence and raise it with an Admin. <span class="block mt-1 text-xs">(Use "Preview as" Admin in the top bar to see the buttons.)</span></div>
      <?php else: ?>
        <div class="flex flex-wrap gap-3">
          <?= mock_form_open('rank_approve', 'class="inline" data-confirm="Grant ' . role_title($r['requested_role']) . ' to ' . e($u['username']) . '? Leave a note the applicant will see." data-confirm-title="Approve rank request" data-confirm-reason="decision_note" data-confirm-label="Approve and grant" data-confirm-reason-label="Decision note for the applicant *"') ?><button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-lg transition duration-300">&#10004;&#xFE0E; Approve &amp; grant <?= role_title($r['requested_role']) ?></button></form>
          <?= mock_form_open('rank_deny', 'class="inline" data-confirm="Deny this request? The applicant will see your note and may reapply later." data-confirm-title="Deny rank request" data-confirm-reason="decision_note" data-confirm-label="Deny request" data-confirm-tone="danger" data-confirm-reason-label="Decision note for the applicant *"') ?><button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-bold rounded-lg transition duration-300">&#10006;&#xFE0E; Deny</button></form>
        </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if ($recent): ?>
  <section class="mt-10">
    <h2 class="text-xl font-bold text-white mb-4 border-b border-slate-700 pb-2">Recent decisions</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm row-hover data-table m-stack">
        <thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2 pr-3">Member</th><th class="py-2 pr-3">Requested</th><th class="py-2 pr-3">Outcome</th><th class="py-2 pr-3">Decided by</th><th class="py-2 pr-3">Note</th></tr></thead>
        <tbody><?php foreach ($recent as $d): ?>
          <tr class="border-b border-slate-700/50">
            <td data-label="Member" class="py-2.5 pr-3 text-gray-200"><?= e(user($d['user_id'])['username']) ?></td>
            <td data-label="Requested" class="py-2.5 pr-3"><?= badge_html($d['requested_role']) ?></td>
            <td data-label="Outcome" class="py-2.5 pr-3"><span class="text-xs font-bold <?= $d['status'] === 'APPROVED' ? 'text-green-400' : 'text-red-400' ?>"><?= e($d['status']) ?></span></td>
            <td data-label="Decided by" class="py-2.5 pr-3 text-gray-400 text-xs"><?= e(user($d['decided_by'])['username'] ?? '-') ?> &middot; <?= when($d['created_min'], 'M j') ?></td>
            <td data-label="Note" class="py-2.5 text-gray-400 text-xs"><?= e($d['note'] ?: '-') ?></td>
          </tr>
        <?php endforeach; ?></tbody>
      </table>
    </div>
  </section>
  <?php endif; ?>
</main>
