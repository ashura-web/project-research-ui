<?php /* views/unsubscribe.php - one-click email opt-out from an email footer. ?done shows the result. */
defined('MOCK') || exit;
$done = isset($_GET['done']); $label = 'Review results';
?>
<main class="content-overlay auth-page">
  <div class="auth-shell auth-shell-single">
    <section class="auth-card" aria-labelledby="un-h">
      <p class="auth-kicker">Email settings</p>
      <?php if ($done): ?>
        <h1 id="un-h" class="auth-title">Emails turned off</h1>
        <div class="form-status success" role="status">nova_07 will no longer get "<?= e($label) ?>" emails. The bell on the site still shows everything.</div>
        <p class="auth-hint">Changed your mind? Turn them back on any time in <a href="<?= url('profile') ?>#email">Profile &gt; Email notifications</a>. Sign-in codes and security alerts are always sent.</p>
      <?php else: ?>
        <h1 id="un-h" class="auth-title">Stop "<?= e($label) ?>" emails?</h1>
        <p class="auth-sub">For <strong>nova_07</strong>. Nothing else changes: other emails, sign-in codes and security alerts keep coming.</p>
        <a href="<?= url('unsubscribe', ['done' => 1]) ?>" class="form-button auth-submit no-underline text-center block">Turn these emails off</a>
        <p class="auth-hint"><a href="<?= url('profile') ?>#email">Choose emails one by one instead</a></p>
      <?php endif; ?>
    </section>
  </div>
</main>
