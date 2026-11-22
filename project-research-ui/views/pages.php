<?php /* views/pages.php - research index: every page of the mock, the rank it needs and its wireframe number. */
defined('MOCK') || exit;
$wire = ['home' => '01', 'about' => '02', 'gallery' => '03', 'explore' => '04', 'article' => '05', 'note' => '06', 'media' => '07', 'help' => '08', 'glossary' => '09',
         'member' => '10', 'contact' => '11', 'login' => '12', 'signup' => '13', 'two-step' => '14', 'forgot-password' => '15', '404' => '16', 'dashboard' => '17',
         'contribute' => '18', 'write-note' => '19', 'media-submit' => '20', 'profile' => '21', 'notifications' => '22', 'rank-request' => '23', 'store' => '24',
         'bulletins' => '25', 'review-queue' => '26', 'requests' => '27', 'governance' => '28', 'governance-submit' => '29', 'governance-case' => '30', 'petitions' => '31',
         'petition' => '32', 'poll' => '33', 'admin' => '34', 'users' => '35', 'inbox' => '36', 'audit-log' => '37', 'mail-outbox' => '38', 'owner' => '39',
         'owner-appointments' => '40', 'content' => '41'];
$sections = [
    'Public pages' => ['home', 'about', 'explore', 'gallery', 'glossary', 'help', 'media', 'article', 'note', 'member', 'contact', 'random'],
    'Account' => ['login', 'two-step', 'signup', 'choose-username', 'forgot-password', 'reset-password', 'unsubscribe'],
    'Member workspace' => ['dashboard', 'write-note', 'media-submit', 'contribute', 'profile', 'store', 'rank-request', 'notifications'],
    'Governance' => ['governance', 'governance-submit', 'governance-case', 'petitions', 'petition', 'poll'],
    'Reviewer' => ['review-queue', 'requests', 'bulletins'],
    'Admin' => ['admin', 'users', 'inbox', 'audit-log', 'mail-outbox'],
    'Owner' => ['owner', 'owner-appointments', 'content', 'setup'],
    'Errors' => ['404', 'denied'],
];
$q = ['article' => ['slug' => 'universe'], 'note' => ['id' => 1], 'member' => ['u' => 'nova_07'], 'governance-case' => ['id' => 1], 'petition' => ['id' => 1], 'poll' => ['id' => 1]];
?>
<main class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mb-8">
    <p class="kicker mb-2">Research index</p>
    <h1 class="page-title text-3xl sm:text-4xl font-bold">All pages</h1>
    <p class="text-gray-400 mt-2 max-w-3xl"><?= count(PAGES) ?> screens of a <strong class="text-gray-200">peer-reviewed, rank-based community wiki</strong>. Every page is UI only: the rows are mock data and no form saves anything.
      Pages above your preview rank show the 403 panel; switch rank with <strong class="text-gray-200">Preview as</strong> in the top banner. You are previewing as <?= badge_html(role_key()) ?>.</p>
  </header>
  <?php foreach ($sections as $title => $slugs): ?>
    <section class="mb-8" aria-labelledby="pg-<?= md5($title) ?>">
      <h2 id="pg-<?= md5($title) ?>" class="text-lg font-bold text-violet-300 mb-3"><?= e($title) ?></h2>
      <div class="overflow-x-auto"><table class="data-table m-stack w-full text-sm">
        <thead><tr><th scope="col">Page</th><th scope="col">Address</th><th scope="col">Lowest rank</th><th scope="col">Wireframe</th><th scope="col">Open</th></tr></thead>
        <tbody>
          <?php foreach ($slugs as $s): [$t, $min] = PAGES[$s]; $ok = at_least($min); ?>
            <tr><td data-label="Page"><strong class="text-white"><?= e($t) ?></strong></td><td data-label="Address"><code class="text-xs text-gray-400"><?= e(html_entity_decode(url($s, $q[$s] ?? []))) ?></code></td>
              <td data-label="Lowest rank"><?= badge_html($min) ?></td><td data-label="Wireframe"><?= isset($wire[$s]) ? 'No. ' . $wire[$s] : '<span class="text-gray-500">new</span>' ?></td>
              <td data-label="Open"><a href="<?= url($s, $q[$s] ?? []) ?>" class="text-indigo-300 hover:underline"><?= $ok ? 'Open' : 'Open (403 for you)' ?></a></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
    </section>
  <?php endforeach; ?>
</main>
