<?php /* views/media-submit.php - share an image, video link or audio link. Markup reused from workspace.php media-submit.
   Mock: the form has no enctype, so a chosen file is only previewed in the browser and never uploaded. */
defined('MOCK') || exit;
$old += ['media_type' => 'IMAGE', 'external_url' => '', 'title' => '', 'caption' => ''];
$types = ['IMAGE' => ['image', 'Image', 'Upload a JPG, PNG, WEBP or GIF, or link to one.'], 'VIDEO' => ['video', 'Video', 'Paste a video link; the real site embedded it.'], 'AUDIO' => ['audio', 'Audio', 'Link to audio hosted elsewhere.']];
$bypass = at_least('admin');
?>
<main class="ms-page max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="ms-head">
    <p class="kicker mb-2">Open to every Member</p>
    <h1 class="text-3xl font-bold page-title">Share media</h1>
    <p class="text-gray-400 text-sm mt-3">Share an image, a video or audio. A Reviewer checks every upload before it appears on the public <a href="<?= url('media') ?>#community-uploads" class="text-indigo-400 hover:underline">Media page</a>.</p>
  </header>
  <div class="ms-grid">
    <?= mock_form_open('media-submit', 'novalidate class="ms-card" data-validate data-media-form') ?>
      <?php if ($errors): ?><div class="form-status error" role="alert">Please fix <?= count($errors) ?> field<?= count($errors) === 1 ? '' : 's' ?> below.</div><?php endif; ?>
      <fieldset class="ms-step">
        <legend><span class="ms-num">1</span> What are you sharing?</legend>
        <div class="ms-types" role="radiogroup" aria-label="Media type">
          <?php foreach ($types as $val => [$ico, $lab, $hint]): ?><label class="ms-type"><input type="radio" name="media_type" value="<?= $val ?>" <?= $old['media_type'] === $val ? 'checked' : '' ?>><span class="ms-type-box"><span class="ms-type-ico"><?= nav_icon($ico) ?></span><strong><?= $lab ?></strong><small><?= $hint ?></small></span></label><?php endforeach; ?>
        </div>
      </fieldset>
      <fieldset class="ms-step">
        <legend><span class="ms-num">2</span> Add the file or link</legend>
        <div class="form-input-group ms-file-wrap" data-media-only="IMAGE">
          <label for="media_file" class="ms-drop" data-dropzone>
            <input type="file" id="media_file" accept=".jpg,.jpeg,.png,.webp,.gif" class="sr-only" data-file-preview="#media-preview">
            <span class="ms-drop-ico"><?= nav_icon('upload') ?></span>
            <span class="ms-drop-main"><strong>Drop an image here</strong> or <u>browse your files</u></span>
            <span class="ms-drop-sub">JPG, PNG, WEBP or GIF &middot; up to 3 MB &middot; preview only, never uploaded</span>
            <figure id="media-preview" class="file-preview ms-preview" hidden><img alt="Selected image preview"><figcaption class="text-xs text-gray-400 mt-1"></figcaption></figure>
          </label>
        </div>
        <p class="ms-or" data-media-only="IMAGE"><span>or paste a link</span></p>
        <div class="form-input-group">
          <label for="external_url" class="block text-sm font-medium text-gray-300 mb-1">External link</label>
          <input type="url" id="external_url" name="external_url" data-label="External link" class="form-input<?= invalid_class($errors, 'external_url') ?>" placeholder="https://example.com/video" value="<?= e($old['external_url']) ?>"<?= invalid_attr($errors, 'external_url', 'external_url') ?>>
          <p class="text-xs text-gray-500 mt-1">Give a file or a link, not both.</p>
          <?= field_error($errors, 'external_url', 'external_url') ?>
        </div>
      </fieldset>
      <fieldset class="ms-step">
        <legend><span class="ms-num">3</span> Describe it</legend>
        <div class="form-input-group">
          <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Title *</label>
          <input type="text" id="title" name="title" class="form-input<?= invalid_class($errors, 'title') ?>" required minlength="3" maxlength="200" data-label="Title" placeholder="e.g. Scale diagram, second pass" value="<?= e($old['title']) ?>"<?= invalid_attr($errors, 'title', 'title') ?>>
          <?= field_error($errors, 'title', 'title') ?>
        </div>
        <div class="form-input-group">
          <label for="caption" class="block text-sm font-medium text-gray-300 mb-1">Short description <span class="text-gray-500">(shown as the caption)</span></label>
          <textarea id="caption" name="caption" rows="3" class="form-input" maxlength="300" data-counter placeholder="What does it show and where does it fit?"><?= e($old['caption']) ?></textarea>
        </div>
      </fieldset>
      <button type="submit" class="form-button w-full"><?= $bypass ? 'Publish now' : 'Submit for review' ?></button>
    </form>
    <aside class="ms-side">
      <section class="ms-panel"><h2>What happens next</h2><ol class="ms-flow">
        <li><strong>Pending</strong><span>Your upload waits in the review queue. You can see it under <a href="<?= url('dashboard') ?>#my-media">My media</a>.</span></li>
        <li><strong>Review</strong><span>A Reviewer checks it against the guidelines below.</span></li>
        <li><strong>Published</strong><span>It appears on the Media page and you earn 10 credits.</span></li></ol></section>
      <section class="ms-panel"><h2>Guidelines</h2><ul class="ms-rules">
        <li>Only share work you made or are allowed to share.</li><li>Give it a clear title, so others can find it in search.</li>
        <li>Use the caption to say what it shows.</li><li>No personal data, spam or offensive material.</li></ul></section>
    </aside>
  </div>
</main>
