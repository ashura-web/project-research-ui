<?php /* views/inbox.php - Admin support inbox for Contact-page messages. Markup reused from support.php (inbox view).
   Email links are left out on purpose: the mock never opens a mail client or Gmail. */
defined('MOCK') || exit;
$filter = in_array($_GET['status'] ?? '', ['NEW', 'READ', 'RESOLVED'], true) ? $_GET['status'] : 'ALL';
$all = rows('support_messages');
$counts = array_count_values(array_column($all, 'status')) + ['NEW' => 0, 'READ' => 0, 'RESOLVED' => 0];
$list = array_values(array_filter($all, fn ($m) => $filter === 'ALL' || $m['status'] === $filter));
$typeNames = ['ACCOUNT' => 'Account help', 'BUG' => 'Bug report', 'CONTENT' => 'Content correction', 'OTHER' => 'Other'];
$stColors = ['NEW' => '#4f6fb3', 'READ' => '#7c776c', 'RESOLVED' => '#3f8a5b'];
?>
<main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Admin console</p>
  <h1 class="text-3xl font-bold mb-2 page-title">Support inbox</h1>
  <p class="text-gray-400 mb-2">Messages from visitors and members.</p>
  <p class="text-xs text-gray-500 mb-6">Only Admins and the Owner can open this inbox. Sender emails are private: they appear here and nowhere else.</p>
  <div class="flex flex-wrap gap-2 mb-8">
    <?php foreach ([['ALL', 'All'], ['NEW', 'New'], ['READ', 'Read'], ['RESOLVED', 'Resolved']] as [$k, $l]): ?>
      <a href="<?= url('inbox', $k === 'ALL' ? [] : ['status' => $k]) ?>" class="px-3.5 py-1.5 rounded-full text-sm font-semibold transition duration-300 <?= $filter === $k ? 'bg-violet-600 text-white' : 'bg-slate-800/80 text-gray-300 border border-slate-700 hover:bg-slate-700' ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= $l ?><?= $k !== 'ALL' ? ' (' . $counts[$k] . ')' : '' ?></a>
    <?php endforeach; ?>
  </div>
  <?php if (!$list): ?><p class="text-gray-500 text-sm bg-slate-800/60 border border-slate-700 rounded-xl p-8 text-center">No messages in this view. &#10003;&#xFE0E;</p><?php endif; ?>
  <?php foreach ($list as $m): $sender = user($m['user_id']); $handler = user($m['handler_id']); $ref = 'IC-MSG-' . str_pad((string) $m['id'], 4, '0', STR_PAD_LEFT); ?>
    <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-5 mb-4 scroll-mt-20" id="msg-<?= $m['id'] ?>">
      <div class="flex flex-wrap justify-between items-start gap-3 mb-3">
        <div><h3 class="font-bold text-white"><?= e($m['name']) ?></h3><p class="text-xs text-gray-400"><?= e($m['email']) ?> &middot; <?= $sender ? 'registered member <strong class="text-gray-300">' . e($sender['username']) . '</strong>' : '<span class="text-gray-500">anonymous visitor</span>' ?> &middot; <span class="ref-code"><?= $ref ?></span></p></div>
        <div class="flex flex-col items-end gap-1.5"><span class="badge-level" style="background:<?= $stColors[$m['status']] ?>"><?= e($m['status']) ?></span><span class="text-xs text-gray-500"><?= when($m['created_min']) ?></span></div>
      </div>
      <div class="flex flex-wrap gap-2 text-xs mb-3"><span class="px-2 py-0.5 rounded bg-slate-700 text-gray-200"><?= e($typeNames[$m['type']] ?? $m['type']) ?></span><?php if ($handler): ?><span class="px-2 py-0.5 rounded bg-green-900 text-green-200">handled by <?= e($handler['username']) ?></span><?php endif; ?></div>
      <p class="text-sm text-gray-300 whitespace-pre-line border-l-2 border-slate-600 pl-4"><?= e($m['message']) ?></p>
      <div class="flex flex-wrap gap-3 mt-4">
        <?php if ($m['status'] === 'NEW'): ?><?= mock_form_open('mark_read', 'class="inline"') ?><button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-lg transition duration-300">Mark as read</button></form><?php endif; ?>
        <?php if ($m['status'] !== 'RESOLVED'): ?><?= mock_form_open('mark_resolved', 'class="inline" data-confirm="Mark this message as resolved? It moves to the Resolved tab." data-confirm-label="Mark resolved"') ?><button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-bold rounded-lg transition duration-300">&#10004;&#xFE0E; Mark resolved</button></form><?php endif; ?>
        <button type="button" class="px-4 py-2 bg-slate-600 hover:bg-slate-500 text-white text-sm font-bold rounded-lg transition duration-300" data-mock-msg="In the real site this opens your email app with the reference <?= $ref ?> in the subject.">Reply by email app</button>
      </div>
      <?php if ($m['reply']): ?>
        <div class="inbox-reply mt-4"><strong>Replied<?= $handler ? ' by ' . e($handler['username']) : '' ?></strong><p><?= nl2br(e($m['reply'])) ?></p></div>
      <?php else: ?>
        <details class="inbox-reply-form mt-4">
          <summary><?= $sender ? 'Reply in-app to ' . e($sender['username']) : 'Write a reply (then send it by email)' ?></summary>
          <?= mock_form_open('reply', 'class="mt-3" data-validate novalidate') ?>
            <label for="reply-<?= $m['id'] ?>" class="sr-only">Reply</label>
            <textarea id="reply-<?= $m['id'] ?>" name="reply" rows="4" class="form-input" minlength="5" maxlength="4000" required data-counter data-label="Reply" placeholder="Write your answer..."></textarea>
            <div class="flex justify-end mt-2"><button type="submit" class="form-button" style="width:auto">Save reply</button></div>
          </form>
        </details>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</main>
