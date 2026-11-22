<?php /* views/choose-username.php - first visit after a social sign-in (real name: google-finish). */
defined('MOCK') || exit;
$old += ['username' => ''];
?>
<main class="content-overlay auth-page">
  <div class="auth-shell auth-shell-single">
    <section class="auth-card" aria-labelledby="gf-h">
      <p class="auth-kicker">Signed in with Google (mock)</p>
      <h1 id="gf-h" class="auth-title">Choose your username</h1>
      <p class="auth-sub">Google confirmed <strong>n***@gmail.com</strong>. The username is your public name; your Gmail address stays private.</p>
      <?php if ($errors): ?><div class="form-status error" role="alert">Please fix the field below.</div><?php endif; ?>
      <?= mock_form_open('choose-username', 'novalidate class="auth-form" data-validate') ?>
        <div class="form-input-group">
          <label for="username" class="auth-label">Username <small>your public name</small></label>
          <input type="text" id="username" name="username" class="form-input<?= invalid_class($errors, 'username') ?>" value="<?= e($old['username']) ?>" maxlength="50" required autocapitalize="none" spellcheck="false" data-pattern="username" data-label="Username" aria-describedby="gf-hint"<?= invalid_attr($errors, 'username', 'username') ?>>
          <p id="gf-hint" class="auth-hint">3 to 50 letters, numbers, dashes or underscores. Avoid your real name.</p>
          <?= field_error($errors, 'username', 'username') ?>
        </div>
        <label class="auth-check"><input type="checkbox" name="agree" value="1" required data-label="Agreement"><span>I look after my own Google account and any password I add later. <small>(The team can still help if I get locked out.)</small></span></label>
        <?= field_error($errors, 'agree', 'agree') ?>
        <button type="submit" class="form-button auth-submit">Create my account</button>
      </form>
      <div class="auth-alt"><p>Changed your mind? <a href="<?= url('home') ?>">Cancel</a> (nothing is saved)</p></div>
    </section>
  </div>
</main>
