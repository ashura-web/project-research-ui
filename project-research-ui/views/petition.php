<?php /* views/petition.php - One petition with its signature bar and the Sign button. Markup reused from governance.php (governance-petition view). */
defined('MOCK') || exit;
$p = find('petitions', (int) ($_GET['id'] ?? 1));
if (!$p): ?>
<main class="max-w-2xl mx-auto py-16 px-4 text-center content-overlay"><div class="empty-state p-10"><h1 class="text-2xl font-bold text-white mb-3">Petition not found</h1><a href="<?= url('petitions') ?>" class="btn-outline">&larr; All petitions</a></div></main>
<?php return; endif;
$signed = !empty($_SESSION['mock']['signed'][$p['id']]);
$sigs = $p['signatures'] + ($signed ? 1 : 0);
$pct = min(100, (int) round(100 * $sigs / max(1, $p['required'])));
$isTarget = $p['target_id'] === $me['id'];
$tg = user($p['target_id']);
?>
<main class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1"><a href="<?= url('petitions') ?>" class="hover:underline">Community governance &middot; Petitions</a></p>
  <div class="flex flex-wrap items-center justify-between gap-3 mb-1"><h1 class="text-3xl font-bold page-title"><?= e($p['title']) ?></h1><span class="gov-pill gov-<?= e($p['status']) ?>"><?= e(str_replace('_', ' ', $p['status'])) ?></span></div>
  <p class="text-sm text-gray-500 mb-6"><span class="ref-code">PET-2026-<?= str_pad((string) $p['id'], 3, '0', STR_PAD_LEFT) ?></span> &middot; <?= e(str_replace('_', ' ', $p['type'])) ?><?= $tg ? ' &middot; about <strong class="text-gray-400">' . e($tg['username']) . '</strong>' : '' ?> &middot; opened by <?= e(user($p['creator_id'])['username']) ?> &middot; expires in <?= $p['expires_days'] ?> days</p>
  <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6"><p class="text-sm text-gray-300 whitespace-pre-line"><?= e($p['body']) ?></p></section>
  <section class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 mb-6">
    <div class="gov-progress mb-2" role="img" aria-label="<?= $sigs ?> of <?= $p['required'] ?> signatures"><div class="gov-progress-bar" style="width:<?= $pct ?>%"></div></div>
    <p class="text-sm text-gray-300 mb-4"><strong class="text-white"><?= $sigs ?></strong> of <?= $p['required'] ?> required signatures (threshold frozen when the petition opened). Individual signers stay private.</p>
    <?php if ($signed): ?><p class="text-sm text-green-400 font-semibold">&#10004;&#xFE0E; You have signed this petition.</p>
    <?php elseif ($isTarget): ?><p class="text-sm text-gray-400">You are the subject of this petition, so you cannot sign it. You keep your right to respond if a case is opened.</p>
    <?php elseif ($p['status'] === 'OPEN'): ?><?= mock_form_open('sign') ?><button type="submit" class="form-button" style="width:auto">Sign this petition</button></form>
    <?php else: ?><p class="text-sm text-gray-400">Signatures are closed for this petition.</p><?php endif; ?>
    <?php if ($p['status'] === 'THRESHOLD_REACHED'): ?><p class="text-xs text-gray-500 mt-3">Threshold reached: a governance case was opened for the Owner. Community support triggers review; it never removes anyone by itself.</p><?php endif; ?>
  </section>
</main>
