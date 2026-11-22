<?php /* views/governance.php - Community governance home: complaints, petitions, confidence polls, published decisions.
   Markup reused from governance.php (governance view); rules from app/governance-rules.php. */
defined('MOCK') || exit;
$cases = rows('cases'); $petitions = rows('petitions'); $polls = rows('polls');
$myCases = array_values(array_filter($cases, fn ($c) => $c['filed_by'] === $me['id'] || $c['subject_id'] === $me['id']));
$open = array_values(array_filter($petitions, fn ($p) => in_array($p['status'], ['OPEN', 'THRESHOLD_REACHED'], true)));
$decided = array_values(array_filter($cases, fn ($c) => $c['status'] === 'DECIDED'));
$days = intdiv($me['joined_min'] ?? 0, 1440); $canVote = $days >= Governance::waitDays($me['role']);
?>
<main class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay gov-home">
  <header class="gov-hero">
    <p class="kicker mb-1">Community governance</p>
    <h1 class="text-3xl font-bold mb-2 page-title">Accountability protocol</h1>
    <p class="text-gray-400 text-sm max-w-3xl">The checks-and-balances layer of the site. Any member can hold the Admin team to account in three ways: a <strong class="text-white">formal complaint</strong> backed by evidence, an <strong class="text-white">open petition</strong> that other members sign, and a <strong class="text-white">confidence poll</strong> during an investigation. Community support starts a review but never removes anyone by itself: only the <strong class="text-white">Owner</strong> decides, with a written reason, and never in a case about themselves.</p>
  </header>
  <dl class="gov-stats" aria-label="Governance at a glance">
    <div><dt>Open cases</dt><dd><?= count($cases) - count($decided) ?></dd></div>
    <div><dt>Open petitions</dt><dd><?= count($open) ?></dd></div>
    <div><dt>Open polls</dt><dd><?= count($polls) ?></dd></div>
    <div><dt>Decisions published</dt><dd><?= count($decided) ?></dd></div>
  </dl>
  <section class="gov-actions" aria-label="What you can do">
    <a href="<?= url('governance-submit') ?>" class="gov-action is-complaint"><span class="gov-action-icon"><?= nav_icon('gavel') ?></span><strong>File a formal complaint</strong><small>Private between you, the accused and the reviewer. Describe what happened, cite the rule, attach evidence.</small><span class="gov-action-go">Write a complaint &rarr;</span></a>
    <a href="<?= url('petitions') ?>#start" class="gov-action is-petition"><span class="gov-action-icon"><?= nav_icon('users') ?></span><strong>Start an open petition</strong><small>Public. Ask for an investigation, show no confidence, or challenge a rule. At <?= Governance::petitionThreshold() ?> signatures it goes to review.</small><span class="gov-action-go">Start or sign a petition &rarr;</span></a>
    <a href="#polls" class="gov-action is-poll"><span class="gov-action-icon"><?= nav_icon('scales') ?></span><strong>Vote in a confidence poll</strong><small>Opened by the reviewer during an investigation, for <?= Governance::POLL_DAYS ?> days. One vote each; the accused cannot vote.</small><span class="gov-action-go"><?= count($polls) ?> open now &darr;</span></a>
  </section>
  <?php if (!$canVote): ?><p class="form-status info mb-8">New accounts wait <?= Governance::waitDays($me['role']) ?> days before signing or voting.</p><?php endif; ?>
  <ol class="gov-flow" aria-label="How a case moves">
    <li><b>1</b><span><strong>Filed</strong><small>Complaint or petition reaches its threshold</small></span></li>
    <li><b>2</b><span><strong>Triage</strong><small>The Owner takes the case (the accused never reviews it)</small></span></li>
    <li><b>3</b><span><strong>Review</strong><small>Evidence, the accused's response, an optional poll</small></span></li>
    <li><b>4</b><span><strong>Decision</strong><small>Written outcome, published without private evidence</small></span></li>
    <li><b>5</b><span><strong>Appeal</strong><small><?= Governance::APPEAL_WINDOW_DAYS ?> days to appeal before the case closes</small></span></li>
  </ol>
  <section class="mb-10">
    <h2 class="text-xl font-bold text-white mb-4">My governance cases</h2>
    <?php if (!$myCases): ?>
      <div class="empty-state p-6"><p class="text-sm text-gray-400">You have not filed a complaint and none has been filed about you. <span class="block mt-1 text-xs">(Preview as Member to see a filed case.)</span></p></div>
    <?php else: ?>
      <div class="overflow-x-auto"><table class="w-full text-sm data-table m-stack">
        <thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2 pr-3">Case</th><th class="py-2 pr-3">Role</th><th class="py-2 pr-3">Status</th><th class="py-2 pr-3">Updated</th><th class="py-2"><span class="sr-only">Open</span></th></tr></thead>
        <tbody><?php foreach ($myCases as $c): ?>
          <tr class="border-b border-slate-700/50">
            <td data-label="Case" class="py-2.5 pr-3"><span class="ref-code"><?= e($c['code']) ?></span></td>
            <td data-label="Role" class="py-2.5 pr-3 text-gray-400"><?= $c['filed_by'] === $me['id'] ? 'Complainant' : 'Accused' ?></td>
            <td data-label="Status" class="py-2.5 pr-3"><span class="gov-pill gov-<?= e($c['status']) ?>"><?= e(Governance::CASE_STATUS[$c['status']]) ?></span></td>
            <td data-label="Updated" class="py-2.5 pr-3 text-xs text-gray-500"><?= ago(intdiv($c['created_min'], 3)) ?></td>
            <td data-label="Open" class="py-2.5"><a class="text-indigo-400 hover:underline text-xs" href="<?= url('governance-case', ['id' => $c['id']]) ?>">Open &rarr;</a></td>
          </tr>
        <?php endforeach; ?></tbody>
      </table></div>
    <?php endif; ?>
  </section>
  <section class="mb-10">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4"><h2 class="text-xl font-bold text-white">Open petitions</h2><a href="<?= url('petitions') ?>" class="btn-outline">All petitions &amp; start one &rarr;</a></div>
    <?php foreach ($open as $p): $pct = min(100, (int) round(100 * $p['signatures'] / max(1, $p['required']))); ?>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-5 mb-3">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-2"><h3 class="font-bold text-white"><?= e($p['title']) ?></h3><span class="gov-pill gov-<?= e($p['status']) ?>"><?= e(str_replace('_', ' ', $p['status'])) ?></span></div>
        <p class="text-xs text-gray-500 mb-3"><?= $p['type'] === 'POLICY_REVIEW' ? 'Policy review' : 'Target: ' . e(user($p['target_id'])['username'] ?? '-') ?> &middot; started by <?= e(user($p['creator_id'])['username']) ?> &middot; expires in <?= $p['expires_days'] ?> days</p>
        <div class="gov-progress" role="img" aria-label="<?= $p['signatures'] ?> of <?= $p['required'] ?> signatures"><div class="gov-progress-bar" style="width:<?= $pct ?>%"></div></div>
        <p class="text-xs text-gray-400 mt-1"><?= $p['signatures'] ?> / <?= $p['required'] ?> signatures &middot; <a class="text-indigo-400 hover:underline" href="<?= url('petition', ['id' => $p['id']]) ?>">view and sign &rarr;</a></p>
      </div>
    <?php endforeach; ?>
  </section>
  <section class="mb-10 scroll-mt-20" id="polls">
    <h2 class="text-xl font-bold text-white mb-4">Confidence polls</h2>
    <?php foreach ($polls as $poll): ?>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-5 mb-3 flex flex-wrap items-center justify-between gap-3">
        <div><h3 class="font-bold text-white text-sm"><?= e($poll['question']) ?></h3><p class="text-xs text-gray-500 mt-1"><?= $poll['confidence'] + $poll['investigate'] + $poll['no_opinion'] ?> votes cast &middot; closes in <?= $poll['closes_hours'] ?> hours</p></div>
        <a href="<?= url('poll', ['id' => $poll['id']]) ?>" class="btn-outline">Vote now &rarr;</a>
      </div>
    <?php endforeach; ?>
  </section>
  <section>
    <h2 class="text-xl font-bold text-white mb-4">Published decisions</h2>
    <?php foreach ($decided as $d): ?>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-5 mb-3"><p class="text-xs text-gray-500 mb-2"><span class="ref-code"><?= e($d['code']) ?></span> &middot; <?= e(Governance::OUTCOMES[$d['outcome']] ?? '') ?> &middot; <?= ago(intdiv($d['created_min'], 2)) ?></p><p class="text-sm text-gray-300"><?= e($d['decision_summary']) ?></p></div>
    <?php endforeach; ?>
  </section>
</main>
