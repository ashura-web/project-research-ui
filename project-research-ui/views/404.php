<?php /* views/404.php - not found. */
defined('MOCK') || exit;
$asked = (string) ($GLOBALS['asked_page'] ?? '');
if ($asked === '404') $asked = '';
$guess = preg_replace('/[^a-z0-9 ]+/i', ' ', basename($asked));
?>
<main class="content-overlay py-16 px-4 flex justify-center items-center min-h-[70vh]">
  <div class="w-full max-w-lg bg-slate-800/80 border border-slate-700 rounded-xl p-10 text-center">
    <p class="kicker mb-2">Error 404 &middot; Not found</p>
    <h1 class="text-3xl font-bold page-title mb-3">This page leads nowhere</h1>
    <p class="text-gray-400 text-sm mb-6">The page <code class="text-violet-300"><?= e($asked ?: '?') ?></code> is not part of this mock. It may have been renamed, or it never existed.</p>
    <form action="<?= url('explore') ?>" method="GET" class="flex gap-2 mb-6" role="search">
      <?php if (!pretty_urls() && !defined('STATIC_EXPORT')): ?><input type="hidden" name="page" value="explore"><?php endif; ?>
      <label for="nf-q" class="sr-only">Search</label>
      <input type="search" id="nf-q" name="q" class="form-input" value="<?= e(trim($guess)) ?>" placeholder="Search…">
      <button type="submit" class="form-button !w-auto px-5">Search</button>
    </form>
    <div class="flex flex-wrap gap-3 justify-center">
      <a href="<?= url('home') ?>" class="btn-outline">Home</a><a href="<?= url('gallery') ?>" class="btn-outline">Gallery</a><a href="<?= url('pages') ?>" class="btn-outline">All pages</a><a href="<?= url('home') ?>" class="btn-outline" data-back>&larr; Go back</a>
    </div>
  </div>
</main>
