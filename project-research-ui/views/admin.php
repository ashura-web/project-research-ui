<?php /* views/admin.php - Admin console landing page: client/admin side split + tool tiles with live counts.
   Markup reused from admin.php (admin-console view). Counts come from the mock rows. */
defined('MOCK') || exit;
$isOwner = at_least('owner');
$fail = count(where('email_outbox', fn ($r) => $r['status'] === 'FAILED'));
$tiles = [
    ['users', 'users', 'Users', 'Ranks, suspensions and password resets.', count(where('users', fn ($u) => $u['status'] === 'ACTIVE')) . ' active'],
    ['inbox', 'inbox', 'Support inbox', 'Messages sent through the Contact page.', count(where('support_messages', fn ($m) => $m['status'] === 'NEW'))],
    ['review-queue', 'check', 'Review queue', 'Pending articles, notes and media.', count(where('articles', fn ($a) => $a['status'] === 'PENDING')) + count(where('notes', fn ($a) => $a['status'] === 'PENDING')) + count(where('media', fn ($a) => $a['status'] === 'PENDING'))],
    ['requests', 'userplus', 'Rank requests', 'Rank requests from members.', count(where('rank_requests', fn ($r) => $r['status'] === 'PENDING'))],
    ['audit-log', 'book', 'Audit log', 'Every privileged action, who and when.', null],
    ['mail-outbox', 'mail', 'Email outbox', 'Queued, sent and failed emails.', $fail ? $fail . ' failed' : 0],
];
if ($isOwner) {
    $tiles[] = ['content', 'trash', 'Content manager', 'Find and remove any post, with a reason for the author.', '1 removed'];
    $tiles[] = ['owner', 'gavel', 'Owner console', 'Admin appointments and governance cases.', null];
}
?>
<main class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Admin side</p>
  <h1 class="text-3xl font-bold mb-2 page-title">Admin console</h1>
  <p class="text-gray-400 mb-6">Tools for the Admin team<?= $isOwner ? ' and the Owner' : '' ?>. Members (Member, Contributor, Reviewer) use the client side: the public pages and their workspace.</p>
  <div class="form-status error mb-6" role="alert">1 Admin account has no authenticator app yet. It can only reach its profile until two-step sign-in is set up. (Mock warning.)</div>
  <div class="side-split mb-8">
    <div><strong class="text-white">Client side</strong><br>Public pages, dashboard, write an article, notes, media upload, bulletins, governance. Reviewers also review submissions and rank requests.</div>
    <div><strong class="text-white">Admin side</strong><br>Admins: users, support inbox, audit log, email outbox. Owner: everything above plus the owner console and removing any post with a reason.</div>
  </div>
  <div class="admin-tiles">
    <?php foreach ($tiles as [$slug, $ic, $name, $desc, $n]): ?>
      <a class="admin-tile" href="<?= url($slug) ?>"><h3><?= nav_icon($ic) ?><?= e($name) ?><?php if ($n !== null): ?><span class="tile-count <?= $n === 0 ? 'is-zero' : '' ?>"><?= e((string) $n) ?></span><?php endif; ?></h3><p><?= e($desc) ?></p></a>
    <?php endforeach; ?>
  </div>
</main>
