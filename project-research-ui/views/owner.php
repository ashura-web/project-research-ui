<?php /* views/owner.php - Owner console: appointment, Admin team, governance queue. Markup reused from governance.php (custodian view). */
defined('MOCK') || exit;
$admins = where('users', fn ($u) => $u['role'] === 'admin');
$cases = rows('cases');
$threshold = where('petitions', fn ($p) => $p['status'] === 'THRESHOLD_REACHED');
$govAudit = where('audit_log', fn ($a) => in_array($a['action'], ['admin_appoint', 'rank_deny', 'user_suspend'], true));
?>
<main class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">System authority</p>
  <h1 class="text-3xl font-bold mb-2 page-title">Owner console</h1>
  <p class="text-gray-400 text-sm mb-8">The Owner runs the site: appoints the Admin team, reviews governance cases about Admins, and holds the succession keys. The Owner keeps every Admin power and can hand the appointment to a successor at any time.</p>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <section class="bg-slate-800/80 border border-violet-500/40 rounded-xl p-6">
      <h2 class="text-lg font-bold text-white mb-3">Appointment</h2>
      <p class="text-sm text-gray-300">Account <strong class="text-white"><?= e($me['username']) ?></strong></p>
      <p class="text-xs text-gray-500 mt-1">Owner since <?= when($me['joined_min'], 'M j, Y') ?> &middot; founding Owner</p>
      <p class="text-xs text-gray-400 mt-2">&ldquo;Mock appointment note: founding account for the research build.&rdquo;</p>
      <a href="<?= url('owner-appointments') ?>" class="console-action mt-5"><span class="ca-icon" aria-hidden="true"><?= nav_icon('users') ?></span><span class="ca-text"><strong>Admin appointments</strong><small>Appoint, remove, hand over succession</small></span><span class="ca-arrow" aria-hidden="true">&rarr;</span></a>
    </section>
    <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6">
      <h2 class="text-lg font-bold text-white mb-3">Admin team (<?= count($admins) ?>)</h2>
      <ul class="text-sm space-y-1"><?php foreach ($admins as $c): ?><li class="flex justify-between gap-2"><span class="text-gray-300"><?= e($c['username']) ?></span><span class="text-xs <?= $c['status'] === 'ACTIVE' ? 'text-green-400' : 'text-red-400' ?>"><?= e($c['status']) ?></span></li><?php endforeach; ?></ul>
    </section>
    <section class="bg-slate-800/80 border border-amber-500/40 rounded-xl p-6">
      <h2 class="text-lg font-bold text-white mb-3">Limits of this console</h2>
      <ul class="text-xs text-gray-400 space-y-2 list-disc pl-4">
        <li>You cannot decide, close or open a case about <strong>yourself</strong>; those stay sealed and are settled outside the website.</li>
        <li>A petition or poll result <strong>never demotes anyone automatically</strong>; only your recorded, reasoned decision does.</li>
        <li>The Owner appointment changes only through the deliberate succession flow, never a console button.</li>
      </ul>
    </section>
  </div>
  <section class="mb-10">
    <h2 class="text-xl font-bold text-white mb-4">Governance cases</h2>
    <div class="overflow-x-auto"><table class="w-full text-sm data-table m-stack">
      <thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2 pr-3">Case</th><th class="py-2 pr-3">About</th><th class="py-2 pr-3">Filed by</th><th class="py-2 pr-3">Status</th><th class="py-2 pr-3">Filed</th><th class="py-2"><span class="sr-only">Open</span></th></tr></thead>
      <tbody><?php foreach ($cases as $c): ?>
        <tr class="border-b border-slate-700/50">
          <td data-label="Case" class="py-2.5 pr-3"><span class="ref-code"><?= e($c['code']) ?></span></td>
          <td data-label="About" class="py-2.5 pr-3 text-gray-300"><?= e(user($c['subject_id'])['username']) ?></td>
          <td data-label="Filed by" class="py-2.5 pr-3 text-gray-400"><?= e(user($c['filed_by'])['username']) ?></td>
          <td data-label="Status" class="py-2.5 pr-3"><span class="gov-pill gov-<?= e($c['status']) ?>"><?= e(Governance::CASE_STATUS[$c['status']]) ?></span></td>
          <td data-label="Filed" class="py-2.5 pr-3 text-xs text-gray-500"><?= ago($c['created_min']) ?></td>
          <td data-label="Open" class="py-2.5"><a class="text-indigo-400 hover:underline text-xs" href="<?= url('governance-case', ['id' => $c['id']]) ?>">Open &rarr;</a></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table></div>
  </section>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <section class="mb-6"><h2 class="text-xl font-bold text-white mb-4">Petitions at threshold</h2>
      <?php if (!$threshold): ?><div class="empty-state p-5"><p class="text-sm text-gray-400">None: no petition has reached its threshold.</p></div><?php endif; ?>
      <?php foreach ($threshold as $p): ?><div class="bg-slate-800/80 border border-slate-700 rounded-xl p-4 mb-3"><p class="font-semibold text-white text-sm"><?= e($p['title']) ?></p><p class="text-xs text-gray-500 mt-1"><?= e(user($p['target_id'])['username'] ?? 'policy review') ?> &middot; <a class="text-indigo-400 hover:underline" href="<?= url('petition', ['id' => $p['id']]) ?>">open &rarr;</a></p></div><?php endforeach; ?>
    </section>
    <section class="mb-6"><h2 class="text-xl font-bold text-white mb-4">Confidence polls</h2>
      <?php foreach (rows('polls') as $poll): ?><div class="bg-slate-800/80 border border-slate-700 rounded-xl p-4 mb-3"><p class="font-semibold text-white text-sm"><?= e($poll['question']) ?></p><p class="text-xs text-gray-500 mt-1"><?= $poll['confidence'] + $poll['investigate'] + $poll['no_opinion'] ?> votes &middot; open &middot; <a class="text-indigo-400 hover:underline" href="<?= url('poll', ['id' => $poll['id']]) ?>">results &rarr;</a></p></div><?php endforeach; ?>
    </section>
  </div>
  <section>
    <h2 class="text-xl font-bold text-white mb-4">Recent governance audit trail</h2>
    <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-5"><ul class="text-xs text-gray-400 space-y-1.5">
      <?php foreach ($govAudit as $a): ?><li><span class="text-gray-500"><?= when($a['created_min'], 'M j, H:i') ?></span> &middot; <strong class="text-gray-300"><?= e(user($a['actor_id'])['username']) ?></strong> &middot; <span class="ref-code"><?= e($a['action']) ?></span> &middot; <?= e(short($a['details'], 110)) ?></li><?php endforeach; ?>
    </ul></div>
  </section>
</main>
