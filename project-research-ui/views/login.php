<?php /* views/login.php - log in (step 1 of 2). Markup reused from the real account.php. Any non-empty username/password passes. */
defined('MOCK') || exit;
$old += ['username' => '', 'password' => ''];
$recent = array_slice(published('articles'), 0, 3);
?>
<main class="content-overlay auth-page">
  <div class="auth-shell">
    <aside class="auth-side" aria-label="Recently added">
      <p class="auth-kicker">Welcome back</p>
      <h2 class="auth-side-title">Pick up where you left off.</h2>
      <p class="auth-side-text"><?= count(published('articles')) ?> reviewed articles in the archive, from measured cosmology to the speculative far end of the ladder.</p>
      <p class="auth-side-label">Recently published</p>
      <ul class="auth-recent">
        <?php foreach ($recent as $r): ?><li><a href="<?= url('article', ['slug' => $r['slug']]) ?>"><span class="ar-thumb" style="background:hsl(<?= crc32($r['slug']) % 360 ?> 35% 28%)"></span><span class="ar-text"><strong><?= e($r['title']) ?></strong><small><?= e(ago($r['created_min'])) ?></small></span></a></li><?php endforeach; ?>
      </ul>
      <p class="auth-side-foot">Reading is open to everyone. You only need an account to write, review or save a reading list.</p>
    </aside>
    <section class="auth-card" aria-labelledby="login-h">
      <h1 id="login-h" class="auth-title">Log in</h1>
      <p class="auth-sub">Use your username or email and password. <strong>Mock:</strong> any values work; nothing is checked against real accounts.</p>
      <?php if ($errors): ?><div class="form-status error" role="alert">Please fill in both fields.</div><?php endif; ?>
      <div class="g-block g-off"><button type="button" class="g-fallback" disabled aria-describedby="g-off-note"><span class="g-logo" aria-hidden="true"></span>Continue with Google</button><p id="g-off-note" class="auth-hint">Google sign-in is switched off in this research mock (no client ID is included). Use the form below.</p></div>
      <div class="auth-divider" role="separator"><span>or use your username</span></div>
      <?= mock_form_open('login', 'novalidate class="auth-form" data-validate') ?>
        <div class="form-input-group">
          <label for="username" class="auth-label">Username or email</label>
          <input type="text" id="username" name="username" class="form-input<?= invalid_class($errors, 'username') ?>" placeholder="e.g. nova_07" required autocomplete="username" autocapitalize="none" spellcheck="false" data-label="Username" value="<?= e($old['username']) ?>"<?= invalid_attr($errors, 'username', 'username') ?>>
          <?= field_error($errors, 'username', 'username') ?>
        </div>
        <div class="form-input-group">
          <div class="auth-label-row"><label for="password" class="auth-label">Password</label><a href="<?= url('forgot-password') ?>" class="auth-forgot">Forgot password?</a></div>
          <input type="password" id="password" name="password" class="form-input<?= invalid_class($errors, 'password') ?>" required autocomplete="current-password" data-label="Password" data-capslock<?= invalid_attr($errors, 'password', 'password') ?>>
          <p class="auth-caps" data-capslock-note hidden>Caps Lock is on.</p>
          <?= field_error($errors, 'password', 'password') ?>
        </div>
        <label class="auth-check"><input type="checkbox" name="remember" value="1"><span>Remember my username on this device <small>(never your password)</small></span></label>
        <button type="submit" class="form-button auth-submit">Log in</button>
      </form>
      <div class="auth-alt">
        <p>New here? <a href="<?= url('signup') ?>">Create a free account</a></p>
        <p>Still locked out? <a href="<?= url('contact') ?>">Contact the team</a></p>
      </div>
    </section>
  </div>
</main>
