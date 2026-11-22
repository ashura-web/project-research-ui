<?php /* views/rank-request.php - ask for the next rank (real name: clearance / access-request). Markup reused from account.php. */
defined('MOCK') || exit;
$have = role_key();
$next = ['member' => 'contributor', 'contributor' => 'reviewer'][$have] ?? null;
$mine = where('rank_requests', fn ($q) => $q['user_id'] === $me['id']);
$pending = array_values(array_filter($mine, fn ($q) => $q['status'] === 'PENDING'))[0] ?? null;
$last = array_values(array_filter($mine, fn ($q) => $q['status'] !== 'PENDING'))[0] ?? null;
$notes = count(array_filter(rows('notes'), fn ($n) => $n['author_id'] === $me['id'] && $n['status'] === 'PUBLISHED'));
$arts = count(array_filter(rows('articles'), fn ($n) => $n['author_id'] === $me['id'] && $n['status'] === 'PUBLISHED'));
$tasks = $next === 'contributor'
    ? [['Get 2 notes approved', $notes, 2], ['Post 3 comments under articles', 3, 3], ['Save 3 articles to your reading list', 1, 3], ['Earn 100 credits', (int) $me['credits'], 100]]
    : ($next === 'reviewer' ? [['Publish 3 articles', $arts, 3], ['Have no returned article in the last 30 days', 1, 1], ['Earn 300 credits', (int) $me['credits'], 300]] : []);
$tasks = array_map(fn ($t) => [$t[0], $t[1], $t[2], $t[1] >= $t[2]], $tasks);
$tDone = count(array_filter($tasks, fn ($t) => $t[3]));
$allDone = $tasks && $tDone === count($tasks);
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="text-center mb-8">
    <p class="kicker mb-2">Account &mdash; rank requests</p>
    <h1 class="text-3xl font-bold page-title">Request a higher rank</h1>
    <p class="text-gray-400 text-sm mt-3">You are a <strong class="text-white"><?= role_title($have) ?></strong>. Each rank has a short list of tasks. When every task is done you can file a request, and the deciders see your full record.</p>
  </header>
  <?php if ($pending): ?>
    <div class="bg-slate-800/80 border border-amber-500/40 rounded-2xl p-6 mb-8"><p class="text-sm text-gray-300"><?= status_pill('PENDING') ?>&nbsp; You already have a request for <strong class="text-white"><?= role_title($pending['requested_role']) ?></strong> waiting (filed <?= when($pending['created_min'], 'M j, Y') ?>). Only one request can be open at a time.</p></div>
  <?php elseif ($last): ?>
    <div class="bg-slate-800/80 border <?= $last['status'] === 'APPROVED' ? 'border-green-500/40' : 'border-red-500/40' ?> rounded-2xl p-6 mb-8"><p class="text-sm text-gray-300 mb-2"><?= status_pill($last['status']) ?>&nbsp; Your last request for <strong class="text-white"><?= role_title($last['requested_role']) ?></strong> was decided.</p><?php if ($last['note']): ?><div class="review-note"><strong>Decision note:</strong> <?= e($last['note']) ?></div><?php endif; ?></div>
  <?php endif; ?>

  <section class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
    <?php foreach (['member', 'contributor', 'reviewer', 'admin'] as $code): $you = $code === $have; ?>
      <div class="theory-card<?= $you ? ' ring-1 ring-violet-500/60' : '' ?>"><div class="flex items-center gap-2 mb-2"><?= badge_html($code) ?><?php if ($you): ?><span class="ref-code" style="color:var(--accent-light);">&larr; you are here</span><?php endif; ?></div><p class="text-sm text-gray-400"><?= e(ROLE_UNLOCKS[$code]) ?></p></div>
    <?php endforeach; ?>
  </section>

  <?php if ($tasks): ?>
  <section id="tasks" class="bg-slate-800/80 border border-slate-700 rounded-2xl p-6 mb-8 scroll-mt-20" aria-labelledby="tasks-h">
    <div class="task-sum"><h2 id="tasks-h" class="text-lg font-bold text-white">Tasks for <?= role_title($next) ?></h2><span class="text-sm text-gray-400"><strong><?= $tDone ?></strong> of <?= count($tasks) ?> done</span></div>
    <p class="text-sm text-gray-400 mb-4">Counted from your approved work, comments, reading list and credits. Nothing to fill in.</p>
    <ul class="task-list">
      <?php foreach ($tasks as [$label, $h, $n, $ok]): ?><li class="<?= $ok ? 'is-done' : '' ?>"><span class="ds-tick" aria-hidden="true"><?= $ok ? nav_icon('check') : '' ?></span><span><?= e($label) ?><span class="sr-only"><?= $ok ? ' (done)' : ' (not done yet)' ?></span></span><b><?= $n === 1 ? ($ok ? 'Done' : 'Not yet') : number_format(min($h, $n)) . ' / ' . number_format($n) ?></b><?php if (!$ok && $n > 1): ?><span class="task-bar" aria-hidden="true"><i style="width:<?= (int) round($h / $n * 100) ?>%"></i></span><?php endif; ?></li><?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if (!$next): ?>
    <div class="bg-slate-800/80 border border-violet-500/40 rounded-2xl p-6 mb-8"><h2 class="text-lg font-bold text-white mb-2">Rank requests are closed at your rank</h2>
      <p class="text-sm text-gray-300"><?= $have === 'reviewer' ? 'Reviewer is the highest rank that can be requested. Admins are <strong class="text-white">appointed by the Owner</strong> through the <a href="' . url('governance') . '" class="text-indigo-400 hover:underline">governance pages</a>.' : 'Admin and Owner seats are appointed, never requested. See the <a href="' . url('help') . '" class="text-indigo-400 hover:underline">Help centre</a> for how appointments work.' ?></p></div>
  <?php else: ?>
    <section class="bg-slate-800/80 border border-slate-700 rounded-2xl p-8">
      <?php if ($pending): ?><p class="text-sm text-gray-500 text-center">Form locked while a request is pending.</p>
      <?php else: ?>
        <h2 class="text-xl font-bold text-white mb-1">Request the <?= role_title($next) ?> rank</h2>
        <p class="text-sm text-gray-400 mb-6"><?= $allDone ? 'Every task is done. The deciders see your task list and your work, and leave a written note with their decision.' : 'The request opens when every task above is done.' ?></p>
        <?= mock_form_open('rank-request', 'class="space-y-4"') ?><input type="hidden" name="requested_role" value="<?= $next ?>"><button type="submit" class="form-button w-full" <?= $allDone ? '' : 'disabled aria-disabled="true"' ?>><?= $allDone ? 'Send my request' : 'Finish the tasks to unlock' ?></button></form>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>
