<?php /* views/reset-password.php - choose a new password from an emailed link. ?expired shows the invalid-link state. */
defined('MOCK') || exit;
$valid = !isset($_GET['expired']);
?>
<main class="content-overlay auth-page">
  <div class="auth-shell auth-shell-single">
    <section class="auth-card" aria-labelledby="rp-h">
      <p class="auth-kicker">Account help</p>
      <h1 id="rp-h" class="auth-title">Choose a new password</h1>
      <?php if (!$valid): ?>
        <div class="form-status error" role="alert">This reset link is not valid any more. Links work once and only for 30 minutes.</div>
        <div class="auth-alt"><p><a href="<?= url('forgot-password') ?>">Ask for a new link</a> or <a href="<?= url('contact') ?>">contact the team</a>.</p></div>
      <?php else: ?>
        <p class="auth-sub">For <strong>nova_07</strong>. Choose a strong password you do not use anywhere else.</p>
        <?= mock_form_open('reset-password', 'novalidate class="auth-form" data-validate') ?>
          <input type="text" name="username" value="nova_07" autocomplete="username" class="sr-only" tabindex="-1" aria-hidden="true">
          <div class="form-input-group">
            <label for="new_password" class="auth-label">New password</label>
            <input type="password" id="new_password" name="new_password" class="form-input<?= invalid_class($errors, 'new_password') ?>" required autocomplete="new-password" data-pattern="password" data-label="New password" data-strength data-capslock<?= invalid_attr($errors, 'new_password', 'new_password') ?>>
            <?= field_error($errors, 'new_password', 'new_password') ?>
          </div>
          <div class="form-input-group">
            <label for="confirm_password" class="auth-label">Repeat new password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input<?= invalid_class($errors, 'confirm_password') ?>" required autocomplete="new-password" data-match="#new_password" data-label="Password confirmation"<?= invalid_attr($errors, 'confirm_password', 'confirm_password') ?>>
            <?= field_error($errors, 'confirm_password', 'confirm_password') ?>
          </div>
          <p class="auth-hint">At least 8 characters with a letter and a number.</p>
          <button type="submit" class="form-button auth-submit">Save new password</button>
        </form>
        <p class="auth-hint mt-3"><a href="<?= url('reset-password', ['expired' => 1]) ?>">See the "expired link" state</a></p>
      <?php endif; ?>
    </section>
  </div>
</main>
