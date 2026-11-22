<?php
/* app/pages.php - every page of the mock: slug => [title, lowest role that may open it, breadcrumb parent].
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.
   Adapted from the real front controller's PAGES map and the layout's $backParents list. */
defined('MOCK') || exit;

const PAGES = [
    // public (client side, no account needed)
    'home'            => ['Home', 'guest', null],
    'about'           => ['About', 'guest', 'home'],
    'explore'         => ['Explore', 'guest', 'home'],
    'gallery'         => ['Gallery', 'guest', 'home'],
    'glossary'        => ['Glossary', 'guest', 'home'],
    'help'            => ['Help centre', 'guest', 'home'],
    'media'           => ['Media', 'guest', 'home'],
    'article'         => ['Article', 'guest', 'gallery'],
    'note'            => ['Community note', 'guest', 'explore'],
    'member'          => ['Member profile', 'guest', 'about'],
    'random'          => ['Random article', 'guest', 'home'],
    'contact'         => ['Contact', 'guest', 'home'],
    'pages'           => ['All pages (research index)', 'guest', 'home'],
    // account
    'login'           => ['Log in', 'guest', null],
    'two-step'        => ['Second step', 'guest', null],
    'signup'          => ['Sign up', 'guest', null],
    'choose-username' => ['Choose a username', 'guest', null],
    'forgot-password' => ['Forgot password', 'guest', 'login'],
    'reset-password'  => ['Reset password', 'guest', 'login'],
    'unsubscribe'     => ['Email preferences', 'guest', 'home'],
    // workspace (signed-in members)
    'dashboard'       => ['Dashboard', 'member', 'home'],
    'write-note'      => ['Write a note', 'member', 'dashboard'],
    'media-submit'    => ['Upload media', 'member', 'media'],
    'contribute'      => ['Write an article', 'contributor', 'dashboard'],
    'profile'         => ['My profile', 'member', 'dashboard'],
    'store'           => ['Cosmetics store', 'member', 'profile'],
    'rank-request'    => ['Rank request', 'member', 'dashboard'],
    'notifications'   => ['Notification centre', 'member', 'dashboard'],
    // governance
    'governance'        => ['Governance', 'member', 'dashboard'],
    'governance-submit' => ['File a complaint', 'member', 'governance'],
    'governance-case'   => ['Governance case', 'member', 'governance'],
    'petitions'         => ['Petitions', 'member', 'governance'],
    'petition'          => ['Petition', 'member', 'petitions'],
    'poll'              => ['Confidence poll', 'member', 'governance'],
    // reviewer
    'review-queue'    => ['Review queue', 'reviewer', 'dashboard'],
    'requests'        => ['Rank requests board', 'reviewer', 'dashboard'],
    'bulletins'       => ['Bulletin desk', 'reviewer', 'dashboard'],
    // admin side
    'admin'           => ['Admin console', 'admin', 'dashboard'],
    'users'           => ['Users', 'admin', 'admin'],
    'inbox'           => ['Support inbox', 'admin', 'admin'],
    'audit-log'       => ['Audit log', 'admin', 'admin'],
    'mail-outbox'     => ['Email outbox', 'admin', 'admin'],
    // owner
    'owner'           => ['Owner console', 'owner', 'dashboard'],
    'owner-appointments' => ['Admin appointments', 'owner', 'owner'],
    'content'         => ['Content manager', 'owner', 'admin'],
    'setup'           => ['First-time setup', 'owner', 'owner'],
    // errors
    '404'             => ['Page not found', 'guest', 'home'],
    'denied'          => ['Access denied', 'guest', 'home'],
];

/* Sidebar groups: [label, lowest role, [[slug, label, icon], ...]]. */
const NAV = [
    ['Explore', 'guest', [['home', 'Home', 'home'], ['about', 'About', 'info'], ['explore', 'Explore', 'search'], ['gallery', 'Gallery', 'grid'],
                          ['glossary', 'Glossary', 'book'], ['media', 'Media', 'video'], ['help', 'Help centre', 'info']]],
    ['Workspace', 'member', [['dashboard', 'Dashboard', 'grid'], ['contribute', 'Write an article', 'pen'], ['write-note', 'Write a note', 'plus'],
                             ['media-submit', 'Upload media', 'upload'], ['store', 'Cosmetics store', 'bag'], ['governance', 'Governance', 'scales']]],
    ['Review', 'reviewer', [['review-queue', 'Review queue', 'check'], ['requests', 'Rank requests', 'userplus'], ['bulletins', 'Bulletin desk', 'megaphone']]],
    ['Admin', 'admin', [['admin', 'Admin console', 'shield'], ['users', 'Users', 'users'], ['inbox', 'Support inbox', 'inbox'],
                        ['audit-log', 'Audit log', 'book'], ['mail-outbox', 'Email outbox', 'mail']]],
    ['Owner', 'owner', [['owner', 'Owner console', 'gavel'], ['content', 'Content manager', 'trash']]],
    ['Research', 'guest', [['pages', 'All pages', 'link']]],
];

/* Account dropdown (top bar avatar). */
const ACCOUNT_MENU = [['profile', 'My profile', 'user'], ['dashboard', 'Dashboard', 'grid'], ['store', 'Cosmetics store', 'bag'],
                      ['notifications', 'Notifications', 'bell'], ['rank-request', 'Rank request', 'star']];
