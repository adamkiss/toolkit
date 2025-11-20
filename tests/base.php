<?php

use Adamkiss\Toolkit\A;
use Adamkiss\Toolkit\Data\Data;
use Adamkiss\Toolkit\Str;

$beatles = Data::read(__DIR__ . '/_fixture.php', 'php');
$beatles = A::map($beatles, fn ($b) => Str::ucfirst($b));
$result = "The beatles are: " . A::join($beatles, ', ') . '.';
echo $result . PHP_EOL;
