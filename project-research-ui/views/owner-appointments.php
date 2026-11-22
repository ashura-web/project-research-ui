<?php /* views/owner-appointments.php - Admin appointments: appoint, remove, succession + timeline.
   Markup reused from governance.php (custodian-council view, .arc-* styles). Every form needs a reason and a tick box. */
defined('MOCK') || exit;
$admins = where('users', fn ($u) => in_array($u['role'], ['admin', 'owner'], true));
$candidates = where('users', fn ($u) => $u['role'] === 'reviewer' && $u['status'] === 'ACTIVE');
$sel = function (string $id, array $list, string $empty) use ($me): string {
    $o = '<option value="">' . e($empty) . '</option>';
    foreach ($list as $c) if ($c['id'] !== $me['id']) $o .= '<option value="' . $c['id'] . '">' . e($c['username']) . ' &middot; ' . role_title($c['role']) . '</option>';
    return '<select id="' . $id . '" name="target" class="form-input" required data-label="Member">' . $o . '</select>';
};
$err = fn (string $a) => ($errors && ($old['mock_action'] ?? '') === $a) ? '<div class="form-status error" role="alert">' . e(implode(' ', $errors)) . '</div>' : '';
$history = [
    ['founding', 'gold', null, 'owner_ada', 288000, 'Founding owner of the site.', null],
    ['appoint', 'blue', null, 'admin_kai', 250000, 'Runs the support inbox on weekdays.', 'owner_ada'],
    ['appoint', 'blue', null, 'admin_rhea', 231000, 'Audit log and email outbox duty.', 'owner_ada'],
    ['remove', 'amber', null, 'old_admin', 120000, 'Stepped down at their own request.', 'owner_ada'],
];
$labels = ['founding' => 'Founding', 'succession' => 'Succession', 'appoint' => 'Appointed', 'remove' => 'Removed'];
?>
<main class="arc-page max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="arc-hero" data-arc-reveal>
    <div class="arc-hero-glow" aria-hidden="true"></div>
    <div class="arc-hero-main">
      <p class="kicker mb-1"><a href="<?= url('owner') ?>" class="hover:underline">Owner console</a></p>
      <h1 class="arc-title page-title">Admin appointments</h1>
      <p class="arc-lead">Seat new Admins, step one down, or hand the Owner appointment to a successor. Every change needs a reason and a confirmation, and appears in the timeline below.</p>
    </div>
    <dl class="arc-stats">
      <div class="arc-stat is-blue"><dt>Admin seats</dt><dd><?= count($admins) - 1 ?></dd></div>
      <div class="arc-stat is-teal"><dt>Eligible reviewers</dt><dd><?= count($candidates) ?></dd></div>
      <div class="arc-stat is-gold"><dt>Owner</dt><dd><?= e($me['username']) ?></dd></div>
    </dl>
    <ul class="arc-roster" aria-label="Current Admin team"><?php foreach ($admins as $c): ?><li class="<?= $c['id'] === $me['id'] ? 'is-me' : '' ?>"><?= avatar_html($c, 'sm') ?><span><?= e($c['username']) ?></span><?= $c['id'] === $me['id'] ? '<small>Owner</small>' : '' ?></li><?php endforeach; ?></ul>
  </header>
  <div class="arc-grid" data-stagger>
    <section class="arc-card is-blue" aria-labelledby="arc-appoint-h" data-arc-reveal>
      <div class="arc-card-head"><span class="arc-icon" aria-hidden="true"><?= nav_icon('userplus') ?></span><div><h2 id="arc-appoint-h">Appoint an Admin</h2><p>Give an active Reviewer an Admin seat. The choice and the reason are yours.</p></div></div>
      <?= $err('appoint') ?>
      <?= mock_form_open('appoint', 'class="arc-form" data-validate novalidate data-confirm="Appoint this member to the Admin team?"') ?>
        <label for="appoint-target">Member</label><?= $sel('appoint-target', $candidates, $candidates ? 'Choose a member' : 'No eligible members') ?>
        <label for="appoint-reason">Reason <small>(kept on record)</small></label><textarea id="appoint-reason" name="reason" class="form-input" rows="2" required minlength="10" maxlength="500" data-label="Reason" placeholder="Why this member, now?"></textarea>
        <label class="arc-confirm"><input type="checkbox" name="confirm" value="1" required data-label="Confirmation"> I confirm this appointment</label>
        <button type="submit" class="arc-btn">Appoint to Admin team</button>
      </form>
    </section>
    <section class="arc-card is-amber" aria-labelledby="arc-remove-h" data-arc-reveal>
      <div class="arc-card-head"><span class="arc-icon" aria-hidden="true"><?= nav_icon('exit') ?></span><div><h2 id="arc-remove-h">End an Admin seat</h2><p>The member returns to Reviewer. For misconduct, open a governance case instead.</p></div></div>
      <?= $err('remove') ?>
      <?= mock_form_open('remove', 'class="arc-form" data-validate novalidate data-confirm="Remove this member from the Admin team?" data-confirm-tone="danger"') ?>
        <label for="remove-target">Admin</label><?= $sel('remove-target', $admins, 'Choose an Admin') ?>
        <label for="remove-reason">Reason <small>(kept on record)</small></label><textarea id="remove-reason" name="reason" class="form-input" rows="2" required minlength="10" maxlength="500" data-label="Reason" placeholder="Why is this seat ending?"></textarea>
        <label class="arc-confirm"><input type="checkbox" name="confirm" value="1" required data-label="Confirmation"> I confirm this removal</label>
        <button type="submit" class="arc-btn">Remove from Admin team</button>
      </form>
    </section>
    <section class="arc-card is-crimson arc-wide" aria-labelledby="arc-transfer-h" data-arc-reveal>
      <div class="arc-card-head"><span class="arc-icon" aria-hidden="true"><?= nav_icon('succession') ?></span><div><h2 id="arc-transfer-h">Succession <span class="arc-tag">Irreversible</span></h2><p>Hand the Owner appointment to an active Admin. You lose Owner authority the moment it completes.</p></div></div>
      <?= $err('transfer') ?>
      <?= mock_form_open('transfer', 'class="arc-form arc-form-row" data-validate novalidate data-confirm="Transfer the Owner appointment? You will lose Owner authority." data-confirm-tone="danger"') ?>
        <div><label for="transfer-target">Successor</label><?= $sel('transfer-target', $admins, 'Choose an Admin') ?></div>
        <div><label for="transfer-reason">Reason <small>(kept on record)</small></label><input type="text" id="transfer-reason" name="reason" class="form-input" required minlength="10" maxlength="500" data-label="Reason" placeholder="Why is the appointment changing?"></div>
        <label class="arc-confirm"><input type="checkbox" name="confirm" value="1" required data-label="Confirmation"> I understand this transfers my own authority</label>
        <button type="submit" class="arc-btn">Transfer the appointment</button>
      </form>
    </section>
  </div>
  <section class="arc-timeline-wrap" aria-labelledby="arc-history-h" data-arc-reveal>
    <h2 id="arc-history-h" class="arc-section-title">Appointment timeline</h2>
    <ol class="arc-timeline">
      <?php foreach ($history as [$kind, $tone, $prev, $new, $min, $why, $by]): ?>
        <li class="arc-event is-<?= $tone ?>"><span class="arc-dot" aria-hidden="true"></span><div class="arc-event-body">
          <p class="arc-event-top"><span class="arc-pill is-<?= $tone ?>"><?= $labels[$kind] ?></span> <strong><?= $prev ? e($prev) . ' &rarr; ' : '' ?><?= e($new) ?></strong> <time><?= when($min, 'M j, Y \&\m\i\d\d\o\t\; H:i') ?></time></p>
          <p class="arc-event-why"><?= e($why) ?></p><?php if ($by): ?><p class="arc-event-by">by <?= e($by) ?></p><?php endif; ?>
        </div></li>
      <?php endforeach; ?>
    </ol>
  </section>
</main>
