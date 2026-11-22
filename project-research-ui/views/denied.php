<?php /* views/denied.php - 403 "rank too low" panel with the rank ladder. Ported from the real denied view. */
defined('MOCK') || exit;
$need = $deniedFor ?? 'member';
$have = role_key();
?>
<main class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <div class="bg-slate-800/80 border border-amber-500/40 rounded-2xl p-8 shadow-2xl">
    <p class="kicker mb-2">Access control &mdash; error 403</p>
    <h1 class="text-3xl font-bold page-title mb-3"><?= signed_in() ? 'Your rank is too low for this page' : 'Please log in first' ?></h1>
    <p class="text-gray-300 mb-6 leading-relaxed">This page needs the <strong class="text-white"><?= role_title($need) ?></strong> rank or above, but you are
      <?= signed_in() ? 'signed in as' : 'browsing as a' ?> <strong class="text-white"><?= role_title($have) ?></strong>. Nothing was harmed: you simply lack the rank for this door.</p>
    <div class="rank-ladder mb-6">
      <?php foreach (['member', 'contributor', 'reviewer', 'admin', 'owner'] as $r): $you = $r === $have; $req = $r === $need && !$you; ?>
        <div class="rung<?= $you ? ' is-you' : '' ?><?= $req ? ' is-required' : '' ?>"><span class="badge-level" style="background: <?= role_color($r) ?>;"><?= strtoupper(substr($r, 0, 1)) ?></span><span class="rung-name"><?= role_title($r) ?></span><?php if ($you): ?><span class="rung-tag tag-you">YOU</span><?php endif; ?><?php if ($req): ?><span class="rung-tag tag-need">REQUIRED</span><?php endif; ?></div>
      <?php endforeach; ?>
    </div>
    <div class="bg-slate-900/60 border border-slate-700 rounded-xl p-4 mb-6 text-sm text-gray-400">
      <strong class="text-gray-200"><?= role_title($need) ?> unlocks:</strong> <?= e(ROLE_UNLOCKS[$need] ?? '') ?>
      <div class="mt-2 text-gray-500"><strong>Research mock:</strong> use the <em class="not-italic text-gray-300">Preview as</em> switcher in the top banner to see this page as a <?= role_title($need) ?>.</div>
    </div>
    <div class="flex flex-wrap gap-3">
      <?php if (!signed_in()): ?><a href="<?= url('login') ?>" class="form-button no-underline inline-flex items-center gap-2 w-auto">Log in</a><a href="<?= url('signup') ?>" class="btn-outline">Create an account</a>
      <?php else: ?>
        <?php if (role_rank() <= 2 && in_array($need, ['contributor', 'reviewer'], true)): ?><a href="<?= url('rank-request') ?>" class="form-button no-underline inline-flex items-center gap-2 w-auto">Request a rank upgrade</a><?php endif; ?>
        <a href="<?= url('dashboard') ?>" class="btn-outline">Return to dashboard</a>
        <?php if (role_rank() <= 1): ?><a href="<?= url('write-note') ?>" class="btn-outline">Write a note instead</a><?php endif; ?>
      <?php endif; ?>
      <form method="POST" action="<?= url(current_page()) ?>" class="inline"><?= Csrf::field() ?><input type="hidden" name="mock_action" value="preview_role"><input type="hidden" name="role" value="<?= e($need) ?>"><input type="hidden" name="back" value="<?= e(current_page()) ?>"><button type="submit" class="btn-outline"><?= nav_icon('eye') ?> Preview as <?= role_title($need) ?></button></form>
    </div>
  </div>
</main>
