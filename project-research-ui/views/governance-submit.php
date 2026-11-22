<?php /* views/governance-submit.php - Formal complaint form (Admins only can be accused). Markup reused from governance.php.
   Server-side checks run through the reused Validator in index.php; the evidence panel switcher is in mock-ui.js. */
defined('MOCK') || exit;
$admins = where('users', fn ($u) => in_array($u['role'], ['admin', 'owner'], true) && $u['id'] !== $me['id']);
$old += ['subject' => '', 'category' => 'UNFAIR_MODERATION', 'incident_at' => '', 'policy_reference' => '', 'subject_line' => '', 'description' => '', 'evidence_kind' => 'NONE', 'evidence_text' => '', 'evidence_url' => '', 'evidence_note' => ''];
?>
<main class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8 content-overlay">
  <p class="kicker mb-1">Community governance &middot; Accountability protocol</p>
  <h1 class="text-3xl font-bold mb-2 page-title">File a formal complaint</h1>
  <p class="text-gray-400 text-sm mb-8">Complaints concern <strong class="text-white">Admins</strong> only; ordinary moderation is handled through the support inbox. A complaint is an allegation until decided: describe what happened, cite the help-centre rule if you can, and attach evidence. Bad-faith filings are themselves a rule violation.</p>
  <?php if ($errors): ?><div class="form-status error mb-6" role="alert">Please fix <?= count($errors) ?> highlighted field<?= count($errors) === 1 ? '' : 's' ?>.</div><?php endif; ?>
  <?= mock_form_open('complaint', 'class="space-y-5" data-validate data-draft="complaint" novalidate') ?>
    <div>
      <label class="block text-sm font-semibold text-gray-300 mb-1" for="subject">Accused Admin</label>
      <select id="subject" name="subject" class="form-input<?= invalid_class($errors, 'subject') ?>"<?= invalid_attr($errors, 'subject', 'subject') ?> required data-label="Accused Admin">
        <option value="">Choose an Admin</option>
        <?php foreach ($admins as $a): ?><option value="<?= $a['id'] ?>" <?= (string) $old['subject'] === (string) $a['id'] ? 'selected' : '' ?>><?= e($a['username']) ?><?= $a['role'] === 'owner' ? ' (Owner)' : '' ?></option><?php endforeach; ?>
      </select>
      <?= field_error($errors, 'subject', 'subject') ?>
      <p class="text-xs text-gray-500 mt-1">You cannot choose yourself. A case against the Owner is sealed: nobody on the site can triage or decide it, so it is settled outside the site.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="category">Category</label><select id="category" name="category" class="form-input"><?php foreach (Governance::CATEGORIES as $k => $l): ?><option value="<?= $k ?>" <?= $old['category'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
      <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="incident_at">When did it happen? <span class="text-gray-500 font-normal">(optional)</span></label><input type="date" id="incident_at" name="incident_at" class="form-input" value="<?= e($old['incident_at']) ?>" max="<?= date('Y-m-d') ?>"></div>
    </div>
    <div><label class="block text-sm font-semibold text-gray-300 mb-1" for="policy_reference">Help-centre rule <span class="text-gray-500 font-normal">(optional)</span></label><input type="text" id="policy_reference" name="policy_reference" class="form-input" placeholder="e.g. Help centre: Ranks and accounts" value="<?= e($old['policy_reference']) ?>" maxlength="255"></div>
    <div>
      <label class="block text-sm font-semibold text-gray-300 mb-1" for="subject_line">Subject</label>
      <input type="text" id="subject_line" name="subject_line" class="form-input<?= invalid_class($errors, 'subject_line') ?>"<?= invalid_attr($errors, 'subject_line', 'subject_line') ?> placeholder="One sentence: what is the allegation?" value="<?= e($old['subject_line']) ?>" minlength="10" maxlength="180" required data-label="Subject" data-counter>
      <?= field_error($errors, 'subject_line', 'subject_line') ?>
    </div>
    <div>
      <label class="block text-sm font-semibold text-gray-300 mb-1" for="description">What happened?</label>
      <textarea id="description" name="description" rows="8" class="form-input<?= invalid_class($errors, 'description') ?>"<?= invalid_attr($errors, 'description', 'description') ?> placeholder="Facts only: what you saw, when, who else can confirm it." minlength="50" maxlength="20000" required data-label="Description" data-counter><?= e($old['description']) ?></textarea>
      <?= field_error($errors, 'description', 'description') ?>
      <p class="text-xs text-gray-500 mt-1">50 to 20,000 characters. Kept private: only you, the accused and the reviewer see it.</p>
    </div>
    <fieldset class="border border-slate-700 rounded-xl p-4">
      <legend class="text-sm font-semibold text-gray-300 px-1">Supporting evidence <span class="text-gray-500 font-normal">(optional)</span></legend>
      <div class="flex flex-wrap gap-4 mb-3 text-sm"><?php foreach (['NONE' => 'No evidence', 'TEXT' => 'Written note', 'URL' => 'Link', 'FILE' => 'File upload'] as $k => $l): ?><label class="inline-flex items-center gap-1.5 cursor-pointer"><input type="radio" name="evidence_kind" value="<?= $k ?>" class="accent-violet-500" <?= $old['evidence_kind'] === $k ? 'checked' : '' ?>> <?= $l ?></label><?php endforeach; ?></div>
      <div data-evpanel="TEXT" class="mb-3" hidden><label for="evidence_text" class="sr-only">Evidence text</label><textarea id="evidence_text" name="evidence_text" rows="3" class="form-input" placeholder="Quote, transcribe or describe the evidence..."><?= e($old['evidence_text']) ?></textarea></div>
      <div data-evpanel="URL" class="mb-3" hidden><label for="evidence_url" class="sr-only">Evidence link</label><input type="url" id="evidence_url" name="evidence_url" class="form-input<?= invalid_class($errors, 'evidence_url') ?>" placeholder="https://..." value="<?= e($old['evidence_url']) ?>"><?= field_error($errors, 'evidence_url', 'evidence_url') ?></div>
      <div data-evpanel="FILE" hidden><label for="evidence_file" class="sr-only">Evidence file</label><input type="file" id="evidence_file" name="evidence_file" class="form-input" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.txt"><p class="text-xs text-gray-500 mt-1">JPG, PNG, WEBP, GIF, PDF or TXT, max 2 MB. The mock does not upload anything.</p></div>
      <div class="mt-3"><label class="block text-sm font-semibold text-gray-300 mb-1" for="evidence_note">Evidence description <span class="text-gray-500 font-normal">(optional)</span></label><input type="text" id="evidence_note" name="evidence_note" class="form-input" maxlength="500" placeholder="What does this evidence show?" value="<?= e($old['evidence_note']) ?>"></div>
    </fieldset>
    <button type="submit" class="form-button w-full">File the complaint</button>
    <p class="text-xs text-gray-500 text-center">Bad-faith complaints can cost credits and rank. Evidence, not popularity, decides. <small data-draft-status class="block mt-1"></small></p>
  </form>
</main>
