<?php /* views/store.php - spend credits on cosmetics (real name: Quartermaster / cosmetics). Markup reused from workspace.php.
   Buying runs through the confirm dialog, then a mock toast. No balance changes. */
defined('MOCK') || exit;
$r = role_key(); $staff = at_least('admin'); $isOwner = $r === 'owner';
$balance = (int) $me['credits'];
$owned = []; foreach (rows('owned_items') as $o) if ($o['user_id'] === $me['id']) $owned[$o['item_id']] = (int) $o['equipped'];
$ledger = where('credit_ledger', fn ($l) => $l['user_id'] === $me['id'], 6);
$kinds = ['name_color' => ['Name colours', 'The colour of your username everywhere.'], 'name_effect' => ['Name effects', 'A subtle effect on your name.'], 'frame' => ['Avatar frames', 'A ring around your avatar.'],
          'banner' => ['Profile banners', 'The strip at the top of your member page.'], 'flair' => ['Flair', 'A small tag after your name.'], 'title' => ['Titles', 'A title shown on your member page.']];
$byKind = []; foreach (rows('store_items') as $it) $byKind[$it['kind']][] = $it;
?>
<main class="max-w-6xl mx-auto pt-16 pb-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Store</p>
  <h1 class="text-3xl font-bold mb-6 page-title">Cosmetics</h1>
  <div class="shop-top">
    <section class="shop-identity" aria-label="How others see you">
      <p class="text-xs uppercase tracking-wider text-gray-500 mb-3">How other members see you</p>
      <div class="shop-identity-chip"><?= member_chip($me, '', 'md') ?></div>
      <p class="text-xs text-gray-500 mt-3">This chip appears in the sidebar, your bylines and under every upload. Change your avatar on <a href="<?= url('profile') ?>#avatar" class="text-indigo-400 hover:underline">My profile</a>.</p>
    </section>
    <section class="shop-wallet" aria-label="Credits">
      <div class="flex items-baseline justify-between gap-3">
        <div><p class="text-sm text-gray-400"><?= $staff ? 'Credits (record only)' : 'Credits' ?></p><p class="shop-balance"><?= $isOwner ? '&infin;' : number_format($balance) ?></p></div>
        <p class="text-xs text-gray-500 text-right"><?= $staff ? count(rows('store_items')) : count($owned) ?> / <?= count(rows('store_items')) ?> items<br>unlocked</p>
      </div>
      <?php if ($staff): ?><p class="text-sm text-amber-200/90 mt-2"><?= $isOwner ? 'Owner' : 'Admin' ?>: every item is unlocked for free while you hold the seat.</p>
      <?php else: ?><p class="text-sm text-gray-400 mt-2">Earned when a reviewer accepts your work: article <strong class="text-white">+50</strong>, note <strong class="text-white">+15</strong>, media <strong class="text-white">+10</strong>. Reviewers earn <strong class="text-white">+5</strong> per finished review.</p><?php endif; ?>
      <?php if ($ledger): ?>
        <details class="shop-ledger mt-3"><summary class="text-xs text-indigo-300 cursor-pointer">Recent credit history</summary>
          <ul class="mt-2 space-y-1"><?php foreach ($ledger as $l): ?><li class="flex justify-between gap-3 text-xs"><span class="text-gray-400 truncate"><?= e($l['reason']) ?></span><span class="<?= $l['delta'] >= 0 ? 'text-green-400' : 'text-red-400' ?> font-bold whitespace-nowrap"><?= $l['delta'] > 0 ? '+' : '' ?><?= (int) $l['delta'] ?></span></li><?php endforeach; ?></ul>
        </details>
      <?php endif; ?>
    </section>
  </div>
  <nav class="shop-tabs" aria-label="Cosmetic categories">
    <?php foreach ($kinds as $k => [$t]): if (empty($byKind[$k])) continue; ?><a href="#kind-<?= $k ?>" class="chip"><?= e($t) ?> <span class="text-gray-500"><?= count($byKind[$k]) ?></span></a><?php endforeach; ?>
  </nav>
  <?php foreach ($kinds as $k => [$t, $desc]): if (empty($byKind[$k])) continue; ?>
  <section class="mb-10 scroll-mt-24" id="kind-<?= $k ?>" aria-labelledby="kh-<?= $k ?>">
    <div class="mb-4"><h2 id="kh-<?= $k ?>" class="text-xl font-bold text-white"><?= e($t) ?></h2><p class="text-sm text-gray-500 mt-1"><?= e($desc) ?></p></div>
    <div class="store-grid">
      <?php foreach ($byKind[$k] as $it): $has = isset($owned[$it['id']]); $eq = $has && $owned[$it['id']]; $short = max(0, $it['price'] - $balance); ?>
      <div class="store-card<?= $eq ? ' is-equipped' : '' ?><?= $has ? ' is-owned' : '' ?>">
        <div class="store-preview">
          <?php if ($k === 'name_color' || $k === 'name_effect' || $k === 'title' || $k === 'flair'): ?><span class="store-name-sample"><span class="ac-name" style="color:<?= e($it['swatch']) ?><?= $k === 'name_effect' ? ';text-shadow:0 0 12px ' . e($it['swatch']) : '' ?>"><?= e($me['username']) ?><?= $k === 'flair' || $k === 'title' ? ' <small class="pill">' . e($it['name']) . '</small>' : '' ?></span></span>
          <?php elseif ($k === 'frame'): ?><span class="av av-md" style="box-shadow:0 0 0 3px <?= e($it['swatch']) ?>;border-radius:999px"><span class="av-disc" style="background:hsl(<?= crc32($me['username']) % 360 ?> 28% 40%)"><span class="av-initials"><?= e(initials($me['username'])) ?></span></span></span>
          <?php else: ?><span class="store-banner-swatch" style="--banner:linear-gradient(135deg, <?= e($it['swatch']) ?>, #0e0f14)"></span><?php endif; ?>
        </div>
        <div class="flex items-start justify-between gap-2 mt-3"><h3 class="font-bold text-white text-sm"><?= e($it['name']) ?></h3>
          <span class="store-price"><?php if ($eq): ?><span class="pill pill-equipped">Equipped</span><?php elseif ($has): ?><span class="pill">Owned</span><?php elseif ($staff): ?><span class="pill">Free</span><?php else: ?><?= $it['price'] ?> cr<?php endif; ?></span></div>
        <p class="text-xs text-gray-500 mt-1"><?= e($it['description']) ?></p>
        <?php if ($eq): ?><?= mock_form_open('unequip') ?><button type="submit" class="btn-outline w-full justify-center">Remove</button></form>
        <?php elseif ($has || $staff): ?><?= mock_form_open('equip') ?><button type="submit" class="form-button w-full">Equip</button></form>
        <?php else: ?><?= mock_form_open('buy', 'data-confirm="Buy ' . e($it['name']) . ' for ' . $it['price'] . ' credits? Your balance after the purchase: ' . ($balance - $it['price']) . '." data-confirm-title="Confirm purchase" data-confirm-label="Buy for ' . $it['price'] . '"') ?><button type="submit" class="form-button w-full" <?= $short ? 'disabled' : '' ?>><?= $short ? $short . ' more credits needed' : 'Buy' ?></button></form><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endforeach; ?>
  <p class="text-xs text-gray-500 text-center">In the real site each purchase was one database transaction: item, credit history and balance changed together. Here nothing is saved.</p>
</main>
