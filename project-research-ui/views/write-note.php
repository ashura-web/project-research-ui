<?php /* views/write-note.php - post a short note (real name: theory submission). Markup reused from workspace.php. */
defined('MOCK') || exit;
$TYPES = ['NOTE' => 'Note: a claim with reasoning', 'IDEA' => 'Idea: a proposal for the site or the archive', 'FIELD_NOTE' => 'Field note: an observation or reading tip'];
$old += ['title' => '', 'type' => 'NOTE', 'body' => ''];
$bypass = at_least('admin');
?>
<main class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="text-center mb-8">
    <p class="kicker mb-2">Open to every Member</p>
    <h1 class="text-3xl font-bold page-title">Write a community note</h1>
    <p class="text-gray-400 text-sm mt-3">Share a working idea, a proposal or a field note. A Reviewer checks every note before it is published in the
      <a href="<?= url('gallery') ?>#community" class="text-indigo-400 hover:underline">community notes</a><?= $bypass ? ' &mdash; <strong class="text-amber-300">except for you:</strong> as an Admin, your notes publish straight away.' : '.' ?></p>
  </header>
  <div class="bg-slate-800/80 p-8 rounded-xl shadow-2xl border border-slate-700">
    <?php if ($errors): ?><div class="form-status error" role="alert">Please fix <?= count($errors) ?> field<?= count($errors) === 1 ? '' : 's' ?> below.</div><?php endif; ?>
    <?= mock_form_open('write-note', 'novalidate class="space-y-4" data-validate data-draft="note-new"') ?>
      <div class="form-input-group">
        <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Title *</label>
        <input type="text" id="title" name="title" class="form-input<?= invalid_class($errors, 'title') ?>" required minlength="5" maxlength="150" data-counter data-label="Title" placeholder="e.g. Why bubbles never touch" value="<?= e($old['title']) ?>"<?= invalid_attr($errors, 'title', 'title') ?>>
        <?= field_error($errors, 'title', 'title') ?>
      </div>
      <fieldset class="form-input-group">
        <legend class="block text-sm font-medium text-gray-300 mb-2">Type *</legend>
        <div class="segmented segmented-stack">
          <?php foreach ($TYPES as $k => $label): ?><label class="segmented-option"><input type="radio" name="type" value="<?= $k ?>" <?= $old['type'] === $k ? 'checked' : '' ?>><span><?= e($label) ?></span></label><?php endforeach; ?>
        </div>
        <?= field_error($errors, 'type', 'type') ?>
      </fieldset>
      <div class="form-input-group">
        <label for="body" class="block text-sm font-medium text-gray-300 mb-1">Full write-up * <span class="text-gray-500">(min. 50 characters)</span></label>
        <textarea id="body" name="body" rows="10" class="form-input<?= invalid_class($errors, 'body') ?>" required minlength="50" maxlength="10000" data-counter data-label="The write-up" placeholder="State your claim, the reasoning behind it, and what would support or refute it."<?= invalid_attr($errors, 'body', 'body') ?>><?= e($old['body']) ?></textarea>
        <p class="text-xs text-gray-500 mt-1">Line breaks are kept. Your draft is saved on this device as you type <span data-draft-status></span></p>
        <?= field_error($errors, 'body', 'body') ?>
      </div>
      <div class="pt-2"><button type="submit" class="form-button w-full"><?= $bypass ? 'Publish now' : 'Send for review' ?></button></div>
    </form>
  </div>
</main>
