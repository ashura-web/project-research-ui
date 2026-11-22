<?php /* views/contact.php - contact form. Markup reused from the real support.php (contact section). */
defined('MOCK') || exit;
$sentRef = isset($_GET['sent']) && preg_match('/^IC-MSG-\d{1,8}$/', (string) $_GET['sent']) ? (string) $_GET['sent'] : '';
$TYPES = ['ACCOUNT' => ['Account or login', 'Codes, passwords, sign-in trouble.', 'shield'], 'BUG' => ['Something is broken', 'A page, image or button misbehaves.', 'alert'],
          'CONTENT' => ['About an article', 'A mistake, a source, a report.', 'book'], 'OTHER' => ['Something else', 'Ideas, questions, anything.', 'chat']];
$isReceiver = at_least('admin');
$old += ['inquiry_type' => '', 'full_name' => '', 'email' => '', 'message' => ''];
$myMessages = $me ? where('support_messages', fn ($m) => $m['user_id'] === $me['id']) : [];
?>
<main class="content-overlay auth-page contact-page">
  <div class="auth-shell">
    <aside class="auth-side" aria-label="Before you write">
      <p class="auth-kicker">Contact the team</p>
      <h2 class="auth-side-title">The Admin team reads every message.</h2>
      <p class="auth-side-text">Messages land in the Support inbox. Only Admins and the Owner can open it; other members never see your message or your email.</p>
      <ol class="auth-steps">
        <li><span>1</span><div><strong>You send it</strong><small>You get a reference number straight away.</small></div></li>
        <li><span>2</span><div><strong>An Admin picks it up</strong><small>It is marked as read, then resolved, in the inbox.</small></div></li>
        <li><span>3</span><div><strong>You get a reply</strong><small><?= $me ? 'As a personal bulletin (bell icon) and in "Your messages" below.' : 'By email, to the address you give here.' ?></small></div></li>
      </ol>
      <div class="contact-quick">
        <p class="auth-side-label">Quick help, no message needed</p>
        <a href="<?= url('forgot-password') ?>"><?= nav_icon('shield') ?><span><strong>Forgot password?</strong><small>A reset link is emailed to the account address (mock).</small></span></a>
        <a href="<?= url('help') ?>"><?= nav_icon('book') ?><span><strong>Help centre</strong><small>Short answers to the most common questions.</small></span></a>
        <?php if ($me): ?><a href="<?= url('notifications') ?>"><?= nav_icon('bell') ?><span><strong>Bulletins</strong><small>Known issues and announcements.</small></span></a><?php endif; ?>
      </div>
      <p class="contact-duty">Your password is your responsibility. The team never asks for it and never sends it back to you.</p>
    </aside>

    <section class="auth-card" aria-labelledby="contact-h">
    <?php if ($isReceiver): ?>
      <h1 id="contact-h" class="auth-title">You are on the receiving end</h1>
      <p class="auth-sub">As <?= role_key() === 'owner' ? 'the Owner' : 'an Admin' ?>, you answer contact messages instead of sending them. Visitors and members write here; their messages arrive in your Support inbox.</p>
      <div class="receiver-panel"><?= nav_icon('inbox') ?><div><strong>Support inbox</strong><small>Read, reply to and resolve messages.</small></div><a href="<?= url('inbox') ?>" class="form-button">Open the inbox</a></div>
      <p class="auth-hint mt-4">Preview as a Member or Visitor (top banner) to see the form itself.</p>
    <?php else: ?>
      <h1 id="contact-h" class="auth-title">Send a message</h1>
      <p class="auth-sub">Tell us what happened and where. Links to the page help a lot.</p>
      <?php if ($sentRef !== ''): ?><div class="contact-sent" role="status"><strong>Thank you, your message was "sent".</strong><span>Your mock reference is <code><?= e($sentRef) ?></code>. Nothing was stored.</span></div><?php endif; ?>
      <?php if ($errors): ?><div class="form-status error" role="alert">Please fix <?= count($errors) ?> field<?= count($errors) === 1 ? '' : 's' ?> below.</div><?php endif; ?>
      <?= mock_form_open('contact', 'novalidate class="auth-form" data-validate data-draft="contact"') ?>
        <fieldset class="form-input-group">
          <legend class="auth-label"><span class="step-n">1</span> What is it about?</legend>
          <div class="topic-grid">
            <?php foreach ($TYPES as $k => [$label, $help, $icon]): ?>
              <label class="topic-option"><input type="radio" name="inquiry_type" value="<?= $k ?>" <?= $old['inquiry_type'] === $k ? 'checked' : '' ?> required><span class="to-body"><?= nav_icon($icon) ?><strong><?= e($label) ?></strong><small><?= e($help) ?></small></span></label>
            <?php endforeach; ?>
          </div>
          <?= field_error($errors, 'inquiry_type', 'inquiry_type') ?>
        </fieldset>
        <?php if ($me): ?>
          <div class="auth-row">
            <div class="form-input-group"><label for="full_name" class="auth-label"><span class="step-n">2</span> Username</label><input type="text" id="full_name" class="form-input is-locked" value="<?= e($me['username']) ?>" readonly></div>
            <div class="form-input-group"><label for="email" class="auth-label">Account email</label><input type="email" id="email" class="form-input is-locked" value="<?= e($me['email']) ?>" readonly></div>
          </div>
        <?php else: ?>
          <div class="auth-row">
            <div class="form-input-group">
              <label for="full_name" class="auth-label"><span class="step-n">2</span> Name or nickname</label>
              <input type="text" id="full_name" name="full_name" class="form-input<?= invalid_class($errors, 'full_name') ?>" required placeholder="e.g. Nova" autocomplete="nickname" data-pattern="callsign" data-label="Name" maxlength="50" value="<?= e($old['full_name']) ?>"<?= invalid_attr($errors, 'full_name', 'full_name') ?>>
              <?= field_error($errors, 'full_name', 'full_name') ?>
            </div>
            <div class="form-input-group">
              <label for="email" class="auth-label">Reply to</label>
              <input type="email" id="email" name="email" class="form-input<?= invalid_class($errors, 'email') ?>" required placeholder="you@example.com" autocomplete="email" data-label="Email" value="<?= e($old['email']) ?>"<?= invalid_attr($errors, 'email', 'email') ?>>
              <?= field_error($errors, 'email', 'email') ?>
            </div>
          </div>
        <?php endif; ?>
        <div class="form-input-group">
          <label for="message" class="auth-label"><span class="step-n">3</span> Message</label>
          <textarea id="message" name="message" rows="6" class="form-input<?= invalid_class($errors, 'message') ?>" placeholder="For example: the image on the Universe article does not load on my phone." required minlength="10" maxlength="5000" data-label="Message" data-counter<?= invalid_attr($errors, 'message', 'message') ?>><?= e($old['message']) ?></textarea>
          <?= field_error($errors, 'message', 'message') ?>
        </div>
        <div class="contact-foot"><p class="auth-hint"><span data-draft-status>Your draft is kept on this device until you send it.</span></p><button type="submit" class="form-button auth-submit">Send message</button></div>
        <p class="auth-hint">Mock: the server checks the fields (try sending it empty), then shows a reference. Nothing is stored or emailed.</p>
      </form>
    <?php endif; ?>
    </section>
  </div>
  <?php if ($myMessages && !$isReceiver): ?>
  <section class="my-msgs" aria-labelledby="mymsg-h">
    <h2 id="mymsg-h">Your messages</h2>
    <ul>
      <?php foreach ($myMessages as $mm): ?>
        <li class="my-msg">
          <div class="mm-head"><code>IC-MSG-<?= str_pad((string) $mm['id'], 4, '0', STR_PAD_LEFT) ?></code><span><?= e($TYPES[$mm['type']][0] ?? $mm['type']) ?></span><span class="mm-st mm-<?= strtolower($mm['status']) ?>"><?= $mm['reply'] ? 'ANSWERED' : e($mm['status']) ?></span><time><?= e(ago($mm['created_min'])) ?></time></div>
          <p class="mm-body"><?= e(short($mm['message'], 220)) ?></p>
          <?php if ($mm['reply']): $h = user((int) $mm['handler_id']); ?><div class="mm-reply"><strong>Reply from <?= e($h['username'] ?? 'the team') ?></strong><p><?= nl2br(e($mm['reply'])) ?></p></div><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>
</main>
