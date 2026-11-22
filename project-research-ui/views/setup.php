<?php /* views/setup.php - One-time "found the Owner" setup page. Markup reused from governance.php (custodian-bootstrap view).
   In the real site this page runs exactly once; ?closed=1 shows the "window is closed" state. Nothing is created here. */
defined('MOCK') || exit;
$old += ['username' => '', 'email' => '', 'note' => ''];
if (isset($_GET['closed'])): ?>
<main class="max-w-2xl mx-auto py-16 px-4 text-center content-overlay"><div class="empty-state p-10">
  <h1 class="text-2xl font-bold text-white mb-3">Setup window is closed</h1>
  <p class="text-sm text-gray-400 mb-2">The Owner appointment is already held by <strong class="text-white">owner_ada</strong>.</p>
  <p class="text-xs text-gray-500 mb-6">This page can never run twice. Succession happens in <a href="<?= url('owner-appointments') ?>" class="text-indigo-400 hover:underline">Admin appointments</a>.</p>
  <a href="<?= url('dashboard') ?>" class="btn-outline">&larr; Back to dashboard</a>
</div></main>
<?php return; endif; ?>
<main class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">System authority &middot; one-time setup</p>
  <h1 class="text-3xl font-bold mb-2 page-title">Found the Owner account</h1>
  <p class="text-gray-400 text-sm mb-8">Create the <strong class="text-white">permanent authority account</strong> that sits above the Admin team. This page can create it exactly once. Use a username you have not used before and a password you keep safe. <a href="<?= url('setup', ['closed' => 1]) ?>" class="text-indigo-400 hover:underline">See the closed state</a>.</p>
  <div class="form-status info mb-6">The account will be able to appoint and remove Admins, decide governance cases, and hand the appointment to a successor. It can <strong>not</strong> judge a case about itself.</div>
  <?php if ($errors): ?><div class="form-status error mb-6" role="alert">Please fix the highlighted fields.</div><?php endif; ?>
  <?= mock_form_open('setup', 'class="space-y-5 bg-slate-800/80 border border-amber-500/40 rounded-2xl p-6" data-validate novalidate') ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="username">Username (permanent)</label><input type="text" id="username" name="username" class="form-input<?= invalid_class($errors, 'username') ?>"<?= invalid_attr($errors, 'username', 'username') ?> value="<?= e($old['username']) ?>" placeholder="site_owner" maxlength="50" required autocomplete="username" data-pattern="username" data-label="Username"><?= field_error($errors, 'username', 'username') ?></div>
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="email">Email</label><input type="email" id="email" name="email" class="form-input<?= invalid_class($errors, 'email') ?>"<?= invalid_attr($errors, 'email', 'email') ?> value="<?= e($old['email']) ?>" maxlength="255" required autocomplete="email" data-label="Email"><?= field_error($errors, 'email', 'email') ?></div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="password">Password (min 8)</label><input type="password" id="password" name="password" class="form-input<?= invalid_class($errors, 'password') ?>"<?= invalid_attr($errors, 'password', 'password') ?> minlength="8" required autocomplete="new-password" data-pattern="password" data-strength data-capslock data-label="Password"><?= field_error($errors, 'password', 'password') ?></div>
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="password_confirm">Confirm password</label><input type="password" id="password_confirm" name="password_confirm" class="form-input<?= invalid_class($errors, 'password_confirm') ?>"<?= invalid_attr($errors, 'password_confirm', 'password_confirm') ?> minlength="8" required autocomplete="new-password" data-match="#password" data-label="Confirm password"><?= field_error($errors, 'password_confirm', 'password_confirm') ?></div>
    </div>
    <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="note">Appointment note (on permanent record)</label><textarea id="note" name="note" rows="3" class="form-input<?= invalid_class($errors, 'note') ?>"<?= invalid_attr($errors, 'note', 'note') ?> placeholder="Why this account holds the founding authority..." required minlength="10" data-label="Appointment note"><?= e($old['note']) ?></textarea><?= field_error($errors, 'note', 'note') ?></div>
    <button type="submit" class="form-button w-full">Create the account &amp; appoint it</button>
    <p class="text-xs text-gray-500 text-center">This page runs once. After this, succession and removal live in the owner console only.</p>
  </form>
</main>
