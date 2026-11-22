<?php /* views/about.php - what the site is, roles, workflow, roster, sources. Ported from the real about view. */
defined('MOCK') || exit;
$groups = [];
foreach (['owner', 'admin', 'reviewer', 'contributor', 'member'] as $r) {
    $list = array_filter(rows('users'), fn ($u) => $u['role'] === $r && $u['status'] === 'ACTIVE');
    if ($list) $groups[role_title($r) . ($r === 'owner' ? '' : 's')] = $list;
}
$sources = [
    'Physics background' => [
        ['Sample reference: cosmic inflation (encyclopedia entry)', 'https://en.wikipedia.org/wiki/Inflation_(cosmology)', 'Placeholder citation that shows how a source line looks.'],
        ['Sample reference: multiverse (encyclopedia entry)', 'https://en.wikipedia.org/wiki/Multiverse', 'Placeholder citation for the levels on the physics side.'],
    ],
    'Speculative side' => [
        ['Sample reference: community worldbuilding wiki', 'https://example.com/', 'The real site credited the fan communities its ladders came from. Placeholder link.'],
    ],
];
?>
<main class="about-page max-w-5xl mx-auto pt-16 pb-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mb-10">
    <p class="kicker mb-2">About</p>
    <h1 class="page-title text-3xl sm:text-4xl font-bold">What <?= e(APP_NAME) ?> is</h1>
    <p class="text-gray-300 mt-3 max-w-3xl leading-relaxed"><?= e(APP_NAME) ?> is a <strong>peer-reviewed community wiki</strong> about ranked, nested structures:
      how one structure can sit inside a larger one, and so on up a ladder. It mixes two kinds of material and labels them clearly:
      <strong>physics</strong> and <strong>speculative</strong> worldbuilding. Members join under a username instead of a real name, write
      articles and notes, and review each other's work.</p>
  </header>

  <section class="about-block" aria-labelledby="roles-h">
    <h2 id="roles-h" class="about-h2">Roles</h2>
    <div class="about-roles">
      <div class="role-card"><span class="role-tag" style="--c:<?= role_color('member') ?>">Member</span><p>Every new account. Can read everything, post notes, ideas and field notes, upload media and buy cosmetics. Can ask for promotion.</p></div>
      <div class="role-card"><span class="role-tag" style="--c:<?= role_color('contributor') ?>">Contributor</span><p>Can also draft full articles with the step-by-step editor and upload a custom avatar.</p></div>
      <div class="role-card"><span class="role-tag" style="--c:<?= role_color('reviewer') ?>">Reviewer</span><p>Works the review queue, handles Contributor rank requests and publishes bulletins.</p></div>
      <div class="role-card"><span class="role-tag" style="--c:<?= role_color('admin') ?>">Admin</span><p>Full administration: users, ranks, suspensions, the support inbox and the audit log. Admin work publishes directly but earns no credits.</p></div>
      <div class="role-card is-owner"><span class="role-tag" style="--c:<?= role_color('owner') ?>">Owner</span><p>The site owner. The only one who can appoint or remove Admins, and the one who decides governance cases (never about themselves).</p></div>
    </div>
  </section>

  <section class="about-block" aria-labelledby="flow-h">
    <h2 id="flow-h" class="about-h2">How a contribution goes public</h2>
    <ol class="about-flow">
      <li><b>Submit.</b> Notes and media from any member, articles from Contributors and up. Everything starts as pending.</li>
      <li><b>Review.</b> A Reviewer or Admin approves it or sends it back with a note. The queue blocks anyone from approving their own submission.</li>
      <li><b>Publish.</b> Approved work appears in Explore, the gallery or the media page with the author's member chip underneath.</li>
      <li><b>Credit.</b> The author earns credits once per approved item. Self-published Admin work earns nothing, so credits always mean someone else checked the work.</li>
    </ol>
  </section>

  <section class="about-block" aria-labelledby="cred-h">
    <h2 id="cred-h" class="about-h2">Credits and identity</h2>
    <p class="text-gray-300 leading-relaxed">Credits are spent in the <a href="<?= url('store') ?>" class="text-indigo-300 hover:underline">cosmetics store</a> on name colours,
      name effects, avatar frames, profile banners, flairs and titles. Whatever you equip shows wherever your name appears. Every purchase is written to a
      ledger you can read, and your balance is always the sum of that ledger. Avatars are initials by default; the site asks for art or emblems, never a real photo.</p>
  </section>

  <section class="about-block" aria-labelledby="gov-h">
    <h2 id="gov-h" class="about-h2">Accountability</h2>
    <p class="text-gray-300 leading-relaxed">Rank changes, suspensions, approvals and purchases are written to an audit log. Members can file evidence-based complaints about an Admin,
      sign petitions and vote in confidence polls. Community support only triggers a review: the Owner makes the recorded decision. See
      <a href="<?= url('governance') ?>" class="text-indigo-300 hover:underline">Governance</a>.</p>
  </section>

  <section id="roster" class="about-block scroll-mt-20" aria-labelledby="roster-h">
    <h2 id="roster-h" class="about-h2">Roster</h2>
    <p class="text-sm text-gray-400 mb-4">Active members, listed by username only. (Mock accounts.)</p>
    <div class="roster">
      <?php foreach ($groups as $title => $list): ?>
        <div class="roster-group"><h3><?= e($title) ?> <span><?= count($list) ?></span></h3>
          <div class="roster-list"><?php foreach ($list as $u) echo member_chip($u, '', 'md'); ?></div></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="sources" class="about-block scroll-mt-20" aria-labelledby="src-h">
    <h2 id="src-h" class="about-h2">Research foundations</h2>
    <p class="text-sm text-gray-400 mb-5">Articles cite their own sources. The real site listed its main references here; these are placeholders.</p>
    <div class="src-groups">
      <?php foreach ($sources as $group => $items): ?>
        <div class="src-group"><h3><?= e($group) ?></h3><ul>
          <?php foreach ($items as [$t, $u, $d]): ?><li><a href="<?= e($u) ?>" target="_blank" rel="noopener" class="src-link"><?= e($t) ?></a><p><?= e($d) ?></p></li><?php endforeach; ?>
        </ul></div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="about-block text-sm text-gray-400" aria-labelledby="contact-h">
    <h2 id="contact-h" class="about-h2">Contact</h2>
    <p>Questions, corrections or takedown requests: use the <a href="<?= url('contact') ?>" class="text-indigo-300 hover:underline">contact form</a>. Messages go to the support inbox, which only Admins and the Owner can read.</p>
  </section>
</main>
