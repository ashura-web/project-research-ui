<?php
/* app/config.php - settings for the research mock.
   Copyright (c) 2026 Ar Kar Phyo. All rights reserved. See LICENSE.

   Nothing in this file is secret. The mock has no API keys, no mail server, no OAuth client and no real
   accounts. If you ever copy this structure into a real build, keep real secrets in a file that is NOT
   committed (the real project used a git-ignored secrets file for that). */
defined('MOCK') || exit;

/* Shown in the sidebar, the top bar and every <title>. Change it to preview your own brand name. */
const APP_NAME = 'Project Name';
const APP_TAGLINE = 'A reviewed community wiki (UI/UX research mock)';
const APP_OWNER = 'Ar Kar Phyo';
const APP_YEAR = '2026';

/* All page data lives in app/mock-data.php. This research folder deliberately ships no database. */

/* Every page shows this role until you pick another one with "Preview as" in the sidebar. */
const DEFAULT_ROLE = 'guest';
