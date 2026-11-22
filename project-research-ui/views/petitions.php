<?php /* views/petitions.php - All petitions + "Start a petition" form. Markup reused from governance.php (governance-petitions view). */
defined('MOCK') || exit;
$list = rows('petitions');
$admins = where('users', fn ($u) => $u['role'] === 'admin' && $u['id'] !== $me['id']);
$old += ['type' => 'INVESTIGATION', 'target' => '', 'title' => '', 'body' => '', 'policy_reference' => ''];
$t = Governance::petitionThreshold();
?>
<main class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1"><a href="<?= url('governance') ?>" class="hover:underline">Community governance</a></p>
  <h1 class="text-3xl font-bold mb-2 page-title">Community petitions</h1>
  <p class="text-gray-400 text-sm mb-8 max-w-2xl">A petition asks for a formal review; <strong class="text-white">it never removes anyone by itself</strong>. If it reaches its signature threshold within <?= Governance::PETITION_DAYS ?> days, a governance case opens for the Owner, who must investigate and give a written decision. Signature counts are public; individual signers are not.</p>
  <section class="mb-10">
    <h2 class="text-xl font-bold text-white mb-4">All petitions</h2>
    <?php foreach ($list as $p): $pct = min(100, (int) round(100 * $p['signatures'] / max(1, $p['required']))); $tg = user($p['target_id']); ?>
      <div class="bg-slate-800/80 border border-slate-700 rounded-xl p-5 mb-3">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1"><h3 class="font-bold text-white"><?= e($p['title']) ?></h3><span class="gov-pill gov-<?= e($p['status']) ?>"><?= e(str_replace('_', ' ', $p['status'])) ?></span></div>
        <p class="text-xs text-gray-500 mb-3"><?= e(str_replace('_', ' ', $p['type'])) ?><?= $tg ? ' &middot; about <strong class="text-gray-400">' . e($tg['username']) . '</strong>' : '' ?> &middot; by <?= e(user($p['creator_id'])['username']) ?></p>
        <div class="gov-progress"><div class="gov-progress-bar" style="width:<?= $pct ?>%"></div></div>
        <p class="text-xs text-gray-400 mt-1"><?= $p['signatures'] ?> / <?= $p['required'] ?> signatures</p>
        <a class="text-indigo-400 hover:underline text-sm inline-block mt-2" href="<?= url('petition', ['id' => $p['id']]) ?>"><?= $p['status'] === 'OPEN' ? 'View &amp; sign' : 'View' ?> &rarr;</a>
      </div>
    <?php endforeach; ?>
  </section>
  <section id="start" class="bg-slate-800/80 border border-slate-700 rounded-xl p-6 scroll-mt-20">
    <h2 class="text-xl font-bold text-white mb-2">Start a petition</h2>
    <p class="text-sm text-gray-400 mb-5">Eligibility: an active account past its waiting time (Admin 1 day, Reviewer 2, Contributor 3, Member 4 days after sign-up). One open petition per Admin target, two open petitions per member. The Owner cannot be petitioned. The signature threshold is frozen when you open it: currently <strong class="text-white"><?= $t ?></strong> signature<?= $t === 1 ? '' : 's' ?> (20% of active accounts).</p>
    <?php if ($errors): ?><div class="form-status error mb-4" role="alert">Please fix <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?>.</div><?php endif; ?>
    <?= mock_form_open('petition_start', 'class="space-y-4" data-validate data-draft="petition" novalidate') ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="petition_type">Type</label><select id="petition_type" name="type" class="form-input"><?php foreach (Governance::PETITION_TYPES as $k => $l): ?><option value="<?= $k ?>" <?= $old['type'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
        <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="target">Target Admin <span class="text-gray-500 font-normal">(policy reviews: optional)</span></label><select id="target" name="target" class="form-input"><option value="">None / policy only</option><?php foreach ($admins as $a): ?><option value="<?= $a['id'] ?>" <?= (string) $old['target'] === (string) $a['id'] ? 'selected' : '' ?>><?= e($a['username']) ?></option><?php endforeach; ?></select></div>
      </div>
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="title">Title</label><input type="text" id="title" name="title" class="form-input<?= invalid_class($errors, 'title') ?>"<?= invalid_attr($errors, 'title', 'title') ?> value="<?= e($old['title']) ?>" minlength="10" maxlength="180" required data-label="Title" data-counter><?= field_error($errors, 'title', 'title') ?></div>
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="description">What is the concern?</label><textarea id="description" name="body" rows="6" class="form-input<?= invalid_class($errors, 'body') ?>"<?= invalid_attr($errors, 'body', 'description') ?> minlength="30" maxlength="5000" required data-label="Reason" data-counter><?= e($old['body']) ?></textarea><?= field_error($errors, 'body', 'description') ?></div>
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="policy_reference">Help-centre rule <span class="text-gray-500 font-normal">(optional)</span></label><input type="text" id="policy_reference" name="policy_reference" class="form-input" value="<?= e($old['policy_reference']) ?>" maxlength="255"></div>
      <div class="flex items-center justify-between gap-3"><small class="text-xs text-gray-500" data-draft-status></small><button type="submit" class="form-button" style="width:auto">Open the petition</button></div>
    </form>
  </section>
</main>
