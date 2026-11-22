<?php
/* app/governance-rules.php - fixed governance rules (labels, windows, thresholds).
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.
   Reused almost as-is from the real Backend/governance-rules.php: these constants carry no data,
   only the rule book. The database-backed methods (filing, signing, voting) are left out. */
defined('MOCK') || exit;

final class Governance
{
    /* Days after sign-up before an account may sign, start a petition or vote (stops throwaway accounts). */
    public const WAIT_DAYS = ['admin' => 1, 'reviewer' => 2, 'contributor' => 3, 'member' => 4];
    public const PETITION_DAYS = 14;
    public const POLL_DAYS = 3;
    public const APPEAL_WINDOW_DAYS = 3;

    public const CATEGORIES = [
        'MISUSE_OF_POWER'      => 'Abuse of admin privileges',
        'UNFAIR_MODERATION'    => 'Unfair moderation or rejection',
        'UNJUST_SUSPENSION'    => 'Arbitrary or unjustified suspension',
        'CONTENT_SUPPRESSION'  => 'Deletion or hiding of valid content',
        'CONFLICT_OF_INTEREST' => 'Conflict of interest',
        'RETALIATION'          => 'Retaliation against a member',
        'OTHER'                => 'Other documented violation',
    ];
    public const CASE_STATUS = [
        'SUBMITTED' => 'Submitted', 'TRIAGE' => 'Triage', 'UNDER_REVIEW' => 'Under review',
        'AWAITING_RESPONSE' => 'Awaiting response', 'DECIDED' => 'Decided', 'APPEALED' => 'Appealed',
        'CLOSED' => 'Closed', 'EXTERNAL_REVIEW_REQUIRED' => 'With the external authority',
    ];
    public const PETITION_TYPES = [
        'INVESTIGATION' => 'Investigation: ask for a formal review',
        'NO_CONFIDENCE' => 'No confidence: signal lost trust in an Admin',
        'POLICY_REVIEW' => 'Policy review: challenge a rule or how it is enforced',
    ];
    public const OUTCOMES = [
        'NO_VIOLATION' => 'No violation found', 'WARNING' => 'Written warning', 'PROBATION' => 'Probation',
        'RESTRICTED' => 'Restricted privileges', 'SUSPENDED' => 'Account suspended', 'DEMOTED' => 'Demoted from Admin',
        'REMOVED' => 'Removed from Admin', 'REFERRED_EXTERNALLY' => 'Sent to the external authority',
    ];
    public const POLL_CHOICES = ['confidence' => 'Confidence', 'investigate' => 'Investigate further', 'no_opinion' => 'No opinion'];

    public static function waitDays(string $role): int { return self::WAIT_DAYS[$role] ?? max(self::WAIT_DAYS); }

    /* Same formula as the real site: 20 % of active accounts, at least 1. Counted from the mock rows. */
    public static function petitionThreshold(): int {
        $n = count(where('users', fn ($u) => $u['status'] === 'ACTIVE'));
        return max(1, min($n, (int) ceil($n * 0.20)));
    }
}
