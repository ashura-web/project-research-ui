<?php /* views/dashboard.php - the member home after login. Markup reused from the real workspace.php dashboard section. */
defined('MOCK') || exit;
$r = role_key(); $isOwner = $r === 'owner';
$myArticles = where('articles', fn ($a) => $a['author_id'] === $me['id']);
$myNotes = where('notes', fn ($a) => $a['author_id'] === $me['id']);
$myMedia = where('media', fn ($a) => $a['author_id'] === $me['id']);
$credits = (int) $me['credits'];
$nextItem = null; foreach (rows('store_items') as $it) { if ($it['price'] > 0 && ($nextItem === null || ($it['price'] > $credits && $it['price'] < $nextItem['price']))) $nextItem = $it; }
$steps = [[true, 'Create your account', ''], [(bool) $me['bio'], 'Write a short bio on your profile', url('profile')], [(bool) $myNotes, 'Post your first note', url('write-note')],
          [(bool) $myMedia, 'Share an image or video', url('media-submit')], [false, 'Save an article to your reading list', url('explore')]];
$stepsDone = count(array_filter($steps, fn ($s) => $s[0]));
$request = where('rank_requests', fn ($q) => $q['user_id'] === $me['id'], 1)[0] ?? null;
$bulletins = array_slice(array_filter(rows('bulletins'), fn ($b) => at_least($b['min_role'])), 0, 3);
$reading = array_slice(published('articles'), 2, 3);
$achievements = [['First words', 'Post one note', count($myNotes), 1], ['Storyteller', 'Get 5 notes approved', count($myNotes), 5], ['Archivist', 'Publish an article', count(array_filter($myArticles, fn ($a) => $a['status'] === 'PUBLISHED')), 1], ['Collector', 'Earn 500 credits', $credits, 500]];
$console = at_least('reviewer') ? ['review' => count(where('articles', fn ($a) => $a['status'] === 'PENDING')) + count(where('notes', fn ($a) => $a['status'] === 'PENDING')) + count(where('media', fn ($a) => $a['status'] === 'PENDING')),
    'requests' => count(where('rank_requests', fn ($q) => $q['status'] === 'PENDING')), 'support' => count(where('support_messages', fn ($m) => $m['status'] === 'NEW'))] : null;
$noteType = ['NOTE' => 'Note', 'IDEA' => 'Idea', 'FIELD_NOTE' => 'Field note'];
?>
<main class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8 content-overlay dash">
  <section class="dash-hero" aria-labelledby="dash-h">
    <div class="dash-hero-top">
      <span class="dash-avatar" aria-hidden="true"><?= e(initials($me['username'])) ?></span>
      <div class="min-w-0">
        <p class="kicker mb-1">Dashboard</p>
        <h1 id="dash-h" class="dash-title"><span data-greet>Welcome back</span>, <?= e($me['username']) ?></h1>
        <p class="dash-meta"><span class="role-pill" style="background:<?= role_color($r) ?>"><?= role_title($r) ?></span><span><?= e($me['email']) ?></span><span>Member since <?= when($me['joined_min'], 'M j, Y') ?></span><span>Last login <?= when(25, 'M j, g:ia') ?></span></p>
      </div>
    </div>
    <nav class="dash-actions" aria-label="Quick actions">
      <a href="<?= url('write-note') ?>"><?= nav_icon('pen') ?><span>Write a note</span></a>
      <a href="<?= url('media-submit') ?>"><?= nav_icon('upload') ?><span>Share media</span></a>
      <?php if (at_least('contributor')): ?><a href="<?= url('contribute') ?>"><?= nav_icon('plus') ?><span>New article</span></a><?php endif; ?>
      <?php if ($console): ?><a href="<?= url('review-queue') ?>"><?= nav_icon('check') ?><span>Review queue (<?= $console['review'] ?>)</span></a><?php endif; ?>
      <a href="<?= url('explore') ?>"><?= nav_icon('search') ?><span>Explore</span></a>
      <a href="<?= url('random') ?>"><?= nav_icon('shuffle') ?><span>Surprise me</span></a>
      <a href="<?= url('store') ?>"><?= nav_icon('bag') ?><span>Store</span></a>
    </nav>
  </section>

  <section class="dash-grid<?= $isOwner ? ' is-single' : '' ?>" aria-label="Your progress">
    <div class="dash-card">
      <div class="dash-card-head"><h2>Getting started</h2><span class="dash-chip"><?= $stepsDone ?> / <?= count($steps) ?> done</span></div>
      <div class="dash-bar" role="progressbar" aria-valuemin="0" aria-valuemax="<?= count($steps) ?>" aria-valuenow="<?= $stepsDone ?>" aria-label="Getting started progress"><span style="width:<?= round($stepsDone / count($steps) * 100) ?>%"></span></div>
      <ul class="dash-steps">
        <?php foreach ($steps as [$done, $label, $href]): ?><li class="<?= $done ? 'is-done' : '' ?>"><span class="ds-tick" aria-hidden="true"><?= $done ? nav_icon('check') : '' ?></span><?php if ($done): ?><span><?= e($label) ?></span><span class="sr-only">(done)</span><?php else: ?><a href="<?= $href ?>"><?= e($label) ?></a><?php endif; ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php if (!$isOwner): ?>
    <div class="dash-card">
      <div class="dash-card-head"><h2>Credits and rank</h2><a href="<?= url('store') ?>" class="text-sm text-indigo-300 hover:underline">Open store</a></div>
      <p class="dash-credits"><b data-countup="<?= $credits ?>"><?= $credits ?></b> <span>credits</span></p>
      <?php if ($nextItem && $credits < $nextItem['price']): ?>
        <div class="dash-bar is-gold" role="progressbar" aria-valuemin="0" aria-valuemax="<?= $nextItem['price'] ?>" aria-valuenow="<?= $credits ?>" aria-label="Credits towards <?= e($nextItem['name']) ?>"><span style="width:<?= round($credits / $nextItem['price'] * 100) ?>%"></span></div>
        <p class="text-sm text-gray-400"><?= $nextItem['price'] - $credits ?> more credits for <strong class="text-gray-200"><?= e($nextItem['name']) ?></strong>. Approved notes earn 15, media 10, articles 50.</p>
      <?php elseif ($nextItem): ?><p class="text-sm text-gray-300">You can afford <strong><?= e($nextItem['name']) ?></strong> (<?= $nextItem['price'] ?> credits).</p><?php endif; ?>
      <ol class="dash-ranks" aria-label="Ranks">
        <?php foreach (['member', 'contributor', 'reviewer', 'admin'] as $x): ?><li class="<?= role_rank($x) < role_rank() ? 'is-past' : '' ?><?= $x === $r ? ' is-now' : '' ?>"<?= $x === $r ? ' aria-current="step"' : '' ?>><?= role_title($x) ?></li><?php endforeach; ?>
      </ol>
      <div class="dash-req text-sm">
        <?php if ($request): ?><?= status_pill($request['status']) ?> Your request for <strong><?= role_title($request['requested_role']) ?></strong> <?= $request['status'] === 'PENDING' ? 'is waiting for a decision.' : 'was ' . strtolower($request['status']) . '.' ?>
        <?php else: ?>No rank requests on file.<?php endif; ?>
      </div>
      <?php if (role_rank() <= 2): ?><a href="<?= url('rank-request') ?>" class="btn-outline mt-3 inline-flex">Request a higher rank</a><?php endif; ?>
    </div>
    <?php endif; ?>
  </section>

  <?php if (!$isOwner): ?>
  <section class="dash-ach" aria-labelledby="ach-h">
    <h2 id="ach-h">Achievements <span><?= count(array_filter($achievements, fn ($x) => $x[2] >= $x[3])) ?> of <?= count($achievements) ?></span></h2>
    <ul>
      <?php foreach ($achievements as [$name, $why, $have, $need]): $got = $have >= $need; ?>
        <li class="<?= $got ? 'is-got' : '' ?>" title="<?= e($why) ?>"><?= nav_icon($got ? 'star' : 'clock') ?><span><b><?= e($name) ?></b><small><?= e($why) ?></small><?php if (!$got && $need > 1): ?><span class="ach-bar" aria-hidden="true"><i style="width:<?= (int) round(min($have, $need) / $need * 100) ?>%"></i></span><small class="ach-count"><?= number_format($have) ?> / <?= number_format($need) ?></small><?php endif; ?></span><span class="sr-only"><?= $got ? '(earned)' : '(not yet)' ?></span></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <?php if ($console): ?>
  <section class="console-panel rounded-xl p-6 mb-8" aria-label="Staff console">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><div><p class="kicker mb-1">Staff console</p><h2 class="text-xl font-bold text-white">Duty stations</h2></div><?php if (at_least('admin')): ?><a href="<?= url('audit-log') ?>" class="btn-outline">Audit log</a><?php endif; ?></div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <a href="<?= url('review-queue') ?>" class="console-tile"><span class="stat-num"><?= $console['review'] ?></span><span class="stat-label">Review queue</span><span class="console-sub">submissions awaiting a verdict</span></a>
      <a href="<?= url('requests') ?>" class="console-tile"><span class="stat-num"><?= $console['requests'] ?></span><span class="stat-label">Rank requests</span><span class="console-sub">waiting for a decision</span></a>
      <?php if (at_least('admin')): ?><a href="<?= url('inbox') ?>" class="console-tile"><span class="stat-num"><?= $console['support'] ?></span><span class="stat-label">Support inbox</span><span class="console-sub">new messages</span></a><?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8" aria-label="Your contribution stats">
    <div class="stat-tile"><span class="stat-num"><?= count($myArticles) ?></span><span class="stat-label">Articles filed</span></div>
    <div class="stat-tile"><span class="stat-num"><?= count(array_filter($myArticles, fn ($a) => $a['status'] === 'PUBLISHED')) ?></span><span class="stat-label">Published</span></div>
    <div class="stat-tile"><span class="stat-num"><?= count(array_filter($myNotes, fn ($a) => $a['status'] === 'PUBLISHED')) ?></span><span class="stat-label">Notes approved</span></div>
    <div class="stat-tile"><span class="stat-num"><?= array_sum(array_column($myArticles, 'views')) ?></span><span class="stat-label">Article views</span></div>
    <div class="stat-tile"><span class="stat-num"><?= count(array_filter($myMedia, fn ($a) => $a['status'] === 'PUBLISHED')) ?></span><span class="stat-label">Media approved</span></div>
  </section>

  <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-slate-800/80 rounded-xl border border-slate-700 p-6">
      <div class="flex items-center justify-between mb-3"><h2 class="text-lg font-bold text-white">Bulletins</h2><a href="<?= url('notifications') ?>" class="text-sm text-indigo-400 hover:underline">Open board</a></div>
      <ul class="space-y-3">
        <?php foreach ($bulletins as $b): ?><li class="flex gap-3 items-start"><?php if ($b['unread']): ?><span class="unread-dot mt-1.5" aria-label="Unread"></span><?php endif; ?><div class="min-w-0"><a href="<?= url('notifications') ?>#b-<?= $b['id'] ?>" class="text-sm font-semibold text-gray-100 hover:text-violet-300"><?= e($b['title']) ?></a><p class="text-xs text-gray-500"><span class="prio-badge prio-<?= strtolower($b['priority']) ?>"><?= e($b['priority']) ?></span> &middot; <?= ago($b['created_min']) ?></p></div></li><?php endforeach; ?>
      </ul>
    </div>
    <div class="bg-slate-800/80 rounded-xl border border-slate-700 p-6" id="reading-list">
      <div class="flex items-center justify-between mb-3"><h2 class="text-lg font-bold text-white">My reading list</h2><a href="<?= url('explore') ?>" class="text-sm text-indigo-400 hover:underline">Browse</a></div>
      <ul class="divide-y divide-slate-700/60">
        <?php foreach ($reading as $a): ?><li class="py-2 flex justify-between gap-3 text-sm"><a href="<?= url('article', ['slug' => $a['slug']]) ?>" class="text-gray-100 hover:text-violet-300"><?= e($a['title']) ?></a><span class="text-gray-500 whitespace-nowrap">Level <?= e($a['level']) ?> &middot; <?= $a['score'] ?>/10</span></li><?php endforeach; ?>
      </ul>
    </div>
    <div class="bg-slate-800/80 rounded-xl border border-slate-700 p-6" id="recently-viewed">
      <div class="flex items-center justify-between mb-3"><h2 class="text-lg font-bold text-white">Recently viewed</h2></div>
      <p class="text-sm text-gray-400">Articles you open are listed here, newest first. In the real site this list lived in the browser only (localStorage).</p>
    </div>
  </section>

  <nav class="dash-jump" aria-label="My work"><a href="#my-articles">My articles <span><?= count($myArticles) ?></span></a><a href="#my-notes">My notes <span><?= count($myNotes) ?></span></a><a href="#my-media">My media <span><?= count($myMedia) ?></span></a></nav>

  <section id="my-articles" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6 mb-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><h2 class="text-xl font-bold text-white">My articles</h2>
      <?php if (at_least('contributor')): ?><a href="<?= url('contribute') ?>" class="btn-outline">+ New article</a><?php else: ?><a href="<?= url('rank-request') ?>" class="btn-locked" title="Articles unlock at Contributor">New article <span class="text-xs opacity-75">&mdash; Contributor required</span></a><?php endif; ?></div>
    <?php if (!$myArticles): ?>
      <div class="empty-state"><div class="es-icon"><?= nav_icon('grid') ?></div><h4><?= at_least('contributor') ? 'No articles filed yet' : 'Articles unlock at Contributor' ?></h4><p><?= at_least('contributor') ? 'Your articles and their review status appear here.' : 'Full articles are a Contributor privilege. You can still post notes right now.' ?></p><a href="<?= url(at_least('contributor') ? 'contribute' : 'write-note') ?>" class="btn-outline"><?= at_least('contributor') ? 'File your first article' : 'Write a note instead' ?></a></div>
    <?php else: ?>
      <div class="overflow-x-auto"><table class="w-full text-sm min-w-[480px] row-hover m-stack">
        <thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2">Title</th><th class="py-2">Status</th><th class="py-2">Progress</th><th class="py-2">Submitted</th></tr></thead>
        <tbody>
        <?php foreach ($myArticles as $a): ?>
          <tr class="border-b border-slate-700/50"><td class="py-2.5 text-gray-200" data-label="Title"><?php if ($a['status'] === 'PUBLISHED'): ?><a href="<?= url('article', ['slug' => $a['slug']]) ?>" class="hover:text-indigo-300"><?= e($a['title']) ?></a><?php else: ?><?= e($a['title']) ?><?php endif; ?></td>
            <td class="py-2.5" data-label="Status"><?= status_pill($a['status']) ?></td><td class="py-2.5" data-label="Progress"><?= render_steps(progress_steps($a['status'])) ?></td><td class="py-2.5 text-gray-400" data-label="Submitted"><?= when($a['created_min'], 'M j, Y') ?></td></tr>
          <?php if (in_array($a['status'], ['DRAFT', 'PENDING', 'RETURNED'], true)): ?><tr class="border-b border-slate-700/50"><td colspan="4" class="pb-3"><?php if (!empty($a['review_note'])): ?><div class="review-note"><strong>Reviewer note:</strong> <?= e($a['review_note']) ?></div><?php endif; ?><a href="<?= url('contribute', ['id' => $a['id']]) ?>" class="text-xs text-indigo-400 hover:underline">&#9998;&#xFE0E; <?= $a['status'] === 'RETURNED' ? 'Edit &amp; resubmit' : ($a['status'] === 'PENDING' ? 'Edit while pending' : 'Continue draft') ?></a></td></tr><?php endif; ?>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
  </section>

  <section id="my-notes" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6 mb-8">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><h2 class="text-xl font-bold text-white">My notes</h2><a href="<?= url('write-note') ?>" class="btn-outline">+ New note</a></div>
    <?php if (!$myNotes): ?><?= empty_state('book', 'No notes yet', 'Share an idea, a working note or a field observation. Every member can post.') ?>
    <?php else: ?><div class="overflow-x-auto"><table class="w-full text-sm min-w-[480px] row-hover m-stack"><thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2">Title</th><th class="py-2">Type</th><th class="py-2">Status</th><th class="py-2">Submitted</th></tr></thead><tbody>
      <?php foreach ($myNotes as $n): ?><tr class="border-b border-slate-700/50"><td class="py-2.5 text-gray-200" data-label="Title"><?php if ($n['status'] === 'PUBLISHED'): ?><a href="<?= url('note', ['id' => $n['id']]) ?>" class="hover:text-indigo-300"><?= e($n['title']) ?></a><?php else: ?><?= e($n['title']) ?><?php endif; ?></td><td class="py-2.5 text-gray-400" data-label="Type"><?= $noteType[$n['type']] ?></td><td class="py-2.5" data-label="Status"><?= status_pill($n['status']) ?></td><td class="py-2.5 text-gray-400" data-label="Submitted"><?= when($n['created_min'], 'M j, Y') ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </section>

  <section id="my-media" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><h2 class="text-xl font-bold text-white">My media</h2><a href="<?= url('media-submit') ?>" class="btn-outline">+ New upload</a></div>
    <?php if (!$myMedia): ?><?= empty_state('video', 'No media yet', 'Upload an image, a video link or audio. A Reviewer checks everything before it goes live.') ?>
    <?php else: ?><div class="overflow-x-auto"><table class="w-full text-sm min-w-[480px] row-hover m-stack"><thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2">Title</th><th class="py-2">Type</th><th class="py-2">Status</th><th class="py-2">Submitted</th></tr></thead><tbody>
      <?php foreach ($myMedia as $m): ?><tr class="border-b border-slate-700/50"><td class="py-2.5 text-gray-200" data-label="Title"><?= e($m['title']) ?></td><td class="py-2.5 text-gray-400" data-label="Type"><?= e($m['type']) ?></td><td class="py-2.5" data-label="Status"><?= status_pill($m['status']) ?></td><td class="py-2.5 text-gray-400" data-label="Submitted"><?= when($m['created_min'], 'M j, Y') ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
  </section>
</main>
