<?php /* views/member.php - a public member page (real name: agent record). */
defined('MOCK') || exit;
$u = find('users', (string) ($_GET['u'] ?? 'nova_07'), 'username');
if ($u && $u['status'] !== 'ACTIVE') $u = null;
?>
<main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
<?php if (!$u): ?>
  <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-10 text-center">
    <div class="es-icon"><?= nav_icon('user') ?></div>
    <h1 class="text-2xl font-bold text-white mb-3">Member not found</h1>
    <p class="text-gray-400 text-sm mb-6">No active member has that username.</p>
    <a href="<?= url('about') ?>#roster" class="btn-outline">See the roster</a>
  </div>
<?php else:
  $arts = array_filter(published('articles'), fn ($a) => $a['author_id'] === $u['id']);
  $notes = array_filter(published('notes'), fn ($a) => $a['author_id'] === $u['id']);
  $media = array_filter(published('media'), fn ($a) => $a['author_id'] === $u['id']); ?>
  <section class="member-hero <?= in_array(6, array_column(array_filter(rows('owned_items'), fn ($o) => $o['user_id'] === $u['id']), 'item_id'), true) ? 'banner-starfield' : 'banner-default' ?> mb-8">
    <div class="member-hero-banner" aria-hidden="true"></div>
    <div class="member-hero-body">
      <?= avatar_html($u, 'xl') ?>
      <div class="min-w-0 flex-1">
        <p class="kicker mb-1">Member record</p>
        <h1 class="text-3xl font-bold member-hero-name"><?= e($u['username']) ?></h1>
        <p class="text-sm text-gray-400 mt-1 flex flex-wrap items-center gap-2"><?= badge_html($u['role']) ?><span>joined <?= when($u['joined_min'], 'M Y') ?></span></p>
        <?php if ($u['bio']): ?><p class="text-gray-300 mt-3 whitespace-pre-line"><?= e($u['bio']) ?></p><?php endif; ?>
      </div>
      <div class="member-hero-stats">
        <div class="stat-tile"><span class="stat-num"><?= count($arts) ?></span><span class="stat-label">Articles</span></div>
        <div class="stat-tile"><span class="stat-num"><?= count($notes) ?></span><span class="stat-label">Notes</span></div>
        <div class="stat-tile"><span class="stat-num"><?= count($media) ?></span><span class="stat-label">Media</span></div>
      </div>
    </div>
  </section>
  <section class="mb-8">
    <h2 class="text-xl font-bold text-white mb-4 border-b border-slate-700 pb-2">Published articles</h2>
    <?php if (!$arts): ?><p class="text-gray-400 text-sm">No published articles yet.</p><?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <?php foreach ($arts as $d): ?><a href="<?= url('article', ['slug' => $d['slug']]) ?>" class="theory-card block group"><p class="text-xs text-gray-500 mb-1">Level <?= e($d['level']) ?> &middot; Score <?= $d['score'] ?>/10</p><h3 class="font-bold text-white group-hover:text-violet-300"><?= e($d['title']) ?></h3><p class="text-xs text-gray-400 mt-1"><?= e(short($d['summary'], 120)) ?></p></a><?php endforeach; ?>
    </div><?php endif; ?>
  </section>
  <section>
    <h2 class="text-xl font-bold text-white mb-4 border-b border-slate-700 pb-2">Approved notes</h2>
    <?php if (!$notes): ?><p class="text-gray-400 text-sm">No approved notes yet.</p><?php else: ?>
    <ul class="divide-y divide-slate-700/60">
      <?php foreach ($notes as $t): ?><li class="py-2 flex justify-between gap-3 text-sm"><a href="<?= url('note', ['id' => $t['id']]) ?>" class="text-gray-100 hover:text-violet-300"><?= e($t['title']) ?></a><span class="text-gray-500 whitespace-nowrap"><?= e(ucwords(strtolower(str_replace('_', ' ', $t['type'])))) ?> &middot; <?= when($t['created_min'], 'M j, Y') ?></span></li><?php endforeach; ?>
    </ul><?php endif; ?>
  </section>
<?php endif; ?>
</main>
