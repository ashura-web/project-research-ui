<?php /* views/gallery.php - ladder carousel, featured levels, article cards with filter, notes, top contributors. */
defined('MOCK') || exit;
$arts = published('articles');
usort($arts, fn ($a, $b) => $a['score'] <=> $b['score']);
$group = fn ($a) => in_array($a['category_id'], [1, 4, 5], true) ? 'science' : 'speculative';
$notes = published('notes');
$board = [];
foreach (rows('users') as $u) {
    $c = ['u' => $u, 'notes' => 0, 'media' => 0, 'articles' => 0, 'earned' => 0];
    foreach ($arts as $a) if ($a['author_id'] === $u['id']) $c['articles']++;
    foreach ($notes as $n) if ($n['author_id'] === $u['id']) $c['notes']++;
    foreach (published('media') as $m) if ($m['author_id'] === $u['id']) $c['media']++;
    $c['earned'] = $c['articles'] * 50 + $c['notes'] * 15 + $c['media'] * 10;
    if ($c['earned']) $board[] = $c;
}
usort($board, fn ($a, $b) => $b['earned'] <=> $a['earned']);
$board = array_slice($board, 0, 5);
$top = max(1, $board[0]['earned'] ?? 1);
?>
<main class="gallery-page max-w-7xl mx-auto pt-16 pb-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="g-hero">
    <p class="kicker mb-2">Visual records</p>
    <h1 class="page-title text-3xl sm:text-5xl font-bold">The Gallery</h1>
    <p class="text-gray-400 mt-3 max-w-2xl">Every published article in one place, from the smallest rung to the top of the ladder. Covers here are
      generated colour cards; the real site used credited images.</p>
    <nav class="g-jump" aria-label="Gallery sections">
      <a href="#ladder" class="chip">Ladder</a><a href="#levels" class="chip">The four levels</a>
      <a href="#all-articles" class="chip">All articles <span class="text-gray-500"><?= count($arts) ?></span></a>
      <a href="#community" class="chip">Notes</a><a href="#contributors" class="chip">Contributors</a>
    </nav>
  </header>

  <section id="ladder" class="g-section scroll-mt-20" aria-labelledby="ladder-h">
    <div class="g-section-head"><div>
      <h2 id="ladder-h" class="g-h2">The ladder</h2>
      <p class="g-sub">Each rung contains the one before it. The carousel climbs one rung every 5 seconds; click a card to open its article.</p>
    </div></div>
    <div class="vcar" data-vcar data-interval="5000" role="region" aria-roledescription="carousel" aria-label="The ladder, smallest to largest">
      <div class="vcar-stage">
        <ol class="vcar-track">
          <?php foreach (rows('ladder') as $i => $r): ?>
            <li class="vcar-slide" data-class="<?= $r['cls'] ?>" data-name="<?= e($r['name']) ?>" data-meta="Class <?= $r['cls'] ?> &middot; Score <?= $r['score'] ?>/10" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count(rows('ladder')) ?>: <?= e($r['name']) ?>">
              <a href="<?= url('article', ['slug' => $r['slug']]) ?>" class="vcar-link" aria-label="Open article: <?= e($r['name']) ?>"><?= cover($r['name'], crc32($r['slug']) % 360, 'vcar-cover') ?></a>
            </li>
          <?php endforeach; ?>
        </ol>
        <button type="button" class="vcar-btn is-prev" data-vcar-prev aria-label="Previous rung" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg></button>
        <button type="button" class="vcar-btn is-next" data-vcar-next aria-label="Next rung" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg></button>
      </div>
      <div class="vcar-bar" hidden>
        <p class="vcar-count" aria-live="polite"><b data-vcar-num>01</b> / <?= str_pad((string) count(rows('ladder')), 2, '0', STR_PAD_LEFT) ?> <span data-vcar-name></span></p>
        <div class="vcar-ticks" data-vcar-ticks aria-label="Jump to a rung"></div>
        <button type="button" class="vcar-pause" data-vcar-pause aria-pressed="false" aria-label="Pause auto-advance"><svg viewBox="0 0 24 24" aria-hidden="true"><path class="i-pause" d="M8 5v14M16 5v14"/><path class="i-play" d="M7 5l12 7-12 7z"/></svg></button>
      </div>
    </div>
  </section>

  <section id="levels" class="g-section scroll-mt-20" aria-labelledby="teg-h">
    <div class="g-section-head"><div>
      <h2 id="teg-h" class="g-h2">Featured: the four levels</h2>
      <p class="g-sub">The physics side of the ladder, split into four levels. Levels I to III come from physics; Level IV is a philosophical proposal.</p>
    </div></div>
    <div class="teg-grid">
      <?php foreach ([['I', 'Beyond our horizon', 'Distant regions of the same space, with the same laws.', 210], ['II', 'Inflation bubbles', 'Pockets that stopped inflating, possibly with other constants.', 250],
                      ['III', 'Branching worlds', 'Every outcome of a quantum event on its own branch.', 290], ['IV', 'Mathematical ensemble', 'Every consistent mathematical structure exists.', 330]] as [$lvl, $t, $txt, $hue]): ?>
        <figure class="teg-card"><?= cover('Level ' . $lvl, $hue, 'teg-cover') ?><figcaption><span class="teg-level">Level <?= $lvl ?></span><strong><?= e($t) ?></strong><span><?= e($txt) ?></span></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="all-articles" class="g-section scroll-mt-20" aria-labelledby="all-h">
    <div class="g-section-head">
      <div><h2 id="all-h" class="g-h2">All articles</h2><p class="g-sub">Ordered from the lowest score to the highest.</p></div>
      <div class="g-filter" role="group" aria-label="Filter articles" data-gallery-filter>
        <button type="button" class="seg-btn is-active" data-filter="all" aria-pressed="true">All</button>
        <button type="button" class="seg-btn" data-filter="science" aria-pressed="false">Physics</button>
        <button type="button" class="seg-btn" data-filter="speculative" aria-pressed="false">Speculative</button>
      </div>
    </div>
    <div class="g-grid" data-gallery-grid>
      <?php foreach ($arts as $d): $h = $d['score']; ?>
        <article class="g-card" data-group="<?= $group($d) ?>">
          <a href="<?= url('article', ['slug' => $d['slug']]) ?>" class="g-card-link" aria-label="Open article: <?= e($d['title']) ?>">
            <?= cover($d['title'], crc32($d['slug']) % 360) ?>
            <span class="g-badges"><span class="g-badge">Level <?= e($d['level']) ?></span><span class="g-badge <?= $h >= 7 ? 'is-high' : ($h >= 4 ? 'is-mid' : 'is-low') ?>">Score <?= $h ?></span></span>
          </a>
          <div class="g-body">
            <h3 class="g-title"><a href="<?= url('article', ['slug' => $d['slug']]) ?>"><?= e($d['title']) ?></a></h3>
            <p class="g-text"><?= e(short($d['summary'], 140)) ?></p>
            <div class="g-foot"><?= member_chip(user($d['author_id']), '', 'xs') ?></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="g-empty-filter hidden text-sm text-gray-500 text-center py-8" data-gallery-empty>No articles in this group yet.</p>
  </section>

  <section id="community" class="g-section scroll-mt-20" aria-labelledby="com-h">
    <div class="g-section-head">
      <div><h2 id="com-h" class="g-h2">Community notes</h2><p class="g-sub">Notes, ideas and field notes from members. Each one is reviewed before it appears here.</p></div>
      <a href="<?= url(signed_in() ? 'write-note' : 'login') ?>" class="btn-outline"><?= signed_in() ? 'Write a note' : 'Log in to contribute' ?></a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($notes as $t): $tc = ['NOTE' => 'theory', 'IDEA' => 'idea', 'FIELD_NOTE' => 'note'][$t['type']]; $tl = ['NOTE' => 'Note', 'IDEA' => 'Idea', 'FIELD_NOTE' => 'Field note'][$t['type']]; ?>
        <article class="theory-card flex flex-col">
          <div class="flex items-center justify-between mb-2"><span class="badge-type <?= $tc ?>"><?= $tl ?></span><span class="text-xs text-gray-500"><?= when($t['created_min'], 'M j, Y') ?></span></div>
          <h3 class="font-bold text-white mb-2"><a href="<?= url('note', ['id' => $t['id']]) ?>" class="hover:text-violet-300"><?= e($t['title']) ?></a></h3>
          <p class="text-sm text-gray-400 flex-1"><?= e(short($t['body'], 150)) ?></p>
          <div class="card-byline"><?= member_chip(user($t['author_id']), '', 'xs') ?></div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="contributors" class="g-section scroll-mt-20" aria-labelledby="con-h">
    <div class="g-section-head">
      <div><h2 id="con-h" class="g-h2">Top contributors</h2><p class="g-sub">Approved work per member. Credits: 15 per note, 10 per media item, 50 per article.</p></div>
      <a href="<?= url('about') ?>#roster" class="text-xs text-indigo-300 hover:underline">Full roster</a>
    </div>
    <ol class="contrib-board">
      <?php foreach ($board as $i => $c): ?>
        <li><span class="cb-rank"><?= $i + 1 ?></span><span class="cb-who"><?= member_chip($c['u']) ?></span>
          <span class="cb-stats"><span><b><?= $c['notes'] ?></b> notes</span><span><b><?= $c['media'] ?></b> media</span><span><b><?= $c['articles'] ?></b> articles</span></span>
          <span class="cb-credits"><span class="cb-bar" aria-hidden="true"><span style="width:<?= round($c['earned'] / $top * 100) ?>%"></span></span><b><?= $c['earned'] ?></b> credits earned from approvals</span></li>
      <?php endforeach; ?>
    </ol>
  </section>
</main>
