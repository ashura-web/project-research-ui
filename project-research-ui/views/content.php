<?php /* views/content.php - Owner content manager: every post, with "Remove" (reason required). Markup reused from admin.php (content view). */
defined('MOCK') || exit;
$tabs = ['article' => ['Articles', 'articles'], 'note' => ['Notes', 'notes'], 'media' => ['Media', 'media']];
$tab = array_key_exists($_GET['tab'] ?? '', $tabs) ? (string) $_GET['tab'] : 'article';
$list = rows($tabs[$tab][1]);
$open = fn (array $r) => $tab === 'article' ? url('article', ['slug' => $r['slug']]) : ($tab === 'note' ? url('note', ['id' => $r['id']]) : url('media'));
?>
<main class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Admin side &middot; Owner</p>
  <h1 class="text-3xl font-bold mb-2 page-title">Content manager</h1>
  <p class="text-gray-400 mb-6">Every article, note and media upload. "Remove" asks for a reason, which the author receives as a personal bulletin. A full copy of the post is kept in the audit log and credits already earned are not taken back.</p>
  <div class="flex flex-wrap gap-3 mb-4" aria-label="Kind of post">
    <?php foreach ($tabs as $k => [$label, $t]): ?><a href="<?= url('content', ['tab' => $k]) ?>" class="chip <?= $tab === $k ? 'is-active' : '' ?>"<?= $tab === $k ? ' aria-current="page"' : '' ?>><?= $label ?> <span class="chip-count"><?= count(rows($t)) ?></span></a><?php endforeach; ?>
  </div>
  <label class="sr-only" for="content-filter">Filter this list</label>
  <input id="content-filter" type="search" class="form-input mb-4 max-w-md" placeholder="Filter by title, author or status" data-table-filter="#content-table" data-count-target="#content-count">
  <span id="content-count" class="text-xs text-gray-500 ml-2" aria-live="polite"></span>
  <div class="overflow-x-auto bg-slate-800/80 rounded-xl border border-slate-700 p-4">
    <table id="content-table" class="w-full text-sm row-hover data-table mail-table m-stack">
      <thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2 pr-3">#</th><th class="py-2 pr-3">Title</th><th class="py-2 pr-3">Author</th><th class="py-2 pr-3">Status</th><th class="py-2 pr-3">Created</th><th class="py-2 pr-3"><span class="sr-only">Action</span></th></tr></thead>
      <tbody><?php foreach ($list as $r): ?>
        <tr class="border-b border-slate-700/50 align-top">
          <td data-label="#" class="py-2 pr-3 text-gray-500 whitespace-nowrap"><?= $r['id'] ?></td>
          <td data-label="Title" class="py-2 pr-3"><a class="text-indigo-300 hover:underline" href="<?= $open($r) ?>"><?= e($r['title']) ?></a><?php if (!empty($r['type'])): ?><br><small class="text-gray-500"><?= e(str_replace('_', ' ', $r['type'])) ?></small><?php endif; ?></td>
          <td data-label="Author" class="py-2 pr-3"><?= e(user($r['author_id'])['username'] ?? 'removed member') ?></td>
          <td data-label="Status" class="py-2 pr-3"><?= status_pill($r['status']) ?></td>
          <td data-label="Created" class="py-2 pr-3 text-gray-400 whitespace-nowrap"><?= ago($r['created_min']) ?></td>
          <td data-label="Action" class="py-2 pr-3 text-right"><?= mock_form_open('content_remove', 'class="inline" data-confirm="Remove &quot;' . e($r['title']) . '&quot;? The author receives your reason as a personal bulletin." data-confirm-title="Remove post" data-confirm-reason="reason" data-confirm-reason-label="Reason for the author *" data-confirm-label="Remove" data-confirm-tone="danger"') ?><button type="submit" class="text-xs text-red-300 hover:text-red-200 font-semibold">Remove</button></form></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table>
  </div>
  <h2 class="text-xl font-bold text-white mt-10 mb-3">Recent removals</h2>
  <ul class="space-y-2 text-sm">
    <li class="bg-slate-800/60 border border-slate-700 rounded-lg p-3"><span class="text-gray-400"><?= ago(5200) ?> &middot; by owner_ada &middot; author spam_bot_x</span><br><span class="text-gray-200">Removed note "Buy cheap followers" (spam). Mock entry.</span></li>
  </ul>
  <p class="text-xs text-gray-500 mt-3">Full copies: audit log, action "content_removed".</p>
</main>
