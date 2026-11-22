<?php /* views/_bulletin-list.php - bulletin cards, shared by Bulletins and the Notification centre board tab.
   Expects $bulletins and $canPost. Not a page of its own (no entry in PAGES). */
defined('MOCK') || exit;
?>
<?php if (!$bulletins): ?>
  <?= empty_state('bell', 'No bulletins yet', 'Notices for your rank will appear here.') ?>
<?php else: ?>
  <ul id="bulletin-board" class="bulletin-list">
    <?php foreach ($bulletins as $b): $read = !$b['unread']; $au = user($b['author_id']); ?>
      <li class="bulletin <?= $read ? 'is-read' : 'is-unread' ?> prio-<?= strtolower($b['priority']) ?>" id="b-<?= $b['id'] ?>" data-unread="<?= $read ? 0 : 1 ?>">
        <button type="button" class="bulletin-toggle" aria-expanded="false" aria-controls="bulletin-body-<?= $b['id'] ?>">
          <span class="unread-dot" aria-hidden="true"<?= $read ? ' style="display:none"' : '' ?>></span>
          <span class="flex-1 min-w-0">
            <span class="flex flex-wrap items-center gap-2 mb-0.5"><span class="prio-badge"><?= e($b['priority']) ?></span><?php if ($b['pinned']): ?><span class="text-xs text-amber-300">Pinned</span><?php endif; ?><?php if ($b['min_role'] !== 'member'): ?><span class="text-xs text-gray-500"><?= role_title($b['min_role']) ?>+ only</span><?php endif; ?><?php if (!$read): ?><span class="sr-only">Unread.</span><?php endif; ?></span>
            <span class="bulletin-title"><?= e($b['title']) ?></span>
          </span>
          <span class="text-xs text-gray-500 whitespace-nowrap" title="<?= when($b['created_min']) ?>"><?= ago($b['created_min']) ?></span>
        </button>
        <div class="bulletin-body" id="bulletin-body-<?= $b['id'] ?>">
          <p class="whitespace-pre-line text-gray-300 text-sm leading-relaxed"><?= e($b['body']) ?></p>
          <div class="flex flex-wrap items-center justify-between gap-2 mt-3 pt-3 border-t border-slate-700/60">
            <span class="text-xs text-gray-500">Posted by <?= e($au['username'] ?? 'the team') ?> &middot; <?= when($b['created_min']) ?></span>
            <?php if ($canPost): ?><button type="button" class="text-xs text-red-300 hover:text-red-200" data-confirm="Remove this bulletin for every member? This cannot be undone." data-confirm-label="Remove" data-confirm-tone="danger">Remove</button><?php endif; ?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
