<?php /* views/glossary.php - the reference page (real name: Codex). Score levels, ladder classes, categories, levels, terms. */
defined('MOCK') || exit;
$tone = fn (int $i) => $i >= 7 ? 'cx-red' : ($i >= 4 ? 'cx-amber' : 'cx-green');
$scale = [1 => 'Local', 2 => 'Whole space', 3 => 'Bubble', 4 => 'Branch', 5 => 'Membrane', 6 => 'Abstract', 7 => 'Beyond', 8 => 'Far beyond', 9 => 'Near top', 10 => 'Top rung'];
$byScore = [];
foreach (published('articles') as $a) $byScore[$a['score']][] = $a;
$classes = [1 => ['Physical', 'What we can measure'], 2 => ['Plural', 'Many universes'], 3 => ['Dimensional', 'Higher dimensions'],
            4 => ['Containing', 'Holds whole classes'], 5 => ['Outer', 'Fan-made giants'], 6 => ['Terminal', 'The top of the chain']];
$cats = [];
foreach (published('articles') as $a) $cats[$a['category_id']] = ($cats[$a['category_id']] ?? 0) + 1;
?>
<main class="cx2 max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="cx2-hero">
    <span class="cx2-corner tl" aria-hidden="true"></span><span class="cx2-corner br" aria-hidden="true"></span>
    <p class="kicker mb-2">Reference</p>
    <h1 class="text-3xl sm:text-4xl font-bold page-title">Glossary and classification</h1>
    <p class="cx2-lead">Every article carries a score, a place on the ladder, a category, a level and a code. Here is what each one means and how reviewers assign it.</p>
    <dl class="cx2-stats">
      <div><dt>Score levels</dt><dd>10</dd></div><div><dt>Classes</dt><dd><?= count($classes) ?></dd></div>
      <div><dt>Ladder rungs</dt><dd><?= count(rows('ladder')) ?></dd></div><div><dt>Articles on file</dt><dd><?= count(published('articles')) ?></dd></div>
    </dl>
  </header>
  <div class="cx2-layout">
    <nav class="cx2-rail" aria-label="On this page" data-cx-rail>
      <?php foreach ([['score', 'Score levels'], ['ladder', 'Ladder and classes'], ['categories', 'Categories'], ['levels', 'Levels I-IV'], ['terms', 'Terms'], ['codes', 'Codes']] as $n => [$id, $label]): ?>
        <a href="#<?= $id ?>"><span><?= str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) ?></span><?= $label ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="cx2-body">
      <section id="score" class="cx2-panel reveal scroll-mt-20" aria-labelledby="score-h">
        <p class="cx2-idx">01</p><h2 id="score-h">Score levels</h2>
        <p>The score (1 to 10) shows how far a structure is from our own space and how far it departs from known physics.
          <strong>Score follows containment:</strong> a structure never scores lower than anything it contains.</p>
        <div class="cx2-meter" aria-hidden="true"><?php for ($i = 1; $i <= 10; $i++): ?><span class="<?= $tone($i) ?>"><?= $i ?></span><?php endfor; ?></div>
        <div class="cx2-legend"><span class="cx-lvl cx-green">1-3</span> grounded <span class="cx-lvl cx-amber">4-6</span> elevated <span class="cx-lvl cx-red">7-10</span> speculative</div>
        <ol class="cx2-hz">
          <?php foreach ($scale as $lvl => $label): ?>
            <li class="cx2-hz-row tone-<?= $tone($lvl) ?>"><span class="cx2-hz-num"><?= $lvl ?></span>
              <div class="cx2-hz-main"><strong><?= e($label) ?></strong><p>Mock description for score <?= $lvl ?>.</p></div>
              <div class="cx2-hz-docs"><?php foreach ($byScore[$lvl] ?? [] as $d): ?><a href="<?= url('article', ['slug' => $d['slug']]) ?>" class="cx-pill"><?= e($d['title']) ?></a><?php endforeach; ?></div></li>
          <?php endforeach; ?>
        </ol>
      </section>

      <section id="ladder" class="cx2-panel reveal scroll-mt-20" aria-labelledby="hier-h">
        <p class="cx2-idx">02</p><h2 id="hier-h">Ladder and classes</h2>
        <p>The ladder is a containment chain where each rung holds the one before it. The chain is split into <strong>classes</strong>; the last rung of a class is its <strong>terminator</strong>.</p>
        <div class="cx2-classes">
          <?php $ci = 0; foreach ($classes as $num => [$name, $theme]): $rungs = array_filter(rows('ladder'), fn ($r) => $r['cls'] === $num); ?>
            <details class="cx2-class" <?= $ci++ < 2 ? 'open' : '' ?>>
              <summary><span class="cx2-class-num"><?= $num ?></span><span class="cx2-class-name"><strong><?= e($name) ?></strong><small><?= e($theme) ?></small></span><span class="cx2-class-meta"><?= count($rungs) ?> rung<?= count($rungs) === 1 ? '' : 's' ?></span></summary>
              <ol class="cc-rungs">
                <?php $last = array_key_last($rungs); foreach ($rungs as $k => $r): ?>
                  <li class="<?= $k === $last ? 'is-term' : '' ?>"><span class="cr-n"><?= $r['rung'] ?></span><a href="<?= url('article', ['slug' => $r['slug']]) ?>"><?= e($r['name']) ?></a><small>score <?= $r['score'] ?><?= $k === $last ? ' &middot; terminator' : '' ?></small></li>
                <?php endforeach; ?>
              </ol>
            </details>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="categories" class="cx2-panel reveal scroll-mt-20" aria-labelledby="cat-h">
        <p class="cx2-idx">03</p><h2 id="cat-h">Categories</h2>
        <p><strong>Physics</strong>, <strong>mathematics</strong> and <strong>philosophy</strong> articles describe real ideas. <strong>Speculative</strong> and <strong>fan cosmology</strong> articles are worldbuilding and are always labelled so.</p>
        <div class="cx2-cards">
          <?php foreach (rows('categories') as $c): $sci = in_array($c['id'], [1, 4, 5], true); ?>
            <a class="cx2-card <?= $sci ? 'is-science' : 'is-theory' ?>" href="<?= url('explore', ['category' => $c['id']]) ?>"><span class="cx2-card-tag"><?= $sci ? 'Science' : 'Structure' ?></span><strong><?= e($c['name']) ?></strong><span>Mock description of the <?= e(strtolower($c['name'])) ?> category.</span><small><?= $cats[$c['id']] ?? 0 ?> article<?= ($cats[$c['id']] ?? 0) === 1 ? '' : 's' ?> &rarr;</small></a>
          <?php endforeach; ?>
        </div>
      </section>

      <section id="levels" class="cx2-panel reveal scroll-mt-20" aria-labelledby="teg-h">
        <p class="cx2-idx">04</p><h2 id="teg-h">Levels I-IV</h2>
        <p>The physics side of the ladder uses four levels, plus one label for everything outside them.</p>
        <ol class="cx2-teg">
          <li><b>I</b><strong>Beyond the horizon</strong><span>Regions past our horizon, same laws of physics.</span></li>
          <li><b>II</b><strong>Bubble universes</strong><span>Other bubbles, with different constants.</span></li>
          <li><b>III</b><strong>Branching worlds</strong><span>The branches of quantum theory.</span></li>
          <li><b>IV</b><strong>Mathematical</strong><span>Every consistent mathematical structure (a philosophical idea).</span></li>
          <li class="is-hyper"><b>&#8734;</b><strong>HYPER</strong><span>The label for speculative structures outside the four levels.</span></li>
        </ol>
      </section>

      <section id="terms" class="cx2-panel reveal scroll-mt-20" aria-labelledby="terms-h">
        <p class="cx2-idx">05</p><h2 id="terms-h">Terms</h2>
        <label for="term-filter" class="sr-only">Filter terms</label>
        <input type="search" id="term-filter" class="form-input mb-4" placeholder="Filter terms…" data-mock-filter="#terms tbody tr">
        <div class="table-wrap"><table class="glossary-table m-stack w-full text-sm">
          <thead><tr><th scope="col">Term</th><th scope="col">Meaning</th></tr></thead>
          <tbody><?php foreach (rows('glossary') as $g): ?><tr><td data-label="Term"><strong><?= e($g['term']) ?></strong></td><td data-label="Meaning"><?= e($g['def']) ?></td></tr><?php endforeach; ?></tbody>
        </table></div>
      </section>

      <section id="codes" class="cx2-panel reveal scroll-mt-20" aria-labelledby="des-h">
        <p class="cx2-idx">06</p><h2 id="des-h">Codes</h2>
        <dl class="cx2-codes">
          <div><dt>L1/OR-01</dt><dd>Article code: level, initials and record number.</dd></div>
          <div><dt>H/SJ-10</dt><dd>H marks a speculative (HYPER) article.</dd></div>
          <div><dt>GOV-2026-001</dt><dd>Governance case number.</dd></div>
          <div><dt>IC-MSG-0001</dt><dd>Support message reference, sent back to the person who wrote in.</dd></div>
        </dl>
      </section>
    </div>
  </div>
</main>
