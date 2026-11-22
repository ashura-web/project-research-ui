<?php /* views/note.php - one community note (real name: post/theory page). */
defined('MOCK') || exit;
$n = find('notes', (int) ($_GET['id'] ?? 1));
if ($n && $n['status'] !== 'PUBLISHED' && !at_least('reviewer')) $n = null;
$labels = ['NOTE' => 'NOTE', 'IDEA' => 'IDEA', 'FIELD_NOTE' => 'FIELD NOTE'];
$cls = ['NOTE' => 'theory', 'IDEA' => 'idea', 'FIELD_NOTE' => 'note'];
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
<?php if (!$n): ?>
  <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-10 text-center">
    <h1 class="text-2xl font-bold text-white mb-3">Note not found</h1>
    <p class="text-gray-400 text-sm mb-6">This note does not exist or has not been approved yet.</p>
    <a href="<?= url('gallery') ?>#community" class="btn-outline">Back to community notes</a>
  </div>
<?php else: $u = user($n['author_id']); ?>
  <article class="bg-slate-800/80 rounded-xl shadow-2xl border border-slate-700 p-8 sm:p-10">
    <header class="border-b border-slate-700 pb-5 mb-6">
      <div class="flex flex-wrap items-center gap-3 mb-3"><span class="badge-type <?= $cls[$n['type']] ?>"><?= $labels[$n['type']] ?></span><span class="ref-code">NOTE-<?= str_pad((string) $n['id'], 4, '0', STR_PAD_LEFT) ?></span><span class="ref-code">&#10003;&#xFE0E; <?= $n['status'] === 'PUBLISHED' ? 'APPROVED' : e($n['status']) ?></span></div>
      <h1 class="text-3xl font-bold text-white mb-3"><?= e($n['title']) ?></h1>
      <p class="text-xs text-gray-500">by <strong class="text-gray-300"><?= e($u['username']) ?></strong> &middot; written <?= when($n['created_min'] + 600, 'M j, Y') ?> &middot; published <?= when($n['created_min'], 'M j, Y') ?></p>
    </header>
    <div class="article-body"><p><?= nl2br(e($n['body'])) ?></p></div>
    <footer class="mt-8 pt-5 border-t border-slate-700 flex flex-wrap items-center justify-between gap-3">
      <?= member_chip($u, 'Posted by') ?>
      <div class="flex flex-wrap gap-2">
        <?php if (role_key() === 'owner'): ?><button type="button" class="btn-outline link-danger" data-confirm="Remove this note? The author is told why." data-reason>Remove (Owner)</button><?php endif; ?>
        <a href="<?= url('gallery') ?>#community" class="btn-outline">&larr; Back to notes</a>
      </div>
    </footer>
  </article>
<?php endif; ?>
</main>
