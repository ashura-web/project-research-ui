<?php /* views/signup.php - create an account. Markup reused from the real account.php signup section. */
defined('MOCK') || exit;
$old += ['username' => '', 'email' => '', 'goal' => 'member'];
?>
<main class="content-overlay auth-page">
  <div class="auth-shell">
    <aside class="auth-side" aria-label="How joining works">
      <p class="auth-kicker">Join the archive</p>
      <h2 class="auth-side-title">Write about the structures above our Universe.</h2>
      <p class="auth-side-text">An account is free and pseudonymous: people know you by your username, never your real name.</p>
      <ol class="auth-steps">
        <li><span>1</span><div><strong>Start as a Member</strong><small>Read everything, post notes, keep a reading list.</small></div></li>
        <li><span>2</span><div><strong>Build a record</strong><small>Every accepted contribution earns credits and counts as evidence for promotion.</small></div></li>
        <li><span>3</span><div><strong>Move up when ready</strong><small>Contributors write articles, Reviewers check them. Another member always checks your work.</small></div></li>
      </ol>
      <p class="auth-side-foot">Your email is only used to sign in and to answer you. It is never shown publicly.</p>
    </aside>
    <section class="auth-card" aria-labelledby="signup-h">
      <h1 id="signup-h" class="auth-title">Create your account</h1>
      <p class="auth-sub">Takes under a minute. <strong>Mock:</strong> the fields are validated on the server, then you are "signed in" as the demo Member.</p>
      <div class="g-block g-off"><button type="button" class="g-fallback" disabled aria-describedby="g-off-note"><span class="g-logo" aria-hidden="true"></span>Continue with Google</button><p id="g-off-note" class="auth-hint">Google sign-in is switched off in this research mock (no client ID is included). Use the form below.</p></div>
      <div class="auth-divider" role="separator"><span>or create one with a password</span></div>
      <?php if ($errors): ?><div class="form-status error" role="alert">Please fix <?= count($errors) ?> field<?= count($errors) === 1 ? '' : 's' ?> below.</div><?php endif; ?>
      <?= mock_form_open('signup', 'novalidate class="auth-form" data-validate') ?>
        <div class="form-input-group">
          <label for="username" class="auth-label">Username <small>your public name</small></label>
          <input type="text" id="username" name="username" class="form-input<?= invalid_class($errors, 'username') ?>" placeholder="e.g. nova_7" autocomplete="username" autocapitalize="none" spellcheck="false" data-pattern="username" data-label="Username" maxlength="50" aria-describedby="un-hint" value="<?= e($old['username']) ?>" required<?= invalid_attr($errors, 'username', 'username') ?>>
          <p id="un-hint" class="auth-hint">3 to 50 letters, numbers, dashes or underscores. Avoid your real name.</p>
          <?= field_error($errors, 'username', 'username') ?>
        </div>
        <div class="form-input-group">
          <label for="email" class="auth-label">Email <small>private</small></label>
          <input type="email" id="email" name="email" class="form-input<?= invalid_class($errors, 'email') ?>" placeholder="you@example.com" autocomplete="email" data-label="Email" value="<?= e($old['email']) ?>" required<?= invalid_attr($errors, 'email', 'email') ?>>
          <?= field_error($errors, 'email', 'email') ?>
        </div>
        <div class="auth-row">
          <div class="form-input-group">
            <label for="password" class="auth-label">Password</label>
            <input type="password" id="password" name="password" class="form-input<?= invalid_class($errors, 'password') ?>" placeholder="8+ characters" autocomplete="new-password" aria-describedby="pw-hint" data-pattern="password" data-label="Password" data-strength data-capslock required<?= invalid_attr($errors, 'password', 'password') ?>>
            <?= field_error($errors, 'password', 'password') ?>
          </div>
          <div class="form-input-group">
            <label for="password_confirm" class="auth-label">Repeat password</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-input<?= invalid_class($errors, 'password_confirm') ?>" autocomplete="new-password" data-match="#password" data-label="Password confirmation" required<?= invalid_attr($errors, 'password_confirm', 'password_confirm') ?>>
            <?= field_error($errors, 'password_confirm', 'password_confirm') ?>
          </div>
        </div>
        <p id="pw-hint" class="auth-hint">At least 8 characters with a letter and a number.</p>
        <p class="auth-caps" data-capslock-note hidden>Caps Lock is on.</p>
        <fieldset class="form-input-group auth-ranks">
          <legend class="auth-label">What would you like to do here?</legend>
          <?php foreach (['member' => ['Read and discuss', 'Start right away as a Member.'], 'contributor' => ['Write articles', 'Start as Member and ask to become a Contributor.'], 'reviewer' => ['Review others\' work', 'Start as Member and ask to become a Reviewer.']] as $lv => [$head, $desc]): ?>
            <label class="rank-option"><input type="radio" name="goal" value="<?= $lv ?>" <?= $old['goal'] === $lv ? 'checked' : '' ?>><span class="ro-body"><strong><?= e($head) ?></strong><small><?= e($desc) ?></small><em><?= e(role_title($lv)) ?></em></span></label>
          <?php endforeach; ?>
          <p class="auth-hint">Everyone starts as a Member. A higher choice becomes a rank request: Reviewers decide Contributor requests and Admins decide Reviewer requests.</p>
        </fieldset>
        <button type="submit" class="form-button auth-submit">Create account</button>
      </form>
      <div class="auth-alt"><p>Already have an account? <a href="<?= url('login') ?>">Log in</a></p></div>
    </section>
  </div>
</main>
