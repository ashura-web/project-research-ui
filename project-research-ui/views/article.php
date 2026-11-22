<?php /* views/article.php - one article (real name: dossier/universe page). Score gauge, tools, body, revisions,
   related articles and the docked discussion. Ported from the real view; the chat sends nothing. */
defined('MOCK') || exit;
$a = find('articles', (string) ($_GET['slug'] ?? 'universe'), 'slug');
if ($a && $a['status'] !== 'PUBLISHED' && !at_least('reviewer') && ($me['id'] ?? 0) !== $a['author_id']) $a = null;
$myId = $me['id'] ?? null;
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
<?php if (!$a): ?>
  <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-10 text-center">
    <div class="es-icon"><?= nav_icon('shield') ?></div>
    <h1 class="text-2xl font-bold text-white mb-3">Article not found</h1>
    <p class="text-gray-400 text-sm mb-6">This article does not exist, or it has not been published yet.</p>
    <div class="flex flex-wrap gap-3 justify-center"><a href="<?= url('explore') ?>" class="btn-outline">Search Explore</a><a href="<?= url('gallery') ?>" class="btn-outline">Return to the gallery</a></div>
  </div>
<?php else:
  $s = $a['score']; $tone = $s >= 7 ? 'text-red-300' : ($s >= 4 ? 'text-amber-300' : 'text-green-300');
  $rung = find('ladder', $a['slug'], 'slug');
  $comments = where('comments', fn ($c) => $c['article_id'] === $a['id']);
  $comments = array_reverse($comments);
  $related = array_slice(array_values(array_filter(published('articles'), fn ($r) => $r['id'] !== $a['id'] && abs($r['score'] - $s) <= 2)), 0, 3);
  $mins = max(1, (int) ceil(str_word_count($a['body']) / 200));
?>
  <?php if ($a['status'] !== 'PUBLISHED'): ?><div class="form-status info mb-4">Preview: this article is <strong><?= e($a['status']) ?></strong> and only visible to its author and reviewers.</div><?php endif; ?>
  <article id="article" data-read-progress class="bg-slate-800/80 rounded-xl shadow-2xl border border-slate-700 p-5 sm:p-10">
    <figure class="article-hero"><?= cover($a['title'], crc32($a['slug']) % 360, 'hero-cover') ?><figcaption>Cover: generated placeholder (the real site credited every image here).</figcaption></figure>
    <header class="border-b border-slate-700 pb-6 mb-6">
      <div class="flex flex-wrap items-center gap-3 mb-3"><span class="badge-type idea">ARTICLE</span><span class="ref-code"><?= e($a['code'] ?: 'no code yet') ?></span><?php if ($a['status'] === 'PUBLISHED'): ?><span class="ref-code">&#10003;&#xFE0E; REVIEWED</span><?php endif; ?></div>
      <h1 class="text-3xl sm:text-4xl font-bold page-title mb-3"><?= e($a['title']) ?></h1>
      <div class="flex flex-wrap gap-2 text-xs">
        <span class="px-2 py-0.5 rounded bg-indigo-900 text-indigo-200 font-bold" title="Level on the physics side, or HYPER for speculative"><?= $a['level'] === 'HYPER' ? 'SPECULATIVE' : 'LEVEL ' . e($a['level']) ?></span>
        <span class="px-2 py-0.5 rounded font-bold <?= $s >= 7 ? 'bg-red-900 text-red-200' : ($s >= 4 ? 'bg-amber-900 text-amber-200' : 'bg-green-900 text-green-200') ?>"><a href="<?= url('glossary') ?>#score" class="hover:underline" title="What the scores mean">SCORE <?= $s ?>/10</a></span>
        <a href="<?= url('explore', ['category' => $a['category_id']]) ?>" class="cat-tag"><?= e(category_name($a['category_id'])) ?></a>
      </div>
      <p class="text-xs text-gray-500 mt-3"><?= $mins ?> min read &middot; <?= number_format($a['views']) ?> views</p>
      <div class="article-tools no-print mt-4" role="toolbar" aria-label="Article actions">
        <?php if (signed_in()): ?><button type="button" class="tool-btn" data-bookmark aria-pressed="false">&#9733;&#xFE0E; <span class="bm-label">Save to reading list</span></button>
        <?php else: ?><a class="tool-btn" href="<?= url('login') ?>" title="Log in to keep a reading list">&#9734;&#xFE0E; Log in to save</a><?php endif; ?>
        <button type="button" class="tool-btn" data-share data-title="<?= e($a['title']) ?>">Share</button>
        <button type="button" class="tool-btn" data-print>Print article</button>
        <button type="button" class="tool-btn cm-chip" data-cm-open aria-controls="discussion" aria-expanded="false" title="Open the discussion"><?= nav_icon('chat') ?><span>Discussion</span><span class="cm-chip-count" aria-hidden="true"><?= count($comments) ?></span></button>
        <?php if (role_key() === 'owner'): ?><button type="button" class="tool-btn link-danger" data-confirm="Remove &quot;<?= e($a['title']) ?>&quot; from the site? The author is told why." data-reason>Remove (Owner)</button><?php endif; ?>
      </div>
      <p class="text-gray-300 italic mt-4 leading-relaxed border-l-2 border-violet-500/60 pl-4"><?= e($a['summary']) ?></p>
    </header>

    <section class="mb-8">
      <div class="flex items-center justify-between text-xs mb-2"><span class="font-bold tracking-widest text-gray-400 uppercase">Score gauge</span><span class="font-bold <?= $tone ?>"><?= $s ?> / 10</span></div>
      <?= score_bar($s) ?>
      <p class="text-xs text-gray-400 mt-2">Score follows containment: never lower than anything this structure contains. <a href="<?= url('glossary') ?>#score" class="text-indigo-300 hover:underline whitespace-nowrap">How scores are given</a></p>
      <?php if ($rung): ?><p class="text-xs text-gray-400 mt-1">Rung <?= $rung['rung'] ?> of <?= count(rows('ladder')) ?> on the ladder &middot; Class <?= $rung['cls'] ?> <a href="<?= url('glossary') ?>#ladder" class="text-indigo-300 hover:underline whitespace-nowrap">What this means</a></p><?php endif; ?>
    </section>

    <div class="article-body">
      <?php foreach (preg_split("/\n\s*\n/", $a['body']) as $i => $p): ?><?php if ($i === 1): ?><h2>Details</h2><?php endif; ?><p><?= e($p) ?></p><?php endforeach; ?>
      <p class="text-xs text-gray-500">Source: placeholder reference. Licence: sample text written for this mock.</p>
    </div>

    <footer class="mt-8 pt-5 border-t border-slate-700 flex flex-wrap items-center justify-between gap-3">
      <div class="flex flex-wrap items-center gap-3"><?= member_chip(user($a['author_id']), 'Written by') ?><?php if ($a['reviewer_id']): ?><?= member_chip(user($a['reviewer_id']), 'Reviewed by', 'xs') ?><?php endif; ?><span class="text-xs text-gray-500">published <?= when($a['created_min'], 'M j, Y') ?></span></div>
      <a href="<?= url('explore') ?>" class="btn-outline no-print" data-back>&larr; Back</a>
    </footer>

    <details class="revision-log no-print mt-6">
      <summary>Revision history (2)</summary>
      <ol class="mt-3 space-y-2 text-xs text-gray-400">
        <li><time><?= when($a['created_min']) ?></time> &middot; <?= e(user($a['author_id'])['username']) ?> &middot; First version &middot; <?= strlen($a['body']) ?> chars</li>
        <li><time><?= when(max(1, $a['created_min'] - 300)) ?></time> &middot; <?= e(user($a['reviewer_id'] ?? $a['author_id'])['username']) ?> &middot; Fixed a typo &middot; <?= strlen($a['body']) + 4 ?> chars</li>
      </ol>
    </details>
  </article>

  <div class="cm-dock" data-cm-chat>
    <button type="button" class="cm-fab" data-cm-open aria-controls="discussion" aria-expanded="false" title="Discussion" aria-label="Open the discussion"><?= nav_icon('chat') ?><span class="cm-fab-badge" hidden>0</span></button>
    <section id="discussion" class="cm-panel cm-chat" aria-labelledby="cm-h" aria-hidden="true">
      <header class="cm-top">
        <div class="cm-top-text"><h2 id="cm-h">Discussion <span class="cm-count"><?= count($comments) ?></span></h2><p>Talk about <?= e($a['title']) ?>. Be kind and cite sources.</p></div>
        <button type="button" class="cm-close" data-cm-close aria-label="Close the discussion">&times;</button>
      </header>
      <ol class="cm-list" data-cm-list aria-live="polite">
        <?php foreach ($comments as $c): $u = user($c['user_id']); ?>
          <li class="cm-msg<?= $u['id'] === $myId ? ' is-mine' : '' ?>" id="comment-<?= $c['id'] ?>">
            <a class="cm-ball" href="<?= url('member', ['u' => $u['username']]) ?>" title="<?= e($u['username']) ?>"><?= avatar_html($u) ?></a>
            <div class="cm-bubble"><header class="cm-head"><a href="<?= url('member', ['u' => $u['username']]) ?>" class="cm-name"><?= e($u['username']) ?></a><span class="cm-rank"><?= e(role_title($u['role'])) ?></span><time><?= e(ago($c['created_min'])) ?></time></header>
              <p class="cm-body"><?= e($c['body']) ?></p>
              <?php if (signed_in()): ?><div class="cm-actions"><button type="button" class="cm-act" data-cm-reply>Reply</button><?php if (at_least('reviewer')): ?><button type="button" class="cm-act cm-danger" data-confirm="Remove this message?">Remove</button><?php endif; ?></div><?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
      <?php if (signed_in()): ?>
        <form class="cm-form" data-cm-form novalidate>
          <span class="cm-ball cm-ball-me"><?= avatar_html($me) ?></span>
          <label for="cm-body" class="sr-only">Your message</label>
          <textarea id="cm-body" name="body" rows="1" maxlength="1000" required placeholder="Message as <?= e($me['username']) ?>..." data-cm-input></textarea>
          <button type="submit" class="cm-send" aria-label="Send message"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M3 20.5 21 12 3 3.5l.01 6.6L15 12 3.01 13.9z"/></svg></button>
        </form>
        <p class="cm-hint">Enter sends, Shift+Enter makes a new line. In the mock your message only appears on this screen.</p>
      <?php else: ?>
        <p class="cm-login"><a href="<?= url('login') ?>">Log in</a> or <a href="<?= url('signup') ?>">create a free account</a> to join the discussion.</p>
      <?php endif; ?>
    </section>
  </div>

  <?php if ($related): ?>
  <section class="mt-10 reveal">
    <h2 class="text-xl font-bold text-white mb-5 border-b border-slate-700 pb-2">Related articles</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <?php foreach ($related as $r): ?>
        <a href="<?= url('article', ['slug' => $r['slug']]) ?>" class="block group"><div class="theory-card h-full">
          <div class="flex flex-wrap gap-2 text-xs mb-2"><span class="px-2 py-0.5 rounded bg-indigo-900 text-indigo-200 font-bold">L-<?= e($r['level']) ?></span><span class="px-2 py-0.5 rounded font-bold <?= $r['score'] >= 7 ? 'bg-red-900 text-red-200' : 'bg-slate-700 text-gray-200' ?>">S<?= $r['score'] ?></span></div>
          <h3 class="font-bold text-white mb-1 group-hover:text-violet-300 transition duration-200"><?= e($r['title']) ?></h3>
          <p class="text-xs text-gray-400"><?= e(short($r['summary'], 90)) ?></p>
        </div></a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
<?php endif; ?>
</main>
