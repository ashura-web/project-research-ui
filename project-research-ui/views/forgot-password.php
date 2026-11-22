<?php /* views/forgot-password.php - ask for a reset link. Markup reused from the real account.php. */
defined('MOCK') || exit;
$sent = isset($_GET['sent']);
$old += ['identifier' => ''];
?>
<main class="content-overlay auth-page">
  <div class="auth-shell auth-shell-single">
    <section class="auth-card" aria-labelledby="fp-h">
      <p class="auth-kicker">Account help</p>
      <h1 id="fp-h" class="auth-title">Forgot your password?</h1>
      <?php if ($sent): ?>
        <div class="form-status success" role="status">If that account exists, a reset link is on its way to the email address saved on it. The link works once, for 30 minutes.</div>
        <p class="auth-hint">Mock: no email was sent. <a href="<?= url('reset-password') ?>">Open the reset page</a> to see the next step.</p>
      <?php else: ?>
        <p class="auth-sub">Type your username or email and we will email you a link to choose a new one.</p>
        <?= mock_form_open('forgot-password', 'novalidate class="auth-form" data-validate') ?>
          <div class="form-input-group">
            <label for="identifier" class="auth-label">Username or email</label>
            <input type="text" id="identifier" name="identifier" class="form-input<?= invalid_class($errors, 'identifier') ?>" value="<?= e($old['identifier']) ?>" required autocomplete="username" autocapitalize="none" data-label="Username or email"<?= invalid_attr($errors, 'identifier', 'identifier') ?>>
            <?= field_error($errors, 'identifier', 'identifier') ?>
          </div>
          <button type="submit" class="form-button auth-submit">Email me a reset link</button>
        </form>
      <?php endif; ?>
      <ol class="help-steps" aria-label="If you are still stuck">
        <li><strong>Signed up with Gmail?</strong> "Continue with Google" on the <a href="<?= url('login') ?>">log-in page</a> works without a password.</li>
        <li><strong>No access to that email any more?</strong> <a href="<?= url('contact') ?>">Contact the team</a>. An Admin can check who you are and set a temporary password.</li>
      </ol>
    </section>
  </div>
</main>
