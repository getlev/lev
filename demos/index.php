<?php

/**
 * @package    Lev
 *
 * @copyright  Copyright (c) 2021 Levitation, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

// Any request to Lev site could be served by any Lev host installed on a web server.
// Lev host is defined by its abs path on a web server.

// Call desired Lev host responder
require __DIR__ . '/../respond.php';
