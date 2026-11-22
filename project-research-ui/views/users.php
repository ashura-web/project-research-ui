<?php /* views/users.php - Admin user management table. Markup reused from admin.php (users view).
   Rank changes, suspensions and password resets are mock buttons; "Signed in now" is invented per row. */
defined('MOCK') || exit;
$all = rows('users');
$pendingCount = count(where('rank_requests', fn ($r) => $r['status'] === 'PENDING'));
$online = fn (array $u) => $u['id'] % 3 === 0 || $u['id'] === $me['id'];
?>
<main class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Admin console</p>
  <h1 class="text-3xl font-bold mb-2 page-title">User management</h1>
  <p class="text-gray-400 mb-8">Accounts, ranks and suspensions. Every action here is recorded in the <a href="<?= url('audit-log') ?>" class="text-indigo-400 hover:underline">audit log</a>.</p>
  <?php if ($pendingCount): ?>
    <div class="cta-panel p-4 mb-8 flex flex-wrap items-center justify-between gap-3">
      <p class="text-sm text-gray-300"><strong class="text-white"><?= $pendingCount ?></strong> rank request<?= $pendingCount === 1 ? '' : 's' ?> waiting on the <a href="<?= url('requests') ?>" class="text-indigo-400 hover:underline">requests board</a>.</p>
      <a href="<?= url('requests') ?>" class="btn-outline">Open the board &rarr;</a>
    </div>
  <?php endif; ?>
  <section class="bg-slate-800/80 rounded-xl border border-slate-700 p-6">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
      <h2 class="text-xl font-bold text-white">All members (<?= count($all) ?>) <span class="presence-sum"><?= count(array_filter($all, $online)) ?> signed in now</span></h2>
      <div class="flex flex-wrap gap-2 items-center">
        <label for="user-filter" class="sr-only">Filter members</label>
        <input type="search" id="user-filter" class="form-input !py-1.5 !w-48 text-sm" placeholder="Filter by name or email..." data-table-filter="#users-table" data-count-target="#user-filter-count">
        <label for="level-filter" class="sr-only">Show role</label>
        <select id="level-filter" class="form-input !py-1.5 !w-40 text-sm" data-level-filter="#users-table">
          <option value="">All roles</option><?php foreach (['member', 'contributor', 'reviewer', 'admin', 'owner'] as $r): ?><option value="<?= $r ?>"><?= role_title($r) ?></option><?php endforeach; ?><option value="SUSPENDED">Suspended</option><option value="ONLINE">Signed in now</option>
        </select>
        <span id="user-filter-count" class="text-xs text-gray-500" aria-live="polite"></span>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table id="users-table" class="w-full text-sm data-table m-stack">
        <thead><tr class="text-left text-gray-400 border-b border-slate-700"><th class="py-2 pr-3">Member</th><th class="py-2 pr-3">Email</th><th class="py-2 pr-3">Role</th><th class="py-2 pr-3">Credits</th><th class="py-2 pr-3">Signed in now</th><th class="py-2 pr-3">Account</th><th class="py-2 pr-3">Joined</th><th class="py-2">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($all as $u): $self = $u['id'] === $me['id']; $on = $online($u); ?>
          <tr class="border-b border-slate-700/50" data-level="<?= $u['role'] ?>" data-status="<?= $u['status'] ?>" data-online="<?= $on ? 1 : 0 ?>">
            <td data-label="Member" class="py-2.5 pr-3 text-gray-200"><?= member_chip($u, '', 'xs') ?> <?= $self ? '<span class="text-xs text-gray-500">(you)</span>' : '' ?></td>
            <td data-label="Email" class="py-2.5 pr-3 text-gray-400 text-xs"><?= e($u['email']) ?></td>
            <td data-label="Role" class="py-2.5 pr-3"><?= badge_html($u['role']) ?> <a href="<?= url('audit-log', ['u' => $u['username']]) ?>" class="text-xs text-gray-500 hover:text-indigo-300" title="This member's audit history" aria-label="Audit history of <?= e($u['username']) ?>"><?= nav_icon('book') ?></a></td>
            <td data-label="Credits" class="py-2.5 pr-3 text-gray-300"><?= number_format($u['credits']) ?></td>
            <td data-label="Signed in" class="py-2.5 pr-3"><?= $on ? '<span class="presence is-on is-live">Active now</span>' : '<span class="presence">Signed out</span>' ?></td>
            <td data-label="Account" class="py-2.5 pr-3"><span class="text-xs font-bold <?= $u['status'] === 'ACTIVE' ? 'text-gray-300' : 'text-red-400' ?>"><?= $u['status'] === 'ACTIVE' ? 'Active' : 'Suspended' ?></span></td>
            <td data-label="Joined" class="py-2.5 pr-3 text-xs text-gray-500"><?= when($u['joined_min'], 'M j, Y') ?></td>
            <td data-label="Actions" class="py-2.5">
              <?php if ($u['role'] === 'owner' && !$self): ?><span class="pill" title="The site owner. Only the succession flow can change this.">Site owner</span>
              <?php elseif ($u['role'] === 'admin' && !$self): ?><span class="pill" title="Admin seats are managed by the owner">Owner gate</span>
              <?php elseif (!$self): ?>
                <div class="flex flex-nowrap gap-1.5">
                  <?php if ($u['role'] !== 'reviewer'): ?><?= mock_form_open('promote', 'class="inline"') ?><button type="submit" title="Promote one rank" aria-label="Promote <?= e($u['username']) ?> one rank" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded">&#9650;&#xFE0E;</button></form><?php endif; ?>
                  <?php if ($u['role'] !== 'member'): ?><?= mock_form_open('demote', 'class="inline"') ?><button type="submit" title="Demote one rank" aria-label="Demote <?= e($u['username']) ?> one rank" class="px-2.5 py-1 bg-slate-600 hover:bg-slate-500 text-white text-xs font-bold rounded">&#9660;&#xFE0E;</button></form><?php endif; ?>
                  <?= mock_form_open('toggle_status', 'class="inline" data-confirm="' . ($u['status'] === 'ACTIVE' ? 'Suspend this member? They cannot sign in until reactivated.' : 'Reactivate this member?') . '" data-confirm-label="' . ($u['status'] === 'ACTIVE' ? 'Suspend' : 'Activate') . '"' . ($u['status'] === 'ACTIVE' ? ' data-confirm-tone="danger" data-confirm-reason="reason" data-confirm-reason-label="Reason (sent to the member) *"' : '')) ?><button type="submit" class="px-2.5 py-1 text-xs font-bold rounded <?= $u['status'] === 'ACTIVE' ? 'bg-amber-600 hover:bg-amber-500' : 'bg-green-600 hover:bg-green-500' ?> text-white"><?= $u['status'] === 'ACTIVE' ? 'Suspend' : 'Activate' ?></button></form>
                  <?= mock_form_open('reset_password', 'class="inline" data-confirm="Issue a temporary password for ' . e($u['username']) . '? Their current password stops working at once." data-confirm-label="Issue password"') ?><button type="submit" class="px-2.5 py-1 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold rounded">Reset password</button></form>
                </div>
              <?php else: ?><span class="text-xs text-gray-600">&mdash;</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>
