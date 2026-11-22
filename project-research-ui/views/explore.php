<?php /* views/explore.php - search with tabs, filters, sort, active-filter chips and pagination (real name: Archive Explorer).
   The filtering really runs, in PHP, over the mock rows - so the page behaves like the real one without a search index. */
defined('MOCK') || exit;
$tab = in_array($_GET['tab'] ?? '', ['articles', 'notes', 'media'], true) ? $_GET['tab'] : 'articles';
$q = trim((string) ($_GET['q'] ?? ''));
$f = ['level' => (string) ($_GET['level'] ?? ''), 'score' => (string) ($_GET['score'] ?? ''), 'category' => (string) ($_GET['category'] ?? ''),
      'sort' => (string) ($_GET['sort'] ?? 'newest'), 'type' => (string) ($_GET['type'] ?? ''), 'media_type' => (string) ($_GET['media_type'] ?? '')];
$has = fn (string ...$t) => $q === '' || stripos(implode(' ', $t), $q) !== false;
$hl = function (string $s) use ($q): string {
    $s = e($s);
    return $q === '' ? $s : preg_replace('/(' . preg_quote(e($q), '/') . ')/i', '<mark>$1</mark>', $s);
};
if ($tab === 'articles') {
    $list = array_filter(published('articles'), fn ($a) => $has($a['title'], $a['summary'], $a['code'])
        && ($f['level'] === '' || $a['level'] === $f['level'])
        && ($f['category'] === '' || (string) $a['category_id'] === $f['category'])
        && ($f['score'] === '' || ($f['score'] === 'low' ? $a['score'] <= 3 : ($f['score'] === 'mid' ? $a['score'] >= 4 && $a['score'] <= 6 : $a['score'] >= 7))));
    $sorts = ['newest' => fn ($a, $b) => $a['created_min'] <=> $b['created_min'], 'oldest' => fn ($a, $b) => $b['created_min'] <=> $a['created_min'],
              'name' => fn ($a, $b) => strcmp($a['title'], $b['title']), 'score' => fn ($a, $b) => $b['score'] <=> $a['score'], 'popular' => fn ($a, $b) => $b['views'] <=> $a['views']];
    usort($list, $sorts[$f['sort']] ?? $sorts['newest']);
} elseif ($tab === 'notes') {
    $list = array_values(array_filter(published('notes'), fn ($n) => $has($n['title'], $n['body']) && ($f['type'] === '' || $n['type'] === $f['type'])));
} else {
    $list = array_values(array_filter(published('media'), fn ($m) => $has($m['title'], $m['caption']) && ($f['media_type'] === '' || $m['type'] === $f['media_type'])));
}
$per = 6; $total = count($list); $pages = max(1, (int) ceil($total / $per)); $pg = min($pages, max(1, (int) ($_GET['p'] ?? 1)));
$rowsShown = array_slice(array_values($list), ($pg - 1) * $per, $per);
$keep = array_filter(['tab' => $tab, 'q' => $q] + $f, fn ($v, $k) => $v !== '' && !($k === 'sort' && $v === 'newest'), ARRAY_FILTER_USE_BOTH);
$chipNames = ['q' => 'Search', 'level' => 'Level', 'score' => 'Score', 'category' => 'Category', 'sort' => 'Sort', 'type' => 'Type', 'media_type' => 'Media'];
$scoreBadge = fn (int $h) => $h >= 7 ? 'bg-red-900 text-red-200' : ($h >= 4 ? 'bg-amber-900 text-amber-200' : 'bg-green-900 text-green-200');
$hidden = !pretty_urls() && !defined('STATIC_EXPORT');
?>
<main class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mb-8">
    <p class="kicker mb-2">Records &mdash; public</p>
    <h1 class="text-3xl sm:text-4xl font-bold page-title">Explore</h1>
    <p class="text-gray-400 mt-2 max-w-2xl">Search every published article, approved note and media record. The address bar keeps your search so you can bookmark or share it.</p>
  </header>
  <form id="archive-form" action="<?= url('explore') ?>" method="GET" class="archive-filters" role="search" data-autosubmit>
    <?php if ($hidden): ?><input type="hidden" name="page" value="explore"><?php endif; ?>
    <div class="flex flex-col md:flex-row gap-3 mb-4">
      <div class="flex-1 relative">
        <label for="archive-q" class="sr-only">Search terms</label>
        <input type="search" id="archive-q" name="q" class="form-input !pl-10" maxlength="100" placeholder="Search names, codes, article text…" value="<?= e($q) ?>">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none"><?= nav_icon('search') ?></span>
      </div>
      <button type="submit" class="form-button md:w-auto">Search</button>
    </div>
    <fieldset class="segmented mb-4"><legend class="sr-only">Section</legend>
      <?php foreach (['articles' => 'Articles', 'notes' => 'Notes', 'media' => 'Media'] as $val => $label): ?>
        <label><input type="radio" name="tab" value="<?= $val ?>" <?= $tab === $val ? 'checked' : '' ?>><span><?= $label ?></span></label>
      <?php endforeach; ?>
    </fieldset>
    <?php if ($tab === 'articles'): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div><label for="f-level" class="filter-label">Level</label><select id="f-level" name="level" class="form-input"><option value="">Any level</option>
        <?php foreach (['I', 'II', 'III', 'IV', 'HYPER'] as $t): ?><option value="<?= $t ?>" <?= $f['level'] === $t ? 'selected' : '' ?>><?= $t === 'HYPER' ? 'Speculative (HYPER)' : 'Level ' . $t ?></option><?php endforeach; ?></select></div>
      <div><label for="f-score" class="filter-label">Score</label><select id="f-score" name="score" class="form-input"><option value="">Any score</option>
        <?php foreach (['low' => 'Grounded (1–3)', 'mid' => 'Elevated (4–6)', 'high' => 'Speculative (7–10)'] as $k => $v): ?><option value="<?= $k ?>" <?= $f['score'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
      <div><label for="f-category" class="filter-label">Category</label><select id="f-category" name="category" class="form-input"><option value="">All categories</option>
        <?php foreach (rows('categories') as $c): ?><option value="<?= $c['id'] ?>" <?= $f['category'] === (string) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
      <div><label for="f-sort" class="filter-label">Sort by</label><select id="f-sort" name="sort" class="form-input">
        <?php foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'name' => 'Name A–Z', 'score' => 'Highest score', 'popular' => 'Most viewed'] as $k => $v): ?><option value="<?= $k ?>" <?= $f['sort'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
    </div>
    <?php elseif ($tab === 'notes'): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3"><div><label for="f-type" class="filter-label">Type</label><select id="f-type" name="type" class="form-input"><option value="">All types</option>
      <?php foreach (['NOTE' => 'Note', 'IDEA' => 'Idea', 'FIELD_NOTE' => 'Field note'] as $k => $v): ?><option value="<?= $k ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div></div>
    <?php else: ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3"><div><label for="f-mtype" class="filter-label">Media type</label><select id="f-mtype" name="media_type" class="form-input"><option value="">All media</option>
      <?php foreach (['IMAGE' => 'Images', 'VIDEO' => 'Videos', 'AUDIO' => 'Audio'] as $k => $v): ?><option value="<?= $k ?>" <?= $f['media_type'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div></div>
    <?php endif; ?>
    <div class="mt-3 text-right"><a href="<?= url('explore') ?>" class="text-xs text-gray-400 hover:text-violet-300">Clear all filters</a></div>
  </form>

  <div id="archive-results" class="mt-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
      <p class="text-sm text-gray-400"><strong class="text-white"><?= $total ?></strong> <?= $tab === 'media' ? 'media record' . ($total === 1 ? '' : 's') : rtrim($tab, 's') . ($total === 1 ? '' : 's') ?> found<?php if ($pages > 1): ?> &middot; page <?= $pg ?> of <?= $pages ?><?php endif; ?></p>
      <div class="flex flex-wrap gap-2">
        <?php foreach ($keep as $k => $v): if ($k === 'tab') continue; $without = $keep; unset($without[$k]);
              $label = $k === 'category' ? category_name((int) $v) : str_replace('_', ' ', $v); ?>
          <a href="<?= url('explore', $without) ?>" class="filter-chip" aria-label="Remove filter <?= e($chipNames[$k]) ?>: <?= e($label) ?>"><?= e($chipNames[$k]) ?>: <strong><?= e($label) ?></strong> <span aria-hidden="true">&times;</span></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php if (!$rowsShown): ?>
      <div class="empty-state"><div class="es-icon"><?= nav_icon('search') ?></div><h4>No records match<?= $q !== '' ? ' "' . e($q) . '"' : '' ?></h4>
        <p>Try fewer words, a different spelling, or remove a filter.</p>
        <div class="flex flex-wrap gap-3 justify-center"><a href="<?= url('explore') ?>" class="btn-outline">Browse all articles</a><a href="<?= url('explore', ['tab' => 'notes']) ?>" class="btn-outline">Browse notes</a></div></div>
    <?php elseif ($tab === 'articles'): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($rowsShown as $r): ?>
          <a href="<?= url('article', ['slug' => $r['slug']]) ?>" class="block group"><article class="theory-card h-full flex flex-col">
            <div class="flex flex-wrap gap-2 text-xs mb-3"><span class="ref-code"><?= e($r['code']) ?></span><span class="px-2 py-0.5 rounded bg-indigo-900 text-indigo-200 font-bold">L-<?= e($r['level']) ?></span><span class="px-2 py-0.5 rounded font-bold <?= $scoreBadge($r['score']) ?>">S<?= $r['score'] ?></span></div>
            <h3 class="font-bold text-white text-lg mb-1 group-hover:text-violet-300 transition"><?= $hl($r['title']) ?></h3>
            <p class="text-sm text-gray-400 flex-1"><?= $hl(short($r['summary'], 150)) ?></p>
            <div class="flex flex-wrap gap-1.5 mt-3"><span class="cat-tag"><?= e(category_name($r['category_id'])) ?></span></div>
            <div class="card-byline"><span class="text-xs text-gray-400"><?= e(user($r['author_id'])['username']) ?></span><span class="text-xs text-gray-500"><?= when($r['created_min'], 'M j, Y') ?> &middot; <?= $r['views'] ?> views</span></div>
          </article></a>
        <?php endforeach; ?>
      </div>
    <?php elseif ($tab === 'notes'): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <?php foreach ($rowsShown as $r): ?>
          <a href="<?= url('note', ['id' => $r['id']]) ?>" class="block group"><article class="theory-card h-full">
            <div class="flex items-center justify-between mb-2"><span class="badge-type <?= ['NOTE' => 'theory', 'IDEA' => 'idea', 'FIELD_NOTE' => 'note'][$r['type']] ?>"><?= str_replace('_', ' ', $r['type']) ?></span><span class="text-xs text-gray-500"><?= when($r['created_min'], 'M j, Y') ?></span></div>
            <h3 class="font-bold text-white mb-2 group-hover:text-violet-300 transition"><?= $hl($r['title']) ?></h3>
            <p class="text-sm text-gray-400"><?= $hl(short($r['body'], 160)) ?></p>
            <div class="card-byline"><span class="text-xs text-gray-400"><?= e(user($r['author_id'])['username']) ?></span></div>
          </article></a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($rowsShown as $r): ?>
          <a href="<?= url('media') ?>#media-<?= $r['id'] ?>" class="block group"><article class="theory-card h-full">
            <div class="flex items-center justify-between mb-2"><span class="badge-type idea"><?= e($r['type']) ?></span><span class="text-xs text-gray-500"><?= when($r['created_min'], 'M j, Y') ?></span></div>
            <h3 class="font-bold text-white mb-1 group-hover:text-violet-300 transition"><?= $hl($r['title']) ?></h3>
            <p class="text-xs text-gray-400"><?= $hl($r['caption']) ?></p>
          </article></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if ($pages > 1): ?>
      <nav class="pager mt-8" aria-label="Pages">
        <?php for ($i = 1; $i <= $pages; $i++): ?><a href="<?= url('explore', $keep + ['p' => $i]) ?>" class="pager-link<?= $i === $pg ? ' is-current' : '' ?>"<?= $i === $pg ? ' aria-current="page"' : '' ?>><?= $i ?></a><?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</main>
