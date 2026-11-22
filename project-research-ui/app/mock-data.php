<?php
/* app/mock-data.php - every row the mock shows, as plain PHP arrays.
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.

   ALL OF THIS IS INVENTED. No row comes from the real site's database: usernames, emails, IPs, messages,
   numbers and article texts were written for this mock. Emails and IPs are already masked, the way the
   real admin pages showed them. Dates are "minutes before now" so the mock never looks out of date.

   No database is included or needed: these arrays are the only data source. */
defined('MOCK') || exit;

return [

'users' => [
    // id, username, role, status, credits, joined (min ago), bio, masked email
    ['id' => 1,  'username' => 'owner_ada',      'role' => 'owner',       'status' => 'ACTIVE',    'credits' => 0,    'joined_min' => 288000, 'bio' => 'Keeps the lights on. Appoints the Admin team and decides governance cases.', 'email' => 'o***@example.com'],
    ['id' => 2,  'username' => 'admin_kai',      'role' => 'admin',       'status' => 'ACTIVE',    'credits' => 940,  'joined_min' => 250000, 'bio' => 'Answers the support inbox on weekdays.', 'email' => 'k***@example.com'],
    ['id' => 3,  'username' => 'admin_rhea',     'role' => 'admin',       'status' => 'ACTIVE',    'credits' => 610,  'joined_min' => 231000, 'bio' => 'Audit log and email outbox duty.', 'email' => 'r***@example.com'],
    ['id' => 4,  'username' => 'reviewer_lin',   'role' => 'reviewer',    'status' => 'ACTIVE',    'credits' => 820,  'joined_min' => 190000, 'bio' => 'Reviews articles about the larger structures. Strict on sources.', 'email' => 'l***@example.com'],
    ['id' => 5,  'username' => 'reviewer_omar',  'role' => 'reviewer',    'status' => 'ACTIVE',    'credits' => 455,  'joined_min' => 160000, 'bio' => 'Media and notes reviewer.', 'email' => 'o***@example.net'],
    ['id' => 6,  'username' => 'nova_07',        'role' => 'contributor', 'status' => 'ACTIVE',    'credits' => 385,  'joined_min' => 120000, 'bio' => 'Writes the physics-side articles. Likes clean diagrams.', 'email' => 'n***@example.com'],
    ['id' => 7,  'username' => 'vale_k',         'role' => 'contributor', 'status' => 'ACTIVE',    'credits' => 210,  'joined_min' => 98000,  'bio' => 'Fan cosmology and worldbuilding.', 'email' => 'v***@example.org'],
    ['id' => 8,  'username' => 'quill_22',       'role' => 'contributor', 'status' => 'ACTIVE',    'credits' => 125,  'joined_min' => 64000,  'bio' => '', 'email' => 'q***@example.com'],
    ['id' => 9,  'username' => 'mira_s',         'role' => 'member',      'status' => 'ACTIVE',    'credits' => 95,   'joined_min' => 41000,  'bio' => 'New here, mostly reading.', 'email' => 'm***@example.com'],
    ['id' => 10, 'username' => 'tomo_x',         'role' => 'member',      'status' => 'ACTIVE',    'credits' => 40,   'joined_min' => 22000,  'bio' => '', 'email' => 't***@example.net'],
    ['id' => 11, 'username' => 'sol_ray',        'role' => 'member',      'status' => 'ACTIVE',    'credits' => 15,   'joined_min' => 9000,   'bio' => '', 'email' => 's***@example.com'],
    ['id' => 12, 'username' => 'spam_bot_9',     'role' => 'member',      'status' => 'SUSPENDED', 'credits' => 0,    'joined_min' => 5000,   'bio' => '', 'email' => 'x***@example.com'],
],

/* Which mock user you "are" for each preview role (guest = nobody). */
'demo_identity' => ['member' => 9, 'contributor' => 6, 'reviewer' => 4, 'admin' => 2, 'owner' => 1],

'categories' => [
    ['id' => 1, 'name' => 'Physics'], ['id' => 2, 'name' => 'Speculative'], ['id' => 3, 'name' => 'Fan cosmology'],
    ['id' => 4, 'name' => 'Mathematics'], ['id' => 5, 'name' => 'Philosophy'],
],

/* The ranked "ladder": rung number, display name, class, score. Generic sample names. */
'ladder' => [
    ['rung' => 1, 'name' => 'Observable region', 'cls' => 1, 'score' => 1, 'slug' => 'observable-region'],
    ['rung' => 2, 'name' => 'Universe',          'cls' => 1, 'score' => 2, 'slug' => 'universe'],
    ['rung' => 3, 'name' => 'Bubble multiverse', 'cls' => 2, 'score' => 3, 'slug' => 'bubble-multiverse'],
    ['rung' => 4, 'name' => 'Branching worlds',  'cls' => 2, 'score' => 4, 'slug' => 'branching-worlds'],
    ['rung' => 5, 'name' => 'Brane collection',  'cls' => 3, 'score' => 5, 'slug' => 'brane-collection'],
    ['rung' => 6, 'name' => 'Mathematical ensemble', 'cls' => 3, 'score' => 6, 'slug' => 'mathematical-ensemble'],
    ['rung' => 7, 'name' => 'Sample Structure G', 'cls' => 4, 'score' => 7, 'slug' => 'structure-g'],
    ['rung' => 8, 'name' => 'Sample Structure H', 'cls' => 5, 'score' => 8, 'slug' => 'structure-h'],
    ['rung' => 9, 'name' => 'Sample Structure I', 'cls' => 5, 'score' => 9, 'slug' => 'structure-i'],
    ['rung' => 10, 'name' => 'Sample Structure J', 'cls' => 6, 'score' => 10, 'slug' => 'structure-j'],
],

'articles' => [
    ['id' => 1, 'slug' => 'observable-region', 'title' => 'Observable region', 'code' => 'L1/OR-01', 'category_id' => 1, 'level' => 'I', 'cls' => 1, 'score' => 1, 'author_id' => 6, 'reviewer_id' => 4, 'status' => 'PUBLISHED', 'views' => 412, 'created_min' => 52000,
     'summary' => 'The part of space we can, in principle, see. A sample article that shows the smallest rung on the ladder.',
     'body' => "Light has only had a limited time to travel, so there is an edge to what we can observe. This mock article explains that edge in two short paragraphs.\n\nIn the real build each article ends with a source line and a licence. Here the text is placeholder copy."],
    ['id' => 2, 'slug' => 'universe', 'title' => 'Universe', 'code' => 'L1/UN-02', 'category_id' => 1, 'level' => 'I', 'cls' => 1, 'score' => 2, 'author_id' => 6, 'reviewer_id' => 5, 'status' => 'PUBLISHED', 'views' => 980, 'created_min' => 47000,
     'summary' => 'Everything that shares our laws of physics, observed or not. Sample text for the second rung.',
     'body' => "A universe in this archive means one continuous space-time with one set of physical constants.\n\nThe article layout shows a cover, a score gauge, the body, the author chip and the discussion dock."],
    ['id' => 3, 'slug' => 'bubble-multiverse', 'title' => 'Bubble multiverse', 'code' => 'L2/BM-03', 'category_id' => 1, 'level' => 'II', 'cls' => 2, 'score' => 3, 'author_id' => 6, 'reviewer_id' => 4, 'status' => 'PUBLISHED', 'views' => 655, 'created_min' => 39000,
     'summary' => 'Many universes like bubbles in a larger space, each with its own constants. Mock summary.',
     'body' => "Some models of the early universe suggest space keeps inflating in places, leaving pockets behind.\n\nThis paragraph is sample text that shows how a second section reads in the article style."],
    ['id' => 4, 'slug' => 'branching-worlds', 'title' => 'Branching worlds', 'code' => 'L3/BW-04', 'category_id' => 5, 'level' => 'III', 'cls' => 2, 'score' => 4, 'author_id' => 7, 'reviewer_id' => 4, 'status' => 'PUBLISHED', 'views' => 530, 'created_min' => 31000,
     'summary' => 'A reading of quantum theory in which every outcome happens in its own branch. Mock summary.',
     'body' => "Branching models are a way of reading quantum theory, not a separate place you could visit.\n\nPlaceholder text continues here."],
    ['id' => 5, 'slug' => 'brane-collection', 'title' => 'Brane collection', 'code' => 'L2/BC-05', 'category_id' => 2, 'level' => 'II', 'cls' => 3, 'score' => 5, 'author_id' => 8, 'reviewer_id' => 5, 'status' => 'PUBLISHED', 'views' => 301, 'created_min' => 26000,
     'summary' => 'Universes as membranes floating in a higher-dimensional space. Sample article.',
     'body' => "Membrane models come from string theory research. This mock version keeps the explanation short.\n\nSecond paragraph of sample text."],
    ['id' => 6, 'slug' => 'mathematical-ensemble', 'title' => 'Mathematical ensemble', 'code' => 'L4/ME-06', 'category_id' => 4, 'level' => 'IV', 'cls' => 3, 'score' => 6, 'author_id' => 6, 'reviewer_id' => 4, 'status' => 'PUBLISHED', 'views' => 702, 'created_min' => 20000,
     'summary' => 'The idea that every consistent mathematical structure exists. The top of the physics side.',
     'body' => "This is the most abstract rung on the physics side of the ladder.\n\nMock text: in the real site this article links to the glossary for the hard terms."],
    ['id' => 7, 'slug' => 'structure-g', 'title' => 'Sample Structure G', 'code' => 'H/SG-07', 'category_id' => 3, 'level' => 'HYPER', 'cls' => 4, 'score' => 7, 'author_id' => 7, 'reviewer_id' => 4, 'status' => 'PUBLISHED', 'views' => 244, 'created_min' => 15000,
     'summary' => 'A fan-made structure that contains every rung below it. Placeholder for the speculative side.',
     'body' => "Speculative structures are always labelled as such, so readers can tell worldbuilding from physics.\n\nSample text."],
    ['id' => 8, 'slug' => 'structure-h', 'title' => 'Sample Structure H', 'code' => 'H/SH-08', 'category_id' => 3, 'level' => 'HYPER', 'cls' => 5, 'score' => 8, 'author_id' => 7, 'reviewer_id' => 5, 'status' => 'PUBLISHED', 'views' => 190, 'created_min' => 9000,
     'summary' => 'Contains Structure G. Score follows containment: never lower than what it holds.', 'body' => "Mock article body.\n\nSecond mock paragraph."],
    ['id' => 9, 'slug' => 'structure-i', 'title' => 'Sample Structure I', 'code' => 'H/SI-09', 'category_id' => 3, 'level' => 'HYPER', 'cls' => 5, 'score' => 9, 'author_id' => 8, 'reviewer_id' => 4, 'status' => 'PUBLISHED', 'views' => 120, 'created_min' => 4000,
     'summary' => 'A near-top rung used to show the high end of the score gauge.', 'body' => "Mock article body.\n\nSecond mock paragraph."],
    ['id' => 10, 'slug' => 'structure-j', 'title' => 'Sample Structure J', 'code' => 'H/SJ-10', 'category_id' => 2, 'level' => 'HYPER', 'cls' => 6, 'score' => 10, 'author_id' => 6, 'reviewer_id' => 4, 'status' => 'PUBLISHED', 'views' => 88, 'created_min' => 1500,
     'summary' => 'The top rung in this mock ladder, with the maximum score.', 'body' => "Mock article body.\n\nSecond mock paragraph."],
    // waiting in the review queue / drafts / sent back
    ['id' => 11, 'slug' => 'pending-k', 'title' => 'Sample Structure K (pending)', 'code' => 'H/SK-11', 'category_id' => 3, 'level' => 'HYPER', 'cls' => 6, 'score' => 10, 'author_id' => 8, 'reviewer_id' => null, 'status' => 'PENDING', 'views' => 0, 'created_min' => 180,
     'summary' => 'Waiting for a reviewer. Shows how a pending article looks in the queue.', 'body' => "Pending mock text.\n\nA reviewer can approve it or send it back with a note."],
    ['id' => 12, 'slug' => 'pending-l', 'title' => 'Cosmic horizon notes (pending)', 'code' => 'L1/CH-12', 'category_id' => 1, 'level' => 'I', 'cls' => 1, 'score' => 2, 'author_id' => 6, 'reviewer_id' => null, 'status' => 'PENDING', 'views' => 0, 'created_min' => 620,
     'summary' => 'A second pending article, written by the Contributor you preview as.', 'body' => "Pending mock text."],
    ['id' => 13, 'slug' => 'draft-m', 'title' => 'Untitled draft', 'code' => '', 'category_id' => 2, 'level' => 'II', 'cls' => 2, 'score' => 3, 'author_id' => 6, 'reviewer_id' => null, 'status' => 'DRAFT', 'views' => 0, 'created_min' => 2900,
     'summary' => 'A draft saved from the article wizard.', 'body' => "Draft mock text."],
    ['id' => 14, 'slug' => 'returned-n', 'title' => 'Folded dimensions (returned)', 'code' => 'L2/FD-14', 'category_id' => 2, 'level' => 'II', 'cls' => 3, 'score' => 4, 'author_id' => 6, 'reviewer_id' => 4, 'status' => 'RETURNED', 'views' => 0, 'created_min' => 7200,
     'summary' => 'Sent back with a note: needs a source line.', 'body' => "Returned mock text.", 'review_note' => 'Good start. Please add the source and licence line at the end, then resubmit.'],
],

'notes' => [
    ['id' => 1, 'title' => 'Why the score never goes down a rung', 'type' => 'NOTE', 'author_id' => 9, 'status' => 'PUBLISHED', 'created_min' => 3000,
     'body' => "A structure that contains another one should never get a lower score than what it contains. This mock note explains the rule in plain words, the way a member would write it."],
    ['id' => 2, 'title' => 'Idea: a timeline view for the ladder', 'type' => 'IDEA', 'author_id' => 10, 'status' => 'PUBLISHED', 'created_min' => 7000,
     'body' => "What if the ladder could also be shown as a timeline of when each idea was first proposed? Sample idea text."],
    ['id' => 3, 'title' => 'Reading list for new members', 'type' => 'FIELD_NOTE', 'author_id' => 7, 'status' => 'PUBLISHED', 'created_min' => 12000,
     'body' => "Start with the Universe article, then the glossary, then the help page. Mock field note."],
    ['id' => 4, 'title' => 'Branching vs bubbles: are they the same?', 'type' => 'NOTE', 'author_id' => 11, 'status' => 'PENDING', 'created_min' => 95,
     'body' => "A pending note waiting in the review queue. Mock text long enough to pass the 50 character minimum."],
],

'media' => [
    ['id' => 1, 'title' => 'Scale diagram, first draft', 'type' => 'IMAGE', 'caption' => 'A placeholder for an uploaded image.', 'author_id' => 6, 'status' => 'PUBLISHED', 'hue' => 210, 'created_min' => 8000],
    ['id' => 2, 'title' => 'Explainer video (embed placeholder)', 'type' => 'VIDEO', 'caption' => 'In the real site, YouTube links become embedded players.', 'author_id' => 7, 'status' => 'PUBLISHED', 'hue' => 280, 'created_min' => 14000],
    ['id' => 3, 'title' => 'Ambience loop', 'type' => 'AUDIO', 'caption' => 'An audio item hosted elsewhere.', 'author_id' => 8, 'status' => 'PUBLISHED', 'hue' => 30, 'created_min' => 19000],
    ['id' => 4, 'title' => 'Ladder poster', 'type' => 'IMAGE', 'caption' => 'Sample poster tile.', 'author_id' => 9, 'status' => 'PUBLISHED', 'hue' => 150, 'created_min' => 23000],
    ['id' => 5, 'title' => 'Nebula sketch', 'type' => 'IMAGE', 'caption' => 'Sample image tile.', 'author_id' => 10, 'status' => 'PUBLISHED', 'hue' => 330, 'created_min' => 30000],
    ['id' => 6, 'title' => 'Member upload (pending)', 'type' => 'IMAGE', 'caption' => 'Waiting in the review queue.', 'author_id' => 9, 'status' => 'PENDING', 'hue' => 60, 'created_min' => 40],
],

'comments' => [
    ['id' => 1, 'article_id' => 2, 'user_id' => 4, 'body' => 'Approved. Clear and well sourced.', 'created_min' => 46000],
    ['id' => 2, 'article_id' => 2, 'user_id' => 9, 'body' => 'Could the cover show a scale bar?', 'created_min' => 3000],
    ['id' => 3, 'article_id' => 2, 'user_id' => 6, 'body' => 'Good idea, I will add one in the next revision.', 'created_min' => 2800],
    ['id' => 4, 'article_id' => 6, 'user_id' => 7, 'body' => 'This one needs a glossary link for "ensemble".', 'created_min' => 900],
],

'credit_ledger' => [
    ['id' => 1, 'user_id' => 6, 'delta' => 50,  'reason' => 'Article approved: Mathematical ensemble', 'created_min' => 20000],
    ['id' => 2, 'user_id' => 6, 'delta' => 15,  'reason' => 'Note approved', 'created_min' => 15000],
    ['id' => 3, 'user_id' => 6, 'delta' => -80, 'reason' => 'Bought: Ember name colour', 'created_min' => 12000],
    ['id' => 4, 'user_id' => 6, 'delta' => 50,  'reason' => 'Article approved: Sample Structure J', 'created_min' => 1500],
    ['id' => 5, 'user_id' => 9, 'delta' => 15,  'reason' => 'Note approved', 'created_min' => 3000],
    ['id' => 6, 'user_id' => 9, 'delta' => 10,  'reason' => 'Media approved: Ladder poster', 'created_min' => 23000],
    ['id' => 7, 'user_id' => 4, 'delta' => 5,   'reason' => 'Peer review finished', 'created_min' => 1500],
],

'notifications' => [
    ['id' => 1, 'user_id' => null, 'side' => 'board', 'icon' => 'megaphone', 'label' => 'Bulletin', 'title' => 'Maintenance window on Saturday', 'body' => 'The site will be read-only for about an hour.', 'unread' => 1, 'created_min' => 120],
    ['id' => 2, 'user_id' => null, 'side' => 'you',   'icon' => 'chat',      'label' => 'Reply',    'title' => 'nova_07 replied on "Universe"', 'body' => 'Good idea, I will add one in the next revision.', 'unread' => 1, 'created_min' => 2800],
    ['id' => 3, 'user_id' => null, 'side' => 'you',   'icon' => 'check',     'label' => 'Review',   'title' => 'Your note was approved (+15 credits)', 'body' => '', 'unread' => 0, 'created_min' => 3000],
    ['id' => 4, 'user_id' => null, 'side' => 'admin', 'icon' => 'inbox',     'label' => 'Task',     'title' => '3 items waiting in the review queue', 'body' => '', 'unread' => 1, 'created_min' => 40],
],

'bulletins' => [
    ['id' => 1, 'title' => 'Maintenance window on Saturday', 'body' => "The site will be read-only for about an hour while the database is backed up.\nYou can still read everything.", 'priority' => 'IMPORTANT', 'min_role' => 'member', 'pinned' => 1, 'author_id' => 2, 'unread' => 1, 'created_min' => 120],
    ['id' => 2, 'title' => 'New: draft autosave in the article wizard', 'body' => 'Drafts are now kept on your device while you type, so a closed tab no longer loses your work.', 'priority' => 'INFO', 'min_role' => 'member', 'pinned' => 0, 'author_id' => 4, 'unread' => 0, 'created_min' => 5000],
    ['id' => 3, 'title' => 'Reviewer guidelines updated', 'body' => 'Please check the new source-line rule before approving articles.', 'priority' => 'URGENT', 'min_role' => 'reviewer', 'pinned' => 0, 'author_id' => 1, 'unread' => 1, 'created_min' => 900],
],

'rank_requests' => [
    ['id' => 1, 'user_id' => 9,  'requested_role' => 'contributor', 'status' => 'PENDING',  'note' => '', 'decided_by' => null, 'created_min' => 300],
    ['id' => 2, 'user_id' => 8,  'requested_role' => 'reviewer',    'status' => 'PENDING',  'note' => '', 'decided_by' => null, 'created_min' => 1400],
    ['id' => 3, 'user_id' => 7,  'requested_role' => 'contributor', 'status' => 'APPROVED', 'note' => 'Three approved notes and a clean record.', 'decided_by' => 4, 'created_min' => 98000],
    ['id' => 4, 'user_id' => 10, 'requested_role' => 'contributor', 'status' => 'DENIED',   'note' => 'Needs at least one approved note first.', 'decided_by' => 5, 'created_min' => 15000],
],

'support_messages' => [
    ['id' => 1, 'name' => 'visitor_ash', 'email' => 'a***@example.com', 'user_id' => null, 'type' => 'ACCOUNT', 'message' => 'I signed up but never got the sign-in code. Can you check?', 'status' => 'NEW', 'reply' => '', 'handler_id' => null, 'created_min' => 75],
    ['id' => 2, 'name' => 'mira_s', 'email' => 'm***@example.com', 'user_id' => 9, 'type' => 'BUG', 'message' => 'The cover image on the Universe article does not load on my phone.', 'status' => 'READ', 'reply' => '', 'handler_id' => null, 'created_min' => 1300],
    ['id' => 3, 'name' => 'tomo_x', 'email' => 't***@example.net', 'user_id' => 10, 'type' => 'CONTENT', 'message' => 'Small typo in the glossary entry for "horizon".', 'status' => 'RESOLVED', 'reply' => 'Fixed, thank you for spotting it.', 'handler_id' => 2, 'created_min' => 8000],
],

'audit_log' => [
    ['id' => 1, 'actor_id' => 4, 'action' => 'review_approve',   'target_id' => 6,  'details' => 'Approved article #10 "Sample Structure J".', 'ip' => '203.0.113.x', 'created_min' => 1500],
    ['id' => 2, 'actor_id' => 2, 'action' => 'user_suspend',     'target_id' => 12, 'details' => 'Suspended for posting spam links. Reason sent to the member.', 'ip' => '198.51.100.x', 'created_min' => 4800],
    ['id' => 3, 'actor_id' => 4, 'action' => 'rank_approve',     'target_id' => 7,  'details' => 'Member -> Contributor. Note: Three approved notes and a clean record.', 'ip' => '203.0.113.x', 'created_min' => 98000],
    ['id' => 4, 'actor_id' => 5, 'action' => 'rank_deny',        'target_id' => 10, 'details' => 'Contributor request denied. Note: Needs at least one approved note first.', 'ip' => '192.0.2.x', 'created_min' => 15000],
    ['id' => 5, 'actor_id' => 1, 'action' => 'admin_appoint',    'target_id' => 3,  'details' => 'Appointed to the Admin team.', 'ip' => '198.51.100.x', 'created_min' => 231000],
    ['id' => 6, 'actor_id' => 6, 'action' => 'store_purchase',   'target_id' => 6,  'details' => 'Bought "Ember" name colour for 80 credits.', 'ip' => '192.0.2.x', 'created_min' => 12000],
],

'email_outbox' => [
    ['id' => 1, 'username' => 'visitor', 'to' => 'a***@example.com', 'subject' => 'Your sign-in code', 'department' => 'Accounts', 'status' => 'LOGGED', 'error' => '', 'created_min' => 76],
    ['id' => 2, 'username' => 'mira_s',  'to' => 'm***@example.com', 'subject' => 'New sign-in from a new device', 'department' => 'Security', 'status' => 'SENT', 'error' => '', 'created_min' => 2000],
    ['id' => 3, 'username' => 'nova_07', 'to' => 'n***@example.com', 'subject' => 'Your article was approved', 'department' => 'Notifications', 'status' => 'SENT', 'error' => '', 'created_min' => 1500],
    ['id' => 4, 'username' => 'quill_22', 'to' => 'q***@example.com', 'subject' => 'Weekly digest', 'department' => 'Notifications', 'status' => 'FAILED', 'error' => 'Mailbox unavailable (mock)', 'created_min' => 10000],
    ['id' => 5, 'username' => 'tomo_x',  'to' => 't***@example.net', 'subject' => 'Reply to your message', 'department' => 'Support', 'status' => 'QUEUED', 'error' => '', 'created_min' => 3],
],

'store_items' => [
    ['id' => 1, 'kind' => 'name_color',  'name' => 'Ember',        'price' => 80,  'description' => 'Warm orange name colour.', 'swatch' => '#e2735f'],
    ['id' => 2, 'kind' => 'name_color',  'name' => 'Glacier',      'price' => 80,  'description' => 'Cool blue name colour.',   'swatch' => '#8fb0e8'],
    ['id' => 3, 'kind' => 'name_effect', 'name' => 'Soft glow',    'price' => 200, 'description' => 'A gentle glow behind your name.', 'swatch' => '#d9a3ef'],
    ['id' => 4, 'kind' => 'frame',       'name' => 'Brass ring',   'price' => 150, 'description' => 'Thin metallic avatar frame.', 'swatch' => '#c9a14a'],
    ['id' => 5, 'kind' => 'frame',       'name' => 'Dark halo',    'price' => 300, 'description' => 'Deep avatar frame with a halo.', 'swatch' => '#4a4f6a'],
    ['id' => 6, 'kind' => 'banner',      'name' => 'Starfield',    'price' => 250, 'description' => 'Profile banner with faint stars.', 'swatch' => '#23263a'],
    ['id' => 7, 'kind' => 'flair',       'name' => 'Early reader', 'price' => 40,  'description' => 'Small tag next to your name.', 'swatch' => '#6fa37a'],
    ['id' => 8, 'kind' => 'title',       'name' => 'Cartographer', 'price' => 500, 'description' => 'A title shown on your member page.', 'swatch' => '#b25a0e'],
],
'owned_items' => [6 => [1, 7], 9 => [7]],   // user id => owned store item ids (equipped: first one)

'cases' => [
    ['id' => 1, 'code' => 'GOV-2026-001', 'filed_by' => 9, 'subject_id' => 3, 'category' => 'UNFAIR_MODERATION', 'status' => 'UNDER_REVIEW', 'subject_line' => 'A note was removed without a reason', 'description' => "Mock complaint text. The member says a note was removed without the reason being sent.\nIn the real site evidence is private between the complainant, the accused and the reviewer.", 'response' => 'Mock response from the accused: the note was a duplicate; the reason did not send because of a mail error.', 'outcome' => null, 'decision_summary' => '', 'created_min' => 4000],
    ['id' => 2, 'code' => 'GOV-2026-002', 'filed_by' => 10, 'subject_id' => 2, 'category' => 'OTHER', 'status' => 'DECIDED', 'subject_line' => 'Rude reply in the support inbox', 'description' => 'Mock complaint text.', 'response' => '', 'outcome' => 'WARNING', 'decision_summary' => 'Partly upheld. The Admin apologised and the reply template was rewritten. No rank change.', 'created_min' => 30000],
],
'petitions' => [
    ['id' => 1, 'title' => 'Publish review guidelines on the help page', 'type' => 'POLICY_REVIEW', 'target_id' => null, 'creator_id' => 7, 'signatures' => 7, 'required' => 10, 'status' => 'OPEN', 'expires_days' => 9, 'body' => 'Mock petition text: members should be able to read the exact checklist reviewers use.'],
    ['id' => 2, 'title' => 'Investigate slow rank decisions', 'type' => 'INVESTIGATION', 'target_id' => 3, 'creator_id' => 9, 'signatures' => 10, 'required' => 10, 'status' => 'THRESHOLD_REACHED', 'expires_days' => 2, 'body' => 'Mock petition text.'],
],
'polls' => [
    // confidence poll opened by the reviewer during case 1; counts per fixed choice
    ['id' => 1, 'case_id' => 1, 'question' => 'Do you have confidence in admin_rhea continuing on the Admin team?', 'confidence' => 12, 'investigate' => 5, 'no_opinion' => 3, 'closes_hours' => 50],
],

'glossary' => [
    ['term' => 'Article',     'def' => 'A reviewed entry about one structure, with a summary, a score and sources.'],
    ['term' => 'Note',        'def' => 'A short community post: a note, an idea or a field note. Any member can write one.'],
    ['term' => 'Score',       'def' => 'A 1-10 rating. It follows containment: a structure never scores lower than what it contains.'],
    ['term' => 'Class',       'def' => 'One of six bands that group rungs of the ladder.'],
    ['term' => 'Level I-IV',  'def' => 'Four levels used on the physics side of the ladder.'],
    ['term' => 'Speculative', 'def' => 'Labelled worldbuilding or hypotheses; never presented as tested science.'],
    ['term' => 'Credits',     'def' => 'Earned when someone else approves your work; spent on cosmetics.'],
    ['term' => 'Review queue','def' => 'Where pending work waits. Nobody can approve their own submission.'],
],

'faq' => [
    ['group' => 'Ranks', 'q' => 'How do I move up a rank?', 'a' => 'Finish the tasks for the next rank on your dashboard, then send a rank request. A Reviewer or Admin checks your record and decides.'],
    ['group' => 'Ranks', 'q' => 'Can I approve my own article?', 'a' => 'No. The review queue hides your own submissions from you. Admins may publish directly, but self-published work earns no credits.'],
    ['group' => 'Credits', 'q' => 'How do I earn credits?', 'a' => 'Article approved +50, note approved +15, media approved +10. Reviewers get +5 for each finished review.'],
    ['group' => 'Credits', 'q' => 'Can I lose credits?', 'a' => 'Only by spending them. Every change is written to a ledger you can read on the store page.'],
    ['group' => 'Account', 'q' => 'Do I need my real name?', 'a' => 'No. You are known by a username. Real names are never asked for or shown.'],
    ['group' => 'Account', 'q' => 'What is the second sign-in step?', 'a' => 'After your password you enter a 6-digit code from email or an authenticator app. Admins must use an app.'],
    ['group' => 'Privacy', 'q' => 'Who can see my email?', 'a' => 'Only Admins and the Owner, in the support inbox and user list, and it is partly hidden there.'],
    ['group' => 'Governance', 'q' => 'What if an Admin abuses their power?', 'a' => 'File a complaint with evidence, start a petition, or vote in a confidence poll. The Owner decides with a written reason.'],
],

];
