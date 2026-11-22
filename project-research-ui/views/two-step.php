<?php /* views/two-step.php - step 2 of 2 (one-time code). Markup reused from the real account.php two-step section. */
defined('MOCK') || exit;
?>
<main class="content-overlay auth-page">
  <div class="auth-shell auth-shell-single">
    <section class="auth-card ts-card" aria-labelledby="ts-h">
      <p class="auth-kicker">Step 2 of 2</p>
      <h1 id="ts-h" class="auth-title">Verify it&rsquo;s you</h1>
      <p class="auth-sub">Type the 6-digit code we "emailed" to <strong>n***@example.com</strong>. <strong>Mock:</strong> any 6 digits work, for example 123456.</p>
      <?php if ($errors): ?><div class="form-status error" role="alert"><?= e($errors['code'] ?? 'Check the code.') ?></div><?php endif; ?>
      <p class="code-status is-sending" role="status"><span class="code-status-dot" aria-hidden="true"></span><span>Code sent to n***@example.com at <?= date('H:i') ?>. It can take a minute to arrive.</span></p>
      <?= mock_form_open('two-step', 'novalidate class="auth-form" data-validate') ?>
        <div class="form-input-group">
          <label for="code" class="auth-label">Code from your email</label>
          <input type="text" id="code" name="code" class="form-input code-input<?= invalid_class($errors, 'code') ?>" required inputmode="numeric" autocomplete="one-time-code" maxlength="7" pattern="[0-9 ]{6,7}" data-pattern="code6" data-label="Code" spellcheck="false" placeholder="123456" aria-describedby="code-help"<?= invalid_attr($errors, 'code', 'code') ?>>
          <p id="code-help" class="text-xs text-gray-500 mt-1">Not there? Look in Spam or Promotions. The code works for 10 minutes.</p>
          <?= field_error($errors, 'code', 'code') ?>
        </div>
        <button type="submit" class="form-button auth-submit">Verify and log in</button>
      </form>
      <div class="auth-alt">
        <p>No email yet? <button type="button" class="link-button" data-mock-msg="Mock only: a new code would be emailed now.">Send a new code</button></p>
        <p>Not you? <a href="<?= url('login') ?>">Cancel and go back</a></p>
        <p>Wrong or old email address? <a href="<?= url('contact') ?>">Ask the team for help</a></p>
      </div>
    </section>
  </div>
</main>
