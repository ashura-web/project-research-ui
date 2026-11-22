<?php /* views/poll.php - Confidence poll: vote form, then live result bars. Markup reused from governance.php (governance-poll view).
   The accused Admin cannot vote; a vote is kept only in this browser session. */
defined('MOCK') || exit;
$poll = find('polls', (int) ($_GET['id'] ?? 1));
if (!$poll): ?>
<main class="max-w-2xl mx-auto py-16 px-4 text-center content-overlay"><div class="empty-state p-10"><h1 class="text-2xl font-bold text-white mb-3">Poll not found</h1><a href="<?= url('governance') ?>" class="btn-outline">&larr; Back to governance</a></div></main>
<?php return; endif;
$case = find('cases', $poll['case_id']); $target = user($case['subject_id']);
$mine = $_SESSION['mock']['vote'][$poll['id']] ?? null;
$isTarget = $target['id'] === $me['id'];
$canVote = !$mine && !$isTarget;
$counts = []; foreach (Governance::POLL_CHOICES as $k => $l) $counts[$k] = $poll[$k] + ($mine === $k ? 1 : 0);
$total = max(1, array_sum($counts));
?>
<main class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1"><a href="<?= url('governance') ?>" class="hover:underline">Community governance</a></p>
  <div class="flex flex-wrap items-center justify-between gap-3 mb-1"><h1 class="text-3xl font-bold page-title">Confidence poll</h1><span class="gov-pill gov-OPEN">open</span></div>
  <p class="text-sm text-gray-500 mb-8">About <strong class="text-gray-300"><?= e($target['username']) ?></strong> (Admin) &middot; part of case <span class="ref-code"><?= e($case['code']) ?></span> &middot; closes in <?= $poll['closes_hours'] ?> hours</p>
  <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
    <h2 class="text-lg font-bold text-white mb-1"><?= e($poll['question']) ?></h2>
    <p class="text-xs text-gray-500 mb-6">Poll results are advisory evidence for the reviewer; they are not a removal vote. Individual choices are never published.</p>
    <?php if ($canVote): ?>
      <?= mock_form_open('vote', 'class="space-y-3" data-validate novalidate') ?>
        <?php foreach (['confidence' => 'I have confidence in this Admin', 'investigate' => 'This should be formally investigated', 'no_opinion' => 'No opinion'] as $val => $label): ?>
          <label class="flex items-center gap-3 bg-slate-900/60 border border-slate-700 rounded-lg px-4 py-3 cursor-pointer hover:border-violet-500/60"><input type="radio" name="choice" value="<?= $val ?>" class="accent-violet-500" required data-label="Your answer"> <span class="text-sm text-gray-200"><?= $label ?></span></label>
        <?php endforeach; ?>
        <?= field_error($errors, 'choice', 'choice') ?>
        <button type="submit" class="form-button" style="width:auto">Cast my vote</button>
      </form>
    <?php else: ?>
      <?php if ($mine): ?><p class="text-sm text-green-400 font-semibold mb-4">&#10004;&#xFE0E; You voted. Results update below as others vote.</p><?php endif; ?>
      <?php if ($isTarget): ?><p class="text-sm text-gray-400 mb-4">You are the subject of this poll, so you cannot vote in it.</p><?php endif; ?>
    <?php endif; ?>
    <div class="mt-8 space-y-3">
      <?php foreach (Governance::POLL_CHOICES as $k => $label): $n = $counts[$k]; $pv = (int) round(100 * $n / $total); ?>
        <div><div class="flex justify-between text-xs text-gray-400 mb-1"><span><?= $label ?><?= $mine === $k ? ' (your vote)' : '' ?></span><span><?= $n ?> (<?= $pv ?>%)</span></div><div class="gov-progress"><div class="gov-progress-bar<?= $mine === $k ? ' mine' : '' ?>" style="width:<?= $pv ?>%"></div></div></div>
      <?php endforeach; ?>
      <p class="text-xs text-gray-500 pt-1"><?= array_sum($counts) ?> votes in total.</p>
    </div>
  </section>
</main>
