<?php

$v = json_decode(file_get_contents(__DIR__ . '/../composer.json'))->version;
require_once __DIR__ . "/../dist/toolkit-{$v}.php";
require __DIR__ . '/base.php';
