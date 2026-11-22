<?php /* views/media.php - "now playing" stage + searchable media grid + lightbox. Ported from the real media view.
   No YouTube embeds or external thumbnails: the player is a placeholder so the mock never calls third parties. */
defined('MOCK') || exit;
$items = published('media');
$videos = array_values(array_filter($items, fn ($m) => $m['type'] === 'VIDEO'));
$first = $videos[0] ?? null;
$count = fn ($t) => count(array_filter($items, fn ($m) => $m['type'] === $t));
$playlist = [
    ['Explainer video (embed placeholder)', 'A curated channel', 280],
    ['How big is the ladder? (placeholder)', 'A curated channel', 200],
    ['Member walkthrough (placeholder)', 'vale_k', 40],
];
?>
<main class="media-page mx-auto pt-14 pb-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <header class="mx-head">
    <div class="mx-head-text">
      <p class="kicker mb-1">Media library</p>
      <h1 class="page-title text-3xl sm:text-4xl font-bold">Watch, look, listen</h1>
      <p class="mx-sub"><?= count($items) ?> items &middot; <?= count($playlist) ?> curated videos &middot; uploads from <?= count(array_unique(array_column($items, 'author_id'))) ?> members</p>
    </div>
    <a href="<?= url(signed_in() ? 'media-submit' : 'login') ?>" class="form-button mx-upload"><?= nav_icon('plus') ?><span><?= signed_in() ? 'Upload media' : 'Log in to upload' ?></span></a>
  </header>

  <?php if ($first): ?>
  <section class="mx-stage" aria-label="Video player">
    <div class="mx-stage-main">
      <div class="player-frame" data-player>
        <button type="button" class="player-poster" data-play data-mock-msg="The real site loads a privacy-friendly YouTube embed here. The mock does not contact YouTube." aria-label="Play: <?= e($first['title']) ?>">
          <?= cover('Video placeholder', $first['hue'], 'mx-poster') ?>
          <span class="play-btn" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span>
        </button>
      </div>
      <div class="mx-now">
        <h2 class="mx-now-title" data-player-title><?= e($first['title']) ?></h2>
        <div class="mx-channel">
          <span class="mx-ball mx-ball-ch" aria-hidden="true">A</span>
          <div class="mx-channel-name"><strong data-player-credit>A curated channel</strong><small data-player-src>Curated &middot; video host</small></div>
          <a class="mx-yt-btn" href="#" data-mock-msg="External link disabled in the mock."><?= nav_icon('link') ?><span>Open on the video site</span></a>
        </div>
        <div class="mx-descbox"><p class="mx-now-label">Now playing</p><p class="mx-now-note" data-player-note><?= e($first['caption']) ?></p></div>
      </div>
    </div>
    <div class="mx-upnext">
      <h2 class="mx-upnext-h">Up next <span><?= count($playlist) ?> videos</span></h2>
      <ol class="playlist" aria-label="Playlist">
        <?php foreach ($playlist as $i => [$t, $by, $hue]): ?>
          <li><button type="button" class="playlist-item<?= $i === 0 ? ' is-current' : '' ?>" data-pl-title="<?= e($t) ?>" data-pl-credit="<?= e($by) ?>" data-pl-note="Mock description for &quot;<?= e($t) ?>&quot;." aria-current="<?= $i === 0 ? 'true' : 'false' ?>">
            <span class="pl-thumb"><?= cover('', $hue) ?><span class="pl-num"><?= $i + 1 ?></span></span>
            <span class="pl-text"><span class="pl-title"><?= e($t) ?></span><span class="pl-by"><?= e($by) ?></span></span>
          </button></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>
  <?php endif; ?>

  <section id="community-uploads" class="mx-library scroll-mt-20" aria-labelledby="lib-h">
    <h2 id="lib-h" class="sr-only">All media</h2>
    <div class="mx-toolbar" data-media-tools>
      <div class="mx-search"><?= nav_icon('search') ?><label for="mx-q" class="sr-only">Search media</label><input type="search" id="mx-q" placeholder="Search titles, descriptions, members..." data-media-q autocomplete="off"></div>
      <div class="mx-chips" role="group" aria-label="Filter by type" data-media-filter>
        <?php foreach (['all' => ['All', count($items)], 'VIDEO' => ['Videos', $count('VIDEO')], 'IMAGE' => ['Images', $count('IMAGE')], 'AUDIO' => ['Audio', $count('AUDIO')]] as $k => [$label, $n]): ?>
          <button type="button" class="chip<?= $k === 'all' ? ' is-active' : '' ?>" data-filter="<?= $k ?>" aria-pressed="<?= $k === 'all' ? 'true' : 'false' ?>"><?= $label ?> <span class="chip-count"><?= $n ?></span></button>
        <?php endforeach; ?>
      </div>
      <div class="mx-sort"><label for="mx-sort" class="sr-only">Sort</label>
        <select id="mx-sort" class="form-input" data-media-sort><option value="default">Featured first</option><option value="new">Newest</option><option value="old">Oldest</option><option value="az">Title A&ndash;Z</option></select></div>
    </div>
    <p class="mx-status" data-media-status aria-live="polite"></p>
    <div class="mx-grid" data-media-grid>
      <?php foreach ($items as $n => $it): $u = user($it['author_id']); ?>
        <article class="mx-card" id="media-<?= $it['id'] ?>" data-type="<?= $it['type'] ?>" data-order="<?= $n ?>" data-date="<?= time() - $it['created_min'] * 60 ?>" data-title="<?= e(strtolower($it['title'])) ?>" data-search="<?= e(strtolower($it['title'] . ' ' . $it['caption'] . ' ' . $u['username'])) ?>">
          <?php if ($it['type'] === 'IMAGE'): ?>
            <button type="button" class="mx-thumb" data-lightbox data-lightbox-hue="<?= $it['hue'] ?>" data-lightbox-alt="<?= e($it['caption']) ?>" aria-label="View image: <?= e($it['title']) ?>"><?= cover($it['title'], $it['hue']) ?><span class="mx-badge t-image">Image</span></button>
          <?php elseif ($it['type'] === 'VIDEO'): ?>
            <button type="button" class="mx-thumb is-video" data-mock-msg="Video playback is a placeholder in the mock." aria-label="Play video: <?= e($it['title']) ?>"><?= cover($it['title'], $it['hue']) ?><span class="play-btn play-btn-sm" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg></span><span class="mx-badge t-video">Video</span></button>
          <?php else: ?>
            <div class="mx-thumb is-audio"><span class="mx-wave" aria-hidden="true"></span><span class="mx-badge t-audio">Audio</span></div>
          <?php endif; ?>
          <div class="mx-body">
            <a class="mx-ball" href="<?= url('member', ['u' => $u['username']]) ?>" title="Uploaded by <?= e($u['username']) ?>"><?= avatar_html($u) ?></a>
            <div class="mx-meta">
              <h3 class="mx-title"><?= e($it['title']) ?></h3>
              <p class="mx-by"><a href="<?= url('member', ['u' => $u['username']]) ?>"><?= e($u['username']) ?></a></p>
              <p class="mx-by mx-stats"><?= ucfirst(strtolower($it['type'])) ?> &middot; <?= e(ago($it['created_min'])) ?></p>
              <p class="mx-desc"><?= e($it['caption']) ?></p>
              <?php if (role_key() === 'owner'): ?><button type="button" class="link-danger text-xs" data-confirm="Remove &quot;<?= e($it['title']) ?>&quot; from the media page?" data-reason>Remove (Owner)</button><?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="mx-empty" data-media-empty hidden><p>Nothing matches these filters.</p><button type="button" class="btn-outline" data-media-clear>Clear search and filters</button></div>
  </section>

  <dialog class="lightbox" data-lightbox-dialog aria-label="Image viewer">
    <form method="dialog"><button class="lightbox-close" aria-label="Close">&times;</button></form>
    <div class="lightbox-art" data-lightbox-img></div>
    <p class="lightbox-cap" data-lightbox-cap></p>
  </dialog>
</main>
