<?php /* views/profile.php - your own account settings. Markup reused from the real account.php profile section.
   Left out on purpose: QR code library, real avatar upload, real Google linking, real authenticator secrets. */
defined('MOCK') || exit;
$r = role_key(); $cardNo = 'MB-' . str_pad((string) $me['id'], 5, '0', STR_PAD_LEFT);
$appRequired = at_least('admin');
$sigils = [['Orbit', 210], ['Prism', 280], ['Spiral', 30], ['Lattice', 150], ['Nova', 330]];
?>
<main class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1 no-print">Account</p>
  <h1 class="text-3xl font-bold mb-8 page-title no-print">My profile</h1>
  <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
    <section class="lg:col-span-2" aria-labelledby="card-heading">
      <h2 id="card-heading" class="text-lg font-bold text-white mb-3 no-print">Member card</h2>
      <div class="member-card print-area" id="member-card">
        <div class="member-card-head"><span>COMMUNITY ARCHIVE</span><span><?= e($cardNo) ?></span></div>
        <div class="member-card-body">
          <?= avatar_html($me, 'lg') ?>
          <div class="min-w-0">
            <p class="member-card-label">Member</p><p class="member-card-name"><?= e($me['username']) ?></p>
            <p class="member-card-label mt-2">Rank</p><p class="member-card-value"><?= badge_html($r) ?></p>
            <p class="member-card-label mt-2">Joined</p><p class="member-card-value"><?= when($me['joined_min'], 'M j, Y') ?></p>
          </div>
          <div class="member-qr mock-qr" role="img" aria-label="Placeholder where the real site drew a QR code to the public profile"></div>
        </div>
        <p class="member-card-foot">The real card had a QR code to the public profile</p>
      </div>
      <p class="text-sm text-gray-400 mt-3 no-print text-center"><strong class="text-gray-100"><?= number_format((int) $me['credits']) ?></strong> credits &middot; <a href="<?= url('store') ?>" class="text-indigo-400 hover:underline">open the store</a></p>
      <div class="flex flex-wrap gap-3 mt-4 no-print">
        <button type="button" class="btn-outline" data-print>Print card</button>
        <a href="<?= url('member', ['u' => $me['username']]) ?>" class="btn-outline">View public page</a>
        <button type="button" class="btn-outline" data-share="<?= url('member', ['u' => $me['username']]) ?>" data-title="<?= e($me['username']) ?>">Share</button>
      </div>
    </section>

    <div class="lg:col-span-3 space-y-8 no-print">
      <section id="avatar" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6" aria-labelledby="av-heading">
        <h2 id="av-heading" class="text-lg font-bold text-white mb-1">Avatar</h2>
        <p class="text-sm text-gray-400 mb-4">Your avatar appears next to your username everywhere. The site is pseudonymous, so please <strong class="text-gray-200">do not upload photos of real people</strong>.</p>
        <?= mock_form_open('avatar', 'class="space-y-4"') ?>
          <div class="avatar-picker" role="radiogroup" aria-label="Avatar">
            <label class="avatar-option" title="Initials"><input type="radio" name="avatar_choice" value="initials" checked><?= avatar_html($me, 'md') ?><span class="ao-label">Initials</span></label>
            <?php foreach ($sigils as $i => [$n, $h]): ?><label class="avatar-option" title="<?= $n ?> sigil"><input type="radio" name="avatar_choice" value="sigil:<?= $i ?>"><span class="av av-md"><span class="av-disc" style="background:hsl(<?= $h ?> 45% 38%)"><?= nav_icon('star') ?></span></span><span class="ao-label"><?= $n ?></span></label><?php endforeach; ?>
            <label class="avatar-option<?= at_least('contributor') ? '' : ' is-locked' ?>" title="<?= at_least('contributor') ? 'Upload an image' : 'Unlocks at Contributor' ?>"><input type="radio" name="avatar_choice" value="upload" <?= at_least('contributor') ? '' : 'disabled' ?>><span class="av av-md"><span class="av-disc av-upload-slot"><?= nav_icon(at_least('contributor') ? 'plus' : 'shield') ?></span></span><span class="ao-label"><?= at_least('contributor') ? 'Upload' : 'Contributor+' ?></span></label>
          </div>
          <p class="text-xs text-gray-500">Custom uploads unlock at Contributor. The real server cropped the image and stripped hidden metadata such as GPS location.</p>
          <button type="submit" class="form-button">Save avatar</button>
        </form>
      </section>

      <section class="bg-slate-800/80 rounded-xl border border-slate-700 p-6" aria-labelledby="pf-heading">
        <h2 id="pf-heading" class="text-lg font-bold text-white mb-4">Public profile</h2>
        <?= mock_form_open('profile', 'novalidate data-validate class="space-y-4"') ?>
          <div class="form-input-group"><span class="block text-sm font-medium text-gray-300 mb-1">Username</span><p class="form-input opacity-70" aria-readonly="true"><?= e($me['username']) ?> <span class="text-xs text-gray-500">(cannot be changed)</span></p></div>
          <div class="form-input-group"><label for="bio" class="block text-sm font-medium text-gray-300 mb-1">Bio</label><textarea id="bio" name="bio" rows="4" maxlength="600" data-counter data-label="Bio" class="form-input" placeholder="What you study, a favourite article..."><?= e($me['bio']) ?></textarea></div>
          <button type="submit" class="form-button">Save profile</button>
        </form>
      </section>

      <section id="password" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6 scroll-mt-20" aria-labelledby="pw-heading">
        <h2 id="pw-heading" class="text-lg font-bold text-white mb-1">Change password</h2>
        <p class="text-sm text-gray-400 mb-4">You are responsible for keeping your password safe. Forgot it later? Use "Forgot password?" on the log-in page.</p>
        <?= mock_form_open('password', 'novalidate data-validate class="space-y-4"') ?>
          <div class="form-input-group"><label for="current_password" class="block text-sm font-medium text-gray-300 mb-1">Current password *</label><input type="password" id="current_password" name="current_password" required autocomplete="off" data-label="Current password" class="form-input"></div>
          <div class="form-input-group"><label for="new_password" class="block text-sm font-medium text-gray-300 mb-1">New password *</label><input type="password" id="new_password" name="new_password" required autocomplete="new-password" data-pattern="password" data-strength data-label="New password" class="form-input"></div>
          <div class="form-input-group"><label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Confirm new password *</label><input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" data-match="#new_password" data-label="Password confirmation" class="form-input"></div>
          <button type="submit" class="form-button">Update password</button>
        </form>
      </section>

      <section id="google" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6 scroll-mt-20" aria-labelledby="g-heading">
        <h2 id="g-heading" class="text-lg font-bold text-white mb-1">Google account <span class="twofa-state">Not linked</span></h2>
        <p class="text-sm text-gray-400 mb-4">Link your Google account to log in with one tap. Switched off in this mock (no client ID is included).</p>
        <div class="g-block g-off"><button type="button" class="g-fallback" disabled><span class="g-logo" aria-hidden="true"></span>Continue with Google</button></div>
      </section>

      <section id="email" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6 scroll-mt-20" aria-labelledby="em-heading">
        <h2 id="em-heading" class="text-lg font-bold text-white mb-1">Email notifications</h2>
        <p class="text-sm text-gray-400 mb-4">Sent to <?= e($me['email']) ?>. In the mock nothing is ever emailed; see the <?= at_least('admin') ? '<a href="' . url('mail-outbox') . '" class="text-indigo-400 hover:underline">email outbox</a>' : 'email outbox (Admin)' ?> for what would have been sent.</p>
        <?= mock_form_open('email_prefs', 'class="space-y-3"') ?>
          <?php foreach (['email_news' => ['Site news', 'New bulletins, newly published articles, notes and media.', true], 'email_personal' => ['About me and my work', 'Decisions on my submissions and rank requests, review tasks, governance notices.', true]] as $col => [$head, $desc, $on]): ?>
            <label class="auth-check email-pref"><input type="checkbox" name="<?= $col ?>" value="1" <?= $on ? 'checked' : '' ?>><span><strong><?= e($head) ?></strong><small><?= e($desc) ?></small></span></label>
          <?php endforeach; ?>
          <fieldset class="email-pref email-pref-group">
            <legend><strong>Article discussions</strong><small>Replies to me, chat under my own articles, and new posts by authors I follow. The bell always shows them; this only decides the emails.</small></legend>
            <?php foreach ([1 => ['Smart (recommended)', 'Replies to me right away. Other chat: one email per article, then quiet until I open it again.'], 2 => ['Every message', 'One email for each new message.'], 0 => ['Off', 'No discussion emails. I check the bell instead.']] as $v => [$head, $desc]): ?>
              <label class="auth-check email-pref-radio"><input type="radio" name="email_discussion" value="<?= $v ?>" <?= $v === 1 ? 'checked' : '' ?>><span><strong><?= e($head) ?></strong><small><?= e($desc) ?></small></span></label>
            <?php endforeach; ?>
          </fieldset>
          <p class="text-xs text-gray-500">Always sent, for your safety: sign-in codes, sign-in alerts and password emails. Every optional email has a one-click <a href="<?= url('unsubscribe') ?>" class="text-indigo-400 hover:underline">turn these emails off</a> link.</p>
          <button type="submit" class="form-button">Save email settings</button>
        </form>
      </section>

      <section id="two-step" class="bg-slate-800/80 rounded-xl border border-slate-700 p-6 scroll-mt-20" aria-labelledby="ts-heading">
        <h2 id="ts-heading" class="text-lg font-bold text-white mb-1">Authenticator app <span class="twofa-state<?= $appRequired ? ' is-on' : '' ?>"><?= $appRequired ? 'On' : 'Off' ?></span></h2>
        <p class="text-sm text-gray-400 mb-4"><?= $appRequired ? '<strong class="text-gray-200">Required for Admin and Owner accounts.</strong> At sign-in you choose one code: from this app, or by email.' : 'Optional for Members, Contributors and Reviewers. Sign in with a code from an app on your phone instead of waiting for an email.' ?></p>
        <?php if (!$appRequired): ?>
          <details class="twofa-demo">
            <summary class="form-button inline-flex w-auto cursor-pointer">Set up the authenticator app</summary>
            <ol class="twofa-steps mt-4"><li>Install any authenticator app.</li><li>Scan the QR code with the app, or type the key by hand.</li><li>Type the 6-digit code the app shows to finish.</li></ol>
            <div class="twofa-setup">
              <div class="twofa-qr mock-qr" role="img" aria-label="Placeholder QR code"></div>
              <div>
                <p class="text-xs text-gray-400">Key (sample, not a real secret)</p><p class="twofa-key"><code>MOCK DEMO KEYX 0000</code></p>
                <?= mock_form_open('twofa', 'class="mt-3 space-y-3" novalidate') ?><label for="twofa-code" class="block text-sm font-medium text-gray-300">Code from the app</label><input type="text" id="twofa-code" name="code" class="form-input code-input" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="123456"><button type="submit" class="form-button">Turn on</button></form>
              </div>
            </div>
          </details>
        <?php else: ?>
          <p class="text-sm text-gray-300 mb-4">Backup codes left: <strong>8</strong> of 10.</p>
          <div class="twofa-codes" role="note"><p class="font-semibold text-white mb-2">Sample backup codes (how the "shown only once" box looked)</p><ul><?php foreach (['ABCD-EFGH', 'JKLM-NPQR', 'STUV-WXYZ', 'MOCK-0001'] as $c): ?><li><code><?= $c ?></code></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
      </section>

      <section class="bg-slate-800/80 rounded-xl border border-slate-700 p-6 text-sm text-gray-400">
        <h2 class="text-lg font-bold text-white mb-2">Account</h2>
        <p>Email on file: <strong class="text-gray-200"><?= e($me['email']) ?></strong> (visible only to you and Admins).</p>
        <?php if (role_rank() <= 2): ?><p class="mt-2">Need a higher rank? <a href="<?= url('rank-request') ?>" class="text-indigo-400 hover:underline">Open the rank request page</a>.</p><?php endif; ?>
      </section>
    </div>
  </div>
</main>
