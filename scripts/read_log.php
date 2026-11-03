<?php

$lines = file(__DIR__ . '/../storage/logs/laravel.log');
$recent = array_slice($lines, -150);
echo implode('', $recent);
