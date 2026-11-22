<?php /* views/mail-outbox.php - Email outbox with status chips. Markup reused from admin.php (mail-outbox view).
   Nothing is ever sent; addresses are pre-masked fake ones. */
defined('MOCK') || exit;
$labels = ['' => 'All', 'QUEUED' => 'Waiting', 'SENT' => 'Sent', 'LOGGED' => 'Not sent (kept here)', 'FAILED' => 'Failed'];
$filter = (string) ($_GET['status'] ?? ''); if (!array_key_exists($filter, $labels)) $filter = '';
$all = rows('email_outbox');
$counts = array_count_values(array_column($all, 'status'));
$list = array_values(array_filter($all, fn ($r) => $filter === '' || $r['status'] === $filter));
?>
<main class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Admin console</p>
  <h1 class="text-3xl font-bold mb-2 page-title">Email outbox</h1>
  <p class="text-gray-400 mb-6">Every email the site sends, such as sign-in codes, security alerts and notifications. In this mock, sending is turned off, so messages are only listed. Email addresses are partly hidden for privacy.</p>
  <div class="flex flex-wrap gap-3 mb-6">
    <?php foreach ($labels as $k => $l): ?><a href="<?= url('mail-outbox', $k ? ['status' => $k] : []) ?>" class="chip <?= $filter === $k ? 'is-active' : '' ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= e($l) ?><?php if ($k): ?> <span class="chip-count"><?= $counts[$k] ?? 0 ?></span><?php endif; ?></a><?php endforeach; ?>
  </div>
  <div class="flex flex-wrap gap-3 mb-8">
    <?= mock_form_open('mail_process') ?><button type="submit" class="form-button" style="width:auto" <?= ($counts['QUEUED'] ?? 0) ? '' : 'disabled' ?>>Send queued now</button></form>
    <?= mock_form_open('mail_test') ?><button type="submit" class="btn-outline">Send a test email to me</button></form>
    <?= mock_form_open('mail_clear', 'data-confirm="Remove delivered emails older than one day from this list?"') ?><button type="submit" class="btn-outline">Clear old delivered emails</button></form>
  </div>
  <?php if (!$list): ?>
    <p class="text-gray-400 text-center py-10 border border-dashed border-slate-700 rounded-xl">No emails here yet.</p>
  <?php else: ?>
  <div class="overflow-x-auto bg-slate-800/80 rounded-xl border border-slate-700 p-4">
    <table class="w-full text-sm row-hover data-table mail-table m-stack">
      <thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2 pr-3">#</th><th class="py-2 pr-3">To</th><th class="py-2 pr-3">Subject</th><th class="py-2 pr-3">From</th><th class="py-2 pr-3">Status</th><th class="py-2 pr-3">When</th></tr></thead>
      <tbody><?php foreach ($list as $r): ?>
        <tr class="border-b border-slate-700/50 align-top">
          <td data-label="#" class="py-2 pr-3 text-gray-500 whitespace-nowrap"><?= $r['id'] ?></td>
          <td data-label="To" class="py-2 pr-3 whitespace-nowrap"><?= e($r['username']) ?><br><small class="text-gray-500"><?= e($r['to']) ?></small></td>
          <td data-label="Subject" class="py-2 pr-3"><?= e($r['subject']) ?></td>
          <td data-label="From" class="py-2 pr-3 text-gray-400 whitespace-nowrap"><?= e($r['department']) ?></td>
          <td data-label="Status" class="py-2 pr-3"><span class="mail-st mail-<?= strtolower($r['status']) ?>"><?= ['LOGGED' => 'NOT SENT', 'QUEUED' => 'WAITING'][$r['status']] ?? $r['status'] ?></span><?php if ($r['error']): ?><br><small class="text-red-300" title="<?= e($r['error']) ?>">Could not be delivered</small><?php endif; ?></td>
          <td data-label="When" class="py-2 pr-3 text-gray-400 whitespace-nowrap"><?= ago($r['created_min']) ?></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table>
  </div>
  <?php endif; ?>
</main>
