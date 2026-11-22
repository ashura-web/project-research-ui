<?php /* views/home.php - landing page. Layout ported from the real index view; all text is mock copy. */
defined('MOCK') || exit;
$pub = published('articles');
$ladder = rows('ladder');
$daily = $pub[(int) date('z') % count($pub)];
$latest = array_slice(where('articles', fn ($a) => $a['status'] === 'PUBLISHED'), 0, 6);
$posts = array_slice(where('notes', fn ($n) => $n['status'] === 'PUBLISHED'), 0, 3);
$active = count(array_filter(rows('users'), fn ($u) => $u['status'] === 'ACTIVE'));
?>
<main class="home">
  <div class="home-atmos" aria-hidden="true"><span class="ha-orb ha-orb-a"></span><span class="ha-orb ha-orb-b"></span><span class="ha-grid"></span></div>

  <section class="home-hero">
    <div class="home-hero-text">
      <p class="kicker mb-3"><span class="kicker-dot" aria-hidden="true"></span>Community wiki of ranked structures</p>
      <h1 class="home-title"><span class="ht-line"><span>Map every structure,</span></span><span class="ht-line"><span class="ht-accent">one rung at a time.</span></span></h1>
      <p class="home-lead"><?= e(APP_NAME) ?> is a reviewed community archive. Members write articles about nested structures, from
        real cosmology to clearly labelled worldbuilding. Every article is written by one member and checked by another before it goes public.</p>
      <form action="<?= url('explore') ?>" method="get" class="home-search" role="search">
        <?php if (!pretty_urls() && !defined('STATIC_EXPORT')): ?><input type="hidden" name="page" value="explore"><?php endif; ?>
        <label for="home-q" class="sr-only">Search</label><?= nav_icon('search') ?>
        <input id="home-q" name="q" type="search" placeholder="Search articles, e.g. universe" maxlength="100" autocomplete="off">
        <button type="submit">Search</button>
      </form>
      <div class="home-cta">
        <a href="<?= url('gallery') ?>" class="form-button w-auto">Open the gallery</a>
        <?php if (at_least('contributor')): ?><a href="<?= url('contribute') ?>" class="btn-outline">Write an article</a>
        <?php elseif (signed_in()): ?><a href="<?= url('write-note') ?>" class="btn-outline">Write a note</a>
        <?php else: ?><a href="<?= url('signup') ?>" class="btn-outline">Create an account</a><?php endif; ?>
      </div>
      <ul class="home-trust">
        <li><?= nav_icon('check') ?><span>Reviewed by another member</span></li>
        <li><?= nav_icon('shield') ?><span>Usernames, never real names</span></li>
        <li><?= nav_icon('credits') ?><span>Credits for approved work</span></li>
      </ul>
    </div>
    <nav class="home-ladder" aria-label="The ladder" data-ladder>
      <p class="home-ladder-head"><b>The ladder</b> <span>Each rung contains the one below it.</span> <a href="<?= url('glossary') ?>#ladder">See the full chain &rarr;</a></p>
      <ol>
        <?php foreach (array_reverse($ladder, true) as $i => $r): ?>
          <li style="--i:<?= (int) $i ?>"><a href="<?= url('article', ['slug' => $r['slug']]) ?>"><span class="hl-num" title="Rung <?= $r['rung'] ?>, class <?= $r['cls'] ?>"><?= str_pad((string) $r['rung'], 2, '0', STR_PAD_LEFT) ?></span><span class="hl-name"><?= e($r['name']) ?></span></a></li>
        <?php endforeach; ?>
      </ol>
    </nav>
    <a class="home-scroll" href="#home-numbers" tabindex="-1" aria-hidden="true"><span>Scroll</span><i></i></a>
  </section>

  <div class="home-inner">
    <section class="home-stats" id="home-numbers" aria-label="The archive in numbers">
      <a href="<?= url('explore') ?>"><b data-countup="<?= count($pub) ?>"><?= count($pub) ?></b><span>Published articles</span></a>
      <a href="<?= url('explore', ['tab' => 'notes']) ?>"><b data-countup="<?= count(published('notes')) ?>"><?= count(published('notes')) ?></b><span>Community notes</span></a>
      <a href="<?= url('media') ?>"><b data-countup="<?= count(published('media')) ?>"><?= count(published('media')) ?></b><span>Media uploads</span></a>
      <a href="<?= url('about') ?>#roster"><b data-countup="<?= $active ?>"><?= $active ?></b><span>Active members</span></a>
    </section>

    <section class="home-daily" aria-labelledby="daily-h">
      <a href="<?= url('article', ['slug' => $daily['slug']]) ?>" class="hd-media" tabindex="-1" aria-hidden="true"><?= cover($daily['title'], crc32($daily['slug']) % 360, 'hd-cover') ?></a>
      <div class="hd-body">
        <p class="kicker mb-1">Article of the day &middot; <?= date('M j') ?></p>
        <h2 id="daily-h"><a href="<?= url('article', ['slug' => $daily['slug']]) ?>"><?= e($daily['title']) ?></a></h2>
        <p class="hd-sum"><?= e($daily['summary']) ?></p>
        <div class="hd-score"><span class="hd-hz-label">Score</span><span class="hd-hz-track"><i style="width:<?= $daily['score'] * 10 ?>%"></i></span><b><?= $daily['score'] ?><span>/10</span></b></div>
        <p class="hd-meta">Level <?= e($daily['level']) ?> &middot; Class <?= $daily['cls'] ?> &middot; <?= e(category_name($daily['category_id'])) ?></p>
        <div class="hd-actions">
          <a href="<?= url('article', ['slug' => $daily['slug']]) ?>" class="form-button w-auto">Read it</a>
          <a href="<?= url('random') ?>" class="btn-outline" data-surprise><?= nav_icon('shuffle') ?> Surprise me</a>
        </div>
      </div>
    </section>

    <section class="home-section" aria-labelledby="latest-h">
      <div class="home-section-head"><h2 id="latest-h">Latest articles</h2><a href="<?= url('explore') ?>" class="text-sm text-indigo-300 hover:underline">Browse all &rarr;</a></div>
      <div class="home-cards" data-cards>
        <?php foreach ($latest as $i => $d): ?>
          <article class="home-card">
            <span class="hc-index" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <a href="<?= url('article', ['slug' => $d['slug']]) ?>" class="home-card-link">
              <?= cover($d['title'], crc32($d['slug']) % 360, 'home-card-cover') ?>
              <span class="home-card-body">
                <span class="home-card-meta">Level <?= e($d['level']) ?> &middot; Score <?= $d['score'] ?>/10</span>
                <span class="home-card-title"><?= e($d['title']) ?></span>
                <span class="home-card-text"><?= e(short($d['summary'], 120)) ?></span>
              </span>
            </a>
            <div class="home-card-foot"><?= member_chip(user($d['author_id']), 'Written by', 'xs') ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <div class="home-marquee" aria-hidden="true"><div class="hm-track">
      <?php for ($m = 0; $m < 2; $m++): ?><span><?= e(APP_NAME) ?></span><i>&bull;</i><span>Archive of ranked structures</span><i>&bull;</i><span>Reviewed by members</span><i>&bull;</i><?php endfor; ?>
    </div></div>

    <section class="home-section" aria-labelledby="how-h">
      <div class="home-section-head"><h2 id="how-h">How the archive works</h2><a href="<?= url('about') ?>" class="text-sm text-indigo-300 hover:underline">Full workflow &rarr;</a></div>
      <ol class="home-steps" data-steps>
        <li><span class="hs-num">1</span><h3>Join as a Member</h3><p>Sign up with a username, not your real name. Members can read everything and post notes, ideas and media.</p></li>
        <li><span class="hs-num">2</span><h3>Submit for review</h3><p>Contributors and above can draft full articles. Every submission waits in the review queue for a Reviewer or an Admin.</p></li>
        <li><span class="hs-num">3</span><h3>Someone else approves</h3><p>Nobody can approve their own work. Approved work goes public with your member chip underneath it.</p></li>
        <li><span class="hs-num">4</span><h3>Earn credits</h3><p>Each approved item earns credits once. Spend them on name colours, frames, banners and flairs.</p></li>
      </ol>
    </section>

    <section class="home-section home-about" aria-labelledby="about-h">
      <div class="ha-col">
        <p class="kicker mb-2">What is <?= e(APP_NAME) ?>?</p>
        <h2 id="about-h">A reviewed wiki with ranks, credits and community oversight</h2>
        <p>The site is a <strong>community knowledge archive</strong>. Each structure has its own <strong>article</strong> with a summary,
          a 1-10 score, its place on the ladder and the sources it is based on.</p>
        <p>The archive mixes two kinds of material and always says which is which: <strong>physics</strong> (ideas taken seriously by
          scientists) and <strong>speculative</strong> worldbuilding. In this mock every article is placeholder text.</p>
      </div>
      <ul class="ha-facts">
        <li><b>Reviewed</b><span>Nothing goes public until a second member has checked it.</span></li>
        <li><b>Anonymous</b><span>You are known by a username. Real names are never asked for or shown.</span></li>
        <li><b>Accountable</b><span>Members can file complaints, sign petitions and vote in confidence polls about the Admin team.</span></li>
        <li><b>Rewarding</b><span>Approved work earns credits, spent on cosmetics that appear wherever your name does.</span></li>
      </ul>
    </section>

    <section class="home-section" aria-labelledby="explore-h">
      <div class="home-section-head"><h2 id="explore-h">Where to start</h2></div>
      <div class="home-explore">
        <a href="<?= url('gallery') ?>"><?= nav_icon('image') ?><b>Gallery</b><span>Every published article as a card, filtered by level, class and score.</span></a>
        <a href="<?= url('explore') ?>"><?= nav_icon('search') ?><b>Explore</b><span>Search over articles, notes and media, with sorting and filters.</span></a>
        <a href="<?= url('glossary') ?>"><?= nav_icon('book') ?><b>Glossary</b><span>The full ladder, the terms and the rules that decide where each structure sits.</span></a>
        <a href="<?= url('media') ?>"><?= nav_icon('video') ?><b>Media</b><span>Videos, images and audio shared by members.</span></a>
        <a href="<?= url('help') ?>"><?= nav_icon('info') ?><b>Help centre</b><span>Short answers about ranks, reviews, credits, privacy and your account.</span></a>
        <a href="<?= url('governance') ?>"><?= nav_icon('scales') ?><b>Governance</b><span>How members hold the Admin team to account.</span></a>
      </div>
    </section>

    <section class="home-section" aria-labelledby="posts-h">
      <div class="home-section-head"><h2 id="posts-h">From the community</h2><a href="<?= url('explore', ['tab' => 'notes']) ?>" class="text-sm text-indigo-300 hover:underline">All notes &rarr;</a></div>
      <div class="home-posts">
        <?php foreach ($posts as $t): ?>
          <article class="home-post">
            <p class="hp-type"><?= e(ucwords(strtolower(str_replace('_', ' ', $t['type'])))) ?> &middot; <?= e(ago($t['created_min'])) ?></p>
            <h3><a href="<?= url('note', ['id' => $t['id']]) ?>"><?= e($t['title']) ?></a></h3>
            <p class="hp-text"><?= e(short($t['body'], 170)) ?></p>
            <div class="hp-foot"><?= member_chip(user($t['author_id']), 'Posted by', 'xs') ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="home-section" aria-labelledby="ranks-h">
      <div class="home-section-head"><h2 id="ranks-h">Ranks</h2><a href="<?= url('help') ?>" class="text-sm text-indigo-300 hover:underline">How to rank up &rarr;</a></div>
      <p class="home-section-lead">Everyone starts as a Member. Each rank unlocks more of the workflow. Finish the tasks for the next rank on
        your dashboard, then send a rank request; a Reviewer or Admin checks your record and decides. Admin seats are decided by the Owner.</p>
      <ol class="home-ranks">
        <?php foreach (['member', 'contributor', 'reviewer', 'admin'] as $r): ?><li><b><?= role_title($r) ?></b><span><?= e(ROLE_UNLOCKS[$r]) ?></span></li><?php endforeach; ?>
      </ol>
    </section>

    <section class="home-section home-split">
      <a href="<?= url('gallery') ?>" class="home-feature"><?= cover('Levels I-IV', 220) ?><span><b>The four levels</b><small>The physics side of the ladder, from regions beyond our horizon to the mathematical ensemble.</small></span></a>
      <a href="<?= url('media') ?>" class="home-feature"><?= cover('Media', 300) ?><span><b>Media library</b><small>Videos, images and clips uploaded by members.</small></span></a>
    </section>

    <section class="home-cta-band" aria-labelledby="cta-h">
      <div>
        <h2 id="cta-h"><?= signed_in() ? 'Pick up where you left off' : 'Ready to add a structure?' ?></h2>
        <p><?= signed_in() ? 'Your dashboard shows your submissions, your rank tasks and your credits.' : 'Sign up with a username in under a minute. No real name, no payment, just an email for your sign-in codes.' ?></p>
      </div>
      <a href="<?= url(signed_in() ? 'dashboard' : 'signup') ?>" class="form-button w-auto"><?= signed_in() ? 'Open dashboard' : 'Create an account' ?></a>
    </section>
  </div>
</main>
