<?php /* views/help.php - the help centre / FAQ (real name: Field Manual). Accordion + live filter. */
defined('MOCK') || exit;
$groups = [];
foreach (rows('faq') as $i => $f) $groups[$f['group']][] = ['q-' . ($i + 1), $f['q'], $f['a']];
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="text-center mb-8">
    <p class="kicker mb-2">Help centre</p>
    <h1 class="text-3xl sm:text-4xl font-bold page-title">Frequently asked questions</h1>
    <p class="text-gray-400 text-sm mt-3"><?= count(rows('faq')) ?> answers about how <?= e(APP_NAME) ?> works.</p>
  </header>
  <div class="faq-toolbar mb-6">
    <label for="faq-filter" class="sr-only">Filter answers</label>
    <input type="search" id="faq-filter" class="form-input" placeholder="Filter answers… e.g. credits, review, email" data-faq-filter>
    <button type="button" class="btn-outline whitespace-nowrap" data-expand-all>Expand all</button>
  </div>
  <p class="text-xs text-gray-500 mb-4" data-faq-count aria-live="polite"></p>
  <?php $n = 0; foreach ($groups as $group => $items): ?>
    <section class="faq-group mb-8" aria-labelledby="g-<?= ++$n ?>">
      <h2 id="g-<?= $n ?>" class="text-lg font-bold text-violet-300 mb-3"><?= e($group) ?></h2>
      <div class="accordion">
        <?php foreach ($items as [$id, $q, $a]): ?>
          <div class="accordion-item" id="<?= $id ?>">
            <h3><button type="button" class="accordion-trigger" aria-expanded="false" aria-controls="<?= $id ?>-panel" id="<?= $id ?>-btn"><span><?= e($q) ?></span><span class="accordion-icon" aria-hidden="true"></span></button></h3>
            <div class="accordion-panel" id="<?= $id ?>-panel" role="region" aria-labelledby="<?= $id ?>-btn"><p><?= e($a) ?></p><a href="#<?= $id ?>" class="accordion-permalink">Link to this answer</a></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>
  <p class="text-sm text-gray-400 text-center">Still stuck? <a href="<?= url('contact') ?>" class="text-indigo-300 hover:underline">Send a message to the support team</a>.</p>
</main>
