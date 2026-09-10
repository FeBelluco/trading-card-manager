<?php

declare(strict_types=1);
require dirname(__DIR__) . '/src/auth.php';
start_session();
require_login();
$card = null;
require dirname(__DIR__) . '/src/views/card-form.php';
