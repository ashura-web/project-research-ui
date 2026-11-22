<?php /* views/bulletins.php - the Reviewer's bulletin desk: read, filter and publish notices. Markup reused from workspace.php. */
defined('MOCK') || exit;
$bulletins = array_values(array_filter(rows('bulletins'), fn ($b) => at_least($b['min_role'])));
usort($bulletins, fn ($a, $b) => [$b['pinned'], -$b['created_min']] <=> [$a['pinned'], -$a['created_min']]);
$unread = count(array_filter($bulletins, fn ($b) => $b['unread']));
$canPost = at_least('reviewer');
$old += ['title' => '', 'body' => '', 'priority' => 'INFO', 'min_role' => 'member'];
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mb-6">
    <p class="kicker mb-1">Announcements</p>
    <h1 class="text-3xl font-bold page-title">Bulletin desk</h1>
    <p class="text-gray-400 mt-2">Publish and manage bulletins. Members read them in the <a href="<?= url('notifications', ['type' => 'board']) ?>" class="text-amber-300 hover:text-amber-200 underline decoration-dotted">Notification centre</a>.</p>
  </header>
  <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="segmented-btns" role="group" aria-label="Filter bulletins">
      <button type="button" class="is-active" data-bulletin-filter="all" aria-pressed="true">All <span class="text-gray-500">(<?= count($bulletins) ?>)</span></button>
      <button type="button" data-bulletin-filter="unread" aria-pressed="false">Unread <span class="count-pill"><?= $unread ?></span></button>
    </div>
    <?= mock_form_open('read_all') ?><button type="submit" class="btn-outline !py-1.5 text-sm" <?= $unread ? '' : 'disabled' ?>>&#10003;&#xFE0E; Mark all as read</button></form>
  </div>
  <?php require __DIR__ . '/_bulletin-list.php'; ?>
  <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mt-10" aria-labelledby="compose-title">
    <h2 id="compose-title" class="text-xl font-bold text-white mb-1">Publish a bulletin</h2>
    <p class="text-sm text-gray-400 mb-5">Reviewer privilege. Members below the chosen audience will not see it.</p>
    <?php if ($errors): ?><div class="form-status error">Please fix the highlighted fields.</div><?php endif; ?>
    <?= mock_form_open('bulletin', 'data-validate data-draft="bulletin" novalidate class="space-y-4"') ?>
      <div class="form-input-group"><label for="b-title" class="block text-sm font-medium text-gray-300 mb-1">Title *</label><input type="text" id="b-title" name="title" class="form-input<?= invalid_class($errors, 'title') ?>"<?= invalid_attr($errors, 'title', 'b-title') ?> required minlength="5" maxlength="150" data-counter data-label="Title" value="<?= e($old['title']) ?>"><?= field_error($errors, 'title', 'b-title') ?></div>
      <div class="form-input-group"><label for="b-body" class="block text-sm font-medium text-gray-300 mb-1">Message *</label><textarea id="b-body" name="body" rows="5" class="form-input<?= invalid_class($errors, 'body') ?>"<?= invalid_attr($errors, 'body', 'b-body') ?> required minlength="20" maxlength="5000" data-counter data-label="Message"><?= e($old['body']) ?></textarea><?= field_error($errors, 'body', 'b-body') ?></div>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="form-input-group !mb-0"><label for="b-priority" class="block text-sm font-medium text-gray-300 mb-1">Priority</label><select id="b-priority" name="priority" class="form-input"><?php foreach (['INFO' => 'Info', 'IMPORTANT' => 'Important', 'URGENT' => 'Urgent'] as $k => $v): ?><option value="<?= $k ?>" <?= $old['priority'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="form-input-group !mb-0"><label for="b-level" class="block text-sm font-medium text-gray-300 mb-1">Audience</label><select id="b-level" name="min_role" class="form-input"><?php foreach (['member' => 'All members', 'contributor' => 'Contributor +', 'reviewer' => 'Reviewer +', 'admin' => 'Admins only'] as $k => $v): ?><option value="<?= $k ?>" <?= $old['min_role'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <label class="flex items-center gap-2 text-sm text-gray-300 sm:mt-7"><input type="checkbox" name="pinned" value="1" class="accent-violet-500 w-4 h-4"> Pin to top</label>
      </div>
      <div class="flex items-center justify-between gap-3 pt-2"><small class="text-xs text-gray-500" data-draft-status></small><button type="submit" class="form-button">Publish bulletin</button></div>
    </form>
  </section>
</main>
