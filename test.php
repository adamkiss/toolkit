<?php

require_once __DIR__ . '/vendor/autoload.php';

$test = [1, 2, 3];

ray(A::map($test, fn($n) => $n * 2));
