<?php /* views/governance-case.php - One governance case. Markup reused from governance.php (governance-case view).
   Same visibility rule as the real site: only the complainant, the accused and the Owner (reviewer) can open it.
   Demo: ?id=1 as Member (complainant) or Owner (oversight console); ?id=2 as Admin (accused, appeal form). */
defined('MOCK') || exit;
$case = find('cases', (int) ($_GET['id'] ?? 1));
$isOwner = at_least('owner');
$isSubject = $case && $case['subject_id'] === $me['id'];
$isParty = $case && ($case['filed_by'] === $me['id'] || $isSubject || $isOwner);
if (!$isParty): ?>
<main class="max-w-2xl mx-auto py-16 px-4 text-center content-overlay">
  <div class="empty-state p-10">
    <h1 class="text-2xl font-bold text-white mb-3">Case not found</h1>
    <p class="text-sm text-gray-400 mb-6">This case does not exist, or you are not a party to it. Governance cases are visible only to the complainant, the accused member and the authorised reviewer.</p>
    <p class="text-xs text-gray-500 mb-6">Mock tip: use "Preview as" Member or Owner for case 1, Admin for case 2.</p>
    <a href="<?= url('governance') ?>" class="btn-outline">&larr; Back to governance</a>
  </div>
</main>
<?php return; endif;
$subject = user($case['subject_id']); $filer = user($case['filed_by']);
$poll = find('polls', $case['id'], 'case_id');
$canAnswer = $isSubject && in_array($case['status'], ['TRIAGE', 'UNDER_REVIEW', 'AWAITING_RESPONSE', 'APPEALED'], true) && $case['response'] === '';
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1"><a href="<?= url('governance') ?>" class="hover:underline">Community governance</a></p>
  <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
    <h1 class="text-3xl font-bold page-title">Case <?= e($case['code']) ?></h1>
    <span class="gov-pill gov-<?= e($case['status']) ?>"><?= e(Governance::CASE_STATUS[$case['status']]) ?></span>
  </div>
  <p class="text-sm text-gray-500 mb-8">Filed <?= ago($case['created_min']) ?> by <?= e($filer['username']) ?> &middot; concerning <strong class="text-gray-300"><?= e($subject['username']) ?></strong> (<?= role_title($subject['role']) ?>)</p>
  <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
    <p class="text-xs uppercase tracking-wider text-gray-500 mb-2"><?= e(Governance::CATEGORIES[$case['category']]) ?></p>
    <h2 class="text-xl font-bold text-white mb-3"><?= e($case['subject_line']) ?></h2>
    <p class="text-sm text-gray-300 whitespace-pre-line"><?= e($case['description']) ?></p>
  </section>
  <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
    <h3 class="text-lg font-bold text-white mb-3">Evidence (private)</h3>
    <ul class="space-y-2">
      <li class="text-sm text-gray-300 flex flex-wrap items-center gap-2"><span class="pill">TEXT</span><span class="whitespace-pre-line">Mock evidence: the removal notice had an empty reason field.</span></li>
      <li class="text-sm text-gray-300 flex flex-wrap items-center gap-2"><span class="pill">FILE</span><button type="button" class="text-indigo-400 hover:underline" data-mock-msg="Evidence files are private downloads in the real site. The mock has no files.">Download</button><span class="text-gray-500 text-xs">screenshot-mock.png &middot; 84 KB</span></li>
    </ul>
  </section>
  <?php if ($case['response'] !== ''): ?>
    <section class="bg-slate-800/80 border border-amber-500/30 rounded-xl p-6 mb-6"><h3 class="text-lg font-bold text-white mb-1">Response of the accused: <?= e($subject['username']) ?></h3><p class="text-sm text-gray-300 whitespace-pre-line mt-3"><?= e($case['response']) ?></p></section>
  <?php endif; ?>
  <?php if ($case['outcome']): ?>
    <section class="mb-6"><h3 class="text-lg font-bold text-white mb-3">Decision history</h3>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-5 mb-3"><p class="text-xs text-gray-500 mb-2"><span class="pill">FIRST</span> <strong class="text-gray-300"><?= e(Governance::OUTCOMES[$case['outcome']]) ?></strong> &middot; reviewed by owner_ada &middot; <?= ago(intdiv($case['created_min'], 2)) ?></p><p class="text-sm text-gray-300">Mock decision note: the reply broke the support tone guide; first offence.</p></div>
    </section>
  <?php endif; ?>
  <?php if ($case['decision_summary']): ?>
    <section class="bg-slate-800/80 border border-violet-500/30 rounded-xl p-6 mb-6"><h3 class="text-lg font-bold text-white mb-2">Public summary (redacted)</h3><p class="text-sm text-gray-300"><?= e($case['decision_summary']) ?></p></section>
  <?php endif; ?>
  <?php if ($errors): foreach ($errors as $err): ?><div class="form-status error mb-4"><?= e($err) ?></div><?php endforeach; endif; ?>
  <?php if ($canAnswer): ?>
    <section class="bg-slate-800/80 border border-amber-500/40 rounded-xl p-6 mb-6"><h3 class="text-lg font-bold text-white mb-2">Your response</h3><p class="text-sm text-gray-400 mb-3">You have the right to answer before any decision. Only the reviewer sees it.</p>
      <?= mock_form_open('case_response', 'class="space-y-3" data-validate novalidate') ?><label for="cr" class="sr-only">Response</label><textarea id="cr" name="response" rows="5" class="form-input" required minlength="20" data-label="Response" placeholder="Your account of what happened..."></textarea><button type="submit" class="form-button" style="width:auto">Submit response</button></form>
    </section>
  <?php elseif ($isSubject && $case['status'] === 'DECIDED'): ?>
    <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6"><h3 class="text-lg font-bold text-white mb-2">Appeal</h3><p class="text-sm text-gray-400 mb-3">You may appeal <strong>once</strong>, within <?= Governance::APPEAL_WINDOW_DAYS ?> days. Appeals go to the external authority (the site owner).</p>
      <?= mock_form_open('appeal', 'class="space-y-3" data-validate novalidate') ?><label for="ap" class="sr-only">Grounds</label><textarea id="ap" name="note" rows="3" class="form-input" required minlength="10" data-label="Grounds" placeholder="Grounds for the appeal..."></textarea><button type="submit" class="btn-outline">Appeal the decision</button></form>
    </section>
  <?php endif; ?>
  <?php if ($isOwner && !$isSubject): ?>
    <section class="bg-slate-800/80 border border-violet-500/40 rounded-xl p-6 mb-6">
      <h3 class="text-lg font-bold text-white mb-4">Oversight console <span class="text-xs text-gray-500 font-normal">(Owner)</span></h3>
      <?php if ($case['status'] !== 'DECIDED'): ?>
        <div class="flex flex-wrap gap-2 mb-6">
          <?= mock_form_open('request_response', 'data-confirm="Request a written response from the accused member?"') ?><button class="btn-outline" type="submit">Request accused's response</button></form>
          <?php if ($poll): ?><a class="btn-outline" href="<?= url('poll', ['id' => $poll['id']]) ?>">View the open confidence poll</a><?php else: ?><?= mock_form_open('open_poll', 'data-confirm="Open a community confidence poll about this Admin?"') ?><button class="btn-outline" type="submit">Open confidence poll</button></form><?php endif; ?>
        </div>
        <?= mock_form_open('decide', 'class="space-y-3 border-t border-slate-700 pt-5" data-validate novalidate') ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><div><label class="block text-sm font-semibold text-gray-300 mb-1" for="outcome">Outcome</label><select id="outcome" name="outcome" class="form-input"><?php foreach (Governance::OUTCOMES as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?></select></div></div>
          <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="decide-note">Written reasons (required)</label><textarea id="decide-note" name="note" rows="3" class="form-input" required minlength="10" data-label="Written reasons" placeholder="Why this outcome? Cite the evidence and the help-centre rule..."></textarea></div>
          <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="decide-summary">Public redacted summary (required)</label><textarea id="decide-summary" name="summary" rows="2" class="form-input" required minlength="10" data-label="Public summary" placeholder="One or two neutral sentences, no private evidence."></textarea></div>
          <button type="submit" class="form-button" style="width:auto">Record the decision</button>
          <p class="text-xs text-gray-500">Demoted / removed / suspended change the member's rank only at this step, inside one transaction, and are fully audited.</p>
        </form>
      <?php else: ?>
        <p class="text-sm text-gray-400">The appeal window stays open for <strong class="text-amber-300">2 more days</strong>. The case can be closed after that.</p>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>
