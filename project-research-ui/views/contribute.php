<?php /* views/contribute.php - 4-step article editor with a live card preview. Markup reused from workspace.php (contribute).
   ?id=14 opens the "returned" sample article in edit mode. */
defined('MOCK') || exit;
$editing = isset($_GET['id']) ? find('articles', (int) $_GET['id']) : null;
$src = $editing ?: ['title' => '', 'code' => '', 'level' => '', 'score' => 3, 'cls' => '', 'summary' => '', 'body' => '', 'category_id' => 0];
$old += ['title' => $src['title'], 'code' => $src['code'], 'level' => $src['level'], 'score' => $src['score'], 'cls' => (string) $src['cls'], 'summary' => $src['summary'], 'body' => $src['body'], 'source_url' => '', 'source_name' => ''];
$hz = max(1, min(10, (int) $old['score']));
$mine = where('articles', fn ($a) => $a['author_id'] === ($me['id'] ?? 0));
$bypass = at_least('admin');
$steps = ['Identity', 'Classification', 'Article', 'Review'];
$scale = ['1. Local', '2. Single universe', '3. Many universes', '4. Branching', '5. Laws vary', '6. Mathematical', '7. Beyond maths', '8. Recursive', '9. Open-ended', '10. Unbounded'];
?>
<main class="ctb py-8 px-4 sm:px-6 lg:px-8 content-overlay">
  <div class="ctb-bg" aria-hidden="true"><span class="ctb-grid"></span><span class="ctb-orb o1"></span><span class="ctb-orb o2"></span><span class="ctb-scan"></span></div>
  <header class="ctb-hero">
    <p class="ctb-kicker"><span class="ctb-blink" aria-hidden="true"></span> Article editor &middot; Contributor and above</p>
    <h1 class="ctb-title" data-text="<?= $editing ? 'Edit and resubmit' : 'Write a new article' ?>"><?= $editing ? 'Edit and resubmit' : 'Write a new article' ?></h1>
    <p class="ctb-lead">Describe a structure, give it a place on the ladder and send it for review. <?= $bypass ? '<strong class="text-amber-300">Admin fast-path:</strong> your articles publish straight away (and earn no credits).' : 'A Reviewer checks it before it appears in the gallery; accepted articles earn 50 credits.' ?></p>
    <ul class="ctb-stats" aria-label="Your record">
      <li><b><?= count(array_filter($mine, fn ($a) => $a['status'] === 'PUBLISHED')) ?></b><span>published</span></li>
      <li><b><?= count(array_filter($mine, fn ($a) => $a['status'] === 'PENDING')) ?></b><span>in review</span></li>
      <li><b><?= count(rows('ladder')) ?></b><span>rungs mapped</span></li>
    </ul>
  </header>
  <?php if ($editing && !empty($editing['review_note'])): ?><div class="review-note ctb-wide mb-6" role="note"><strong>Reviewer note:</strong><p class="mt-1"><?= e($editing['review_note']) ?></p></div><?php endif; ?>
  <?php if ($errors): ?><div class="form-status error ctb-wide" role="alert">Please fix <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?>.</div><?php endif; ?>

  <div class="ctb-layout">
  <?= mock_form_open('contribute', 'novalidate data-validate data-draft="article-' . ($editing ? (int) $editing['id'] : 'new') . '" class="ctb-console" data-live-form') ?>
    <span class="ctb-corner tl" aria-hidden="true"></span><span class="ctb-corner tr" aria-hidden="true"></span><span class="ctb-corner bl" aria-hidden="true"></span><span class="ctb-corner br" aria-hidden="true"></span>
    <div data-wizard>
      <ol class="stepper ctb-stepper" aria-label="Submission progress">
        <?php foreach ($steps as $i => $label): ?><li class="stepper-item"><button type="button" data-goto-step="<?= $i ?>"><span class="stepper-dot" aria-hidden="true"><?= $i + 1 ?></span><span class="stepper-label"><?= $label ?></span></button></li><?php endforeach; ?>
      </ol>

      <section class="wizard-step" aria-labelledby="st1">
        <h2 id="st1" class="wizard-title"><span class="ctb-step-code">01</span> Identity</h2>
        <div class="form-input-group">
          <label for="title" class="ctb-label">Title *</label>
          <input type="text" id="title" name="title" class="form-input<?= invalid_class($errors, 'title') ?>" required minlength="3" maxlength="150" data-counter data-label="Title" placeholder="e.g. The Folded Realm" value="<?= e($old['title']) ?>"<?= invalid_attr($errors, 'title', 'title') ?>>
          <?= field_error($errors, 'title', 'title') ?>
        </div>
        <div class="form-input-group">
          <label for="code" class="ctb-label">Catalogue code <span class="ctb-opt">optional</span></label>
          <input type="text" id="code" name="code" class="form-input<?= invalid_class($errors, 'code') ?>" maxlength="50" data-pattern="designation" data-label="Code" placeholder="L2/FR-15" value="<?= e($old['code']) ?>"<?= invalid_attr($errors, 'code', 'code') ?>>
          <p class="ctb-help">Letters, numbers, "/", "-" and ".". <a href="<?= url('glossary') ?>#codes" target="_blank">Code formats</a></p>
          <?= field_error($errors, 'code', 'code') ?>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-input-group"><label for="source_url" class="ctb-label">Reference link <span class="ctb-opt">optional</span></label><input type="url" id="source_url" name="source_url" class="form-input" maxlength="300" placeholder="https://example.org/page" value="<?= e($old['source_url']) ?>"></div>
          <div class="form-input-group"><label for="source_name" class="ctb-label">Credit line <span class="ctb-opt">optional</span></label><input type="text" id="source_name" name="source_name" class="form-input" maxlength="120" placeholder='Example Wiki, "Page" (CC BY-SA)' value="<?= e($old['source_name']) ?>"></div>
        </div>
        <p class="ctb-help">The real editor also took a cover image link. The mock draws a generated colour cover instead.</p>
        <div class="wizard-nav"><span></span><button type="button" class="form-button ctb-go" data-next>Next: Classification <span aria-hidden="true">&rarr;</span></button></div>
      </section>

      <section class="wizard-step" aria-labelledby="st2">
        <h2 id="st2" class="wizard-title"><span class="ctb-step-code">02</span> Classification</h2>
        <div class="form-input-group ctb-rung">
          <label for="rung_hint" class="ctb-label">Where does it sit? <span class="ctb-opt">sets a consistent score</span></label>
          <select id="rung_hint" class="form-input" data-score-suggest="#score">
            <option value="">Choose the largest rung it contains...</option>
            <?php foreach (rows('ladder') as $l): ?><option value="<?= $l['score'] ?>">Contains <?= e($l['name']) ?> (class <?= $l['cls'] ?>, score <?= $l['score'] ?>)</option><?php endforeach; ?>
          </select>
          <p class="ctb-help">The score follows containment: a structure is never rated below what it contains.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="form-input-group">
            <label for="level" class="ctb-label">Level</label>
            <select id="level" name="level" class="form-input"><option value="">None / unknown</option><?php foreach (['I', 'II', 'III', 'IV', 'BEYOND'] as $lv): ?><option value="<?= $lv ?>" <?= $old['level'] === $lv ? 'selected' : '' ?>><?= $lv === 'BEYOND' ? 'Beyond the four levels' : 'Level ' . $lv ?></option><?php endforeach; ?></select>
            <a href="<?= url('glossary') ?>#levels" target="_blank" class="ctb-help-link">What do the levels mean?</a>
          </div>
          <div class="form-input-group">
            <label for="score" class="ctb-label">Scale score (1-10) *</label>
            <input type="range" id="score" name="score" class="score-range" min="1" max="10" step="1" required value="<?= $hz ?>" aria-describedby="score-preview-label">
          </div>
        </div>
        <div class="ctb-gauge mb-6" data-score-preview="#score" data-score-scale="<?= e(json_encode($scale)) ?>">
          <div class="flex items-center justify-between text-xs mb-2"><span class="font-bold tracking-widest text-gray-400 uppercase">Live score gauge</span><span class="font-bold text-gray-200" id="score-preview-label" data-score-label><?= $hz ?> / 10</span></div>
          <?= score_bar($hz) ?>
          <ol class="ctb-ticks" aria-hidden="true"><?php for ($i = 1; $i <= 10; $i++): ?><li><?= $i ?></li><?php endfor; ?></ol>
          <p class="text-xs text-gray-300 mt-2" data-score-note aria-live="polite"><?= e($scale[$hz - 1]) ?></p>
        </div>
        <div class="form-input-group">
          <label for="cls" class="ctb-label">Ladder class <span class="ctb-opt">optional</span></label>
          <select id="cls" name="cls" class="form-input"><option value="">Not on the ladder</option><?php for ($c = 1; $c <= 6; $c++): ?><option value="<?= $c ?>" <?= $old['cls'] === (string) $c ? 'selected' : '' ?>>Class <?= $c ?></option><?php endfor; ?></select>
        </div>
        <fieldset class="form-input-group">
          <legend class="ctb-label mb-2">Categories <span class="ctb-opt">choose any</span></legend>
          <div class="flex flex-wrap gap-2"><?php foreach (rows('categories') as $c): ?><label class="chip-check"><input type="checkbox" name="categories[]" value="<?= $c['id'] ?>" data-text="<?= e($c['name']) ?>" <?= (int) $src['category_id'] === $c['id'] ? 'checked' : '' ?>><span><?= e($c['name']) ?></span></label><?php endforeach; ?></div>
        </fieldset>
        <div class="wizard-nav"><button type="button" class="btn-outline" data-prev>&larr; Back</button><button type="button" class="form-button ctb-go" data-next>Next: Article <span aria-hidden="true">&rarr;</span></button></div>
      </section>

      <section class="wizard-step" aria-labelledby="st3">
        <h2 id="st3" class="wizard-title"><span class="ctb-step-code">03</span> Article</h2>
        <div class="form-input-group">
          <label for="summary" class="ctb-label">Summary (card preview) *</label>
          <textarea id="summary" name="summary" rows="3" class="form-input<?= invalid_class($errors, 'summary') ?>" required minlength="20" maxlength="500" data-counter data-label="Summary" placeholder="One or two sentences shown on the gallery card."<?= invalid_attr($errors, 'summary', 'summary') ?>><?= e($old['summary']) ?></textarea>
          <?= field_error($errors, 'summary', 'summary') ?>
        </div>
        <div class="form-input-group">
          <label for="body" class="ctb-label">Full article *</label>
          <textarea id="body" name="body" rows="10" class="form-input<?= invalid_class($errors, 'body') ?>" required minlength="30" maxlength="60000" data-counter data-label="Article" placeholder="Leave a blank line between paragraphs. End with the source and licence."<?= invalid_attr($errors, 'body', 'body') ?>><?= e($old['body']) ?></textarea>
          <?= field_error($errors, 'body', 'body') ?>
        </div>
        <div class="wizard-nav"><button type="button" class="btn-outline" data-prev>&larr; Back</button><button type="button" class="form-button ctb-go" data-next>Review <span aria-hidden="true">&rarr;</span></button></div>
      </section>

      <section class="wizard-step" data-review aria-labelledby="st4">
        <h2 id="st4" class="wizard-title"><span class="ctb-step-code">04</span> Review and send</h2>
        <dl class="review-list">
          <?php foreach ([['Title', 'title', 0], ['Code', 'code', 0], ['Reference', 'source_url', 0], ['Level', 'level', 1], ['Score', 'score', 1], ['Ladder class', 'cls', 1], ['Categories', 'categories[]', 1], ['Summary', 'summary', 2], ['Article', 'body', 2]] as [$l, $f, $s]): ?>
            <div><dt><?= $l ?></dt><dd data-review-for="<?= $f ?>"<?= $f === 'body' ? ' data-review-trim="240"' : '' ?>></dd><button type="button" data-goto-step="<?= $s ?>">Edit</button></div>
          <?php endforeach; ?>
        </dl>
        <?php if ($editing): ?><div class="form-input-group mt-5"><label for="revision_note" class="ctb-label">What changed? <span class="ctb-opt">saved to revision history</span></label><input type="text" id="revision_note" name="revision_note" class="form-input" maxlength="255" placeholder="e.g. Added the source line"></div><?php endif; ?>
        <p class="ctb-help mt-4">By sending you confirm the article is your own writing or properly credited.</p>
        <div class="wizard-nav flex-wrap">
          <button type="button" class="btn-outline" data-prev>&larr; Back</button>
          <span class="flex flex-wrap gap-2"><button type="submit" name="intent" value="draft" class="btn-outline" formnovalidate>Save as draft</button><button type="submit" name="intent" value="submit" class="form-button ctb-go ctb-transmit"><?= $bypass ? 'Publish now (Admin)' : ($editing ? 'Resubmit for review' : 'Send for review') ?></button></span>
        </div>
      </section>
    </div>
    <p class="text-xs text-gray-500 text-right mt-3" data-draft-status aria-live="polite"></p>
  </form>

  <aside class="ctb-side" aria-label="Live preview">
    <p class="ctb-side-h"><span class="ctb-blink" aria-hidden="true"></span> Live card preview</p>
    <article class="ctb-card" data-live-card>
      <div class="ctb-card-img" data-live-img><span class="ctb-card-ph" aria-hidden="true"></span></div>
      <div class="ctb-card-body">
        <p class="ctb-card-meta"><span data-live="code"><?= $old['code'] !== '' ? e($old['code']) : 'NO CODE' ?></span> <span class="ctb-hz" data-live-hz>S<?= $hz ?></span></p>
        <h3 data-live="title"><?= $old['title'] !== '' ? e($old['title']) : 'Untitled article' ?></h3>
        <p data-live="summary" class="ctb-card-sum"><?= $old['summary'] !== '' ? e($old['summary']) : 'Your summary appears here as you type.' ?></p>
        <p class="ctb-card-tags" data-live-tags></p>
      </div>
    </article>
    <section class="ctb-guide" aria-labelledby="guide-h">
      <h3 id="guide-h">Checklist</h3>
      <ul><li>One structure per article, named as in its source.</li><li>Pick the rung it contains; the score follows.</li><li>Mark speculative models as Speculative, tested science as Physics.</li><li>End the article with the source link and licence.</li></ul>
      <a href="<?= url('glossary') ?>" target="_blank" class="ctb-guide-link">Open the glossary &rarr;</a>
    </section>
  </aside>
  </div>
</main>
