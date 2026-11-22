<?php /* views/review-queue.php - Reviewer duty station: pending articles, notes and media. Markup reused from admin.php.
   Approve = plain mock POST. Reject = confirm dialog that requires a note for the author. */
defined('MOCK') || exit;
$pending = where('articles', fn ($a) => $a['status'] === 'PENDING');
$pendingPosts = where('notes', fn ($a) => $a['status'] === 'PENDING');
$pendingMedia = where('media', fn ($a) => $a['status'] === 'PENDING');
$total = count($pending) + count($pendingPosts) + count($pendingMedia);
$typeLabels = ['NOTE' => 'NOTE', 'IDEA' => 'IDEA', 'FIELD_NOTE' => 'FIELD NOTE'];
$byline = fn (array $x) => '<span class="text-xs text-gray-400">by <strong class="text-gray-300">' . e(user($x['author_id'])['username'] ?? 'unknown') . '</strong> &middot; ' . when($x['created_min']) . '</span>';
$actions = function (array $x, string $kind, string $msg) use ($me): string {
    if ($x['author_id'] === $me['id']) return '<p class="own-item-note mt-4">This is your own submission. Another reviewer has to approve or reject it.</p>';
    return '<div class="flex flex-wrap gap-3 mt-4">'
        . mock_form_open($kind . '_approve', 'class="inline"') . '<button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-lg transition duration-300">&#10004;&#xFE0E; Approve &amp; publish</button></form>'
        . mock_form_open($kind . '_reject', 'class="inline" data-confirm="' . e($msg) . '" data-confirm-title="Reject submission" data-confirm-reason="review_note" data-confirm-label="Return to author" data-confirm-tone="danger"')
        . '<button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm font-bold rounded-lg transition duration-300">&#10006;&#xFE0E; Reject</button></form></div>';
};
?>
<main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mb-8">
    <p class="kicker mb-1">Reviewer duty station</p>
    <h1 class="text-3xl font-bold page-title">Review queue</h1>
    <p class="text-gray-400 mt-2"><?= $total === 0 ? 'All clear: nothing waiting.' : $total . ' item' . ($total === 1 ? '' : 's') . ' waiting for a decision.' ?></p>
  </header>
  <?php if ($total): ?><nav class="flex flex-wrap gap-2 mb-8 text-xs"><?php if ($pending): ?><a href="#sec-articles" class="btn-outline !py-1.5">Articles (<?= count($pending) ?>)</a><?php endif; ?><?php if ($pendingPosts): ?><a href="#sec-posts" class="btn-outline !py-1.5">Notes (<?= count($pendingPosts) ?>)</a><?php endif; ?><?php if ($pendingMedia): ?><a href="#sec-media" class="btn-outline !py-1.5">Media (<?= count($pendingMedia) ?>)</a><?php endif; ?></nav><?php endif; ?>

  <section id="sec-articles" class="mb-12 scroll-mt-6">
    <h2 class="text-xl font-bold text-white mb-4 border-b border-slate-700 pb-2">Article submissions <span class="text-sm font-normal text-gray-500">(<?= count($pending) ?>)</span></h2>
    <?php if (!$pending): ?><p class="text-gray-500 text-sm bg-slate-800/60 border border-slate-700 rounded-xl p-6 text-center">No pending articles. &#10003;&#xFE0E;</p><?php endif; ?>
    <?php foreach ($pending as $p): ?>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-2 mb-3"><h3 class="text-xl font-bold text-white"><?= e($p['title']) ?></h3><?= $byline($p) ?></div>
        <div class="flex flex-wrap gap-2 text-xs mb-3">
          <?php if ($p['code']): ?><span class="px-2 py-0.5 rounded bg-slate-700 text-gray-200"><?= e($p['code']) ?></span><?php endif; ?>
          <span class="px-2 py-0.5 rounded bg-indigo-900 text-indigo-200">Level <?= e($p['level']) ?></span>
          <span class="px-2 py-0.5 rounded <?= $p['score'] >= 7 ? 'bg-red-900 text-red-200' : 'bg-slate-700 text-gray-200' ?>">Score <?= $p['score'] ?>/10</span>
          <span class="px-2 py-0.5 rounded bg-slate-700 text-gray-200"><?= e(category_name($p['category_id'])) ?></span>
        </div>
        <p class="text-sm text-gray-300 mb-3"><?= e($p['summary']) ?></p>
        <details class="review-preview"><summary>Read the full article (<?= number_format(strlen($p['body'])) ?> characters)</summary><div class="text-sm text-gray-300 whitespace-pre-line mt-3 max-h-80 overflow-y-auto pr-2"><?= e($p['body']) ?></div></details>
        <?= $actions($p, 'article', 'The article returns to the author as a draft. Tell them what to fix so they can resubmit.') ?>
      </div>
    <?php endforeach; ?>
  </section>

  <section id="sec-posts" class="mb-12 scroll-mt-6">
    <h2 class="text-xl font-bold text-white mb-4 border-b border-slate-700 pb-2">Community notes <span class="text-sm font-normal text-gray-500">(<?= count($pendingPosts) ?>)</span></h2>
    <?php if (!$pendingPosts): ?><p class="text-gray-500 text-sm bg-slate-800/60 border border-slate-700 rounded-xl p-6 text-center">No pending notes. &#10003;&#xFE0E;</p><?php endif; ?>
    <?php foreach ($pendingPosts as $cp): ?>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-2 mb-3"><h3 class="text-lg font-bold text-white"><?= e($cp['title']) ?></h3><?= $byline($cp) ?></div>
        <div class="flex flex-wrap gap-2 text-xs mb-3"><span class="badge-type <?= ['NOTE' => 'theory', 'IDEA' => 'idea', 'FIELD_NOTE' => 'note'][$cp['type']] ?>"><?= $typeLabels[$cp['type']] ?></span></div>
        <p class="text-sm text-gray-300 whitespace-pre-line border-l-2 border-slate-600 pl-4 mb-2 max-h-60 overflow-y-auto"><?= e($cp['body']) ?></p>
        <?= $actions($cp, 'note', 'The author will see this note marked REJECTED together with your note.') ?>
      </div>
    <?php endforeach; ?>
  </section>

  <section id="sec-media" class="scroll-mt-6">
    <h2 class="text-xl font-bold text-white mb-4 border-b border-slate-700 pb-2">Media uploads <span class="text-sm font-normal text-gray-500">(<?= count($pendingMedia) ?>)</span></h2>
    <?php if (!$pendingMedia): ?><p class="text-gray-500 text-sm bg-slate-800/60 border border-slate-700 rounded-xl p-6 text-center">No pending media. &#10003;&#xFE0E;</p><?php endif; ?>
    <?php foreach ($pendingMedia as $m): ?>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
        <div class="flex flex-wrap justify-between items-start gap-2 mb-3"><h3 class="text-lg font-bold text-white"><?= e($m['title']) ?></h3><?= $byline($m) ?></div>
        <div class="flex flex-wrap gap-4 items-start">
          <?= cover($m['title'], (int) $m['hue'], 'rq-thumb') ?>
          <div class="flex-1 min-w-[200px]"><div class="flex flex-wrap gap-2 text-xs mb-2"><span class="px-2 py-0.5 rounded bg-slate-700 text-gray-200"><?= e($m['type']) ?></span><span class="px-2 py-0.5 rounded bg-slate-900 text-gray-400 ref-code">uploads/mock-<?= $m['id'] ?>.webp</span></div><p class="text-sm text-gray-300 mb-2"><?= e($m['caption']) ?></p></div>
        </div>
        <?= $actions($m, 'media', 'The upload will not appear in the public archive. The uploader will see your note.') ?>
      </div>
    <?php endforeach; ?>
  </section>
</main>
