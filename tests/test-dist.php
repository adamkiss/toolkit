<?php

$v = trim(shell_exec('git describe --tags --abbrev=0'));
require_once __DIR__ . "/../dist/toolkit-{$v}.php";
require __DIR__ . '/base.php';
