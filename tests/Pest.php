<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class, RefreshDatabase::class)->in('Unit');

// Module-system tests scaffold, install, and remove real directories under
// Modules. Paratest isolates the database but not that shared filesystem path,
// so every filesystem-mutating module suite runs serially after the parallel
// pass to avoid one worker scanning another worker's staging directory.
uses()->group('modules', 'serial-only')->in(
    'Feature/Admin/Modules',
    'Feature/Company/Modules',
    'Feature/Marketplace',
);

// Architecture assertions parse broad namespace graphs and retain that graph
// for the life of a worker. Run them in the serial phase so an ordinary feature
// test is not handed the parser's memory footprint in the same 128 MB process.
uses()->group('architecture', 'serial-only')->in('Unit/Architecture', 'Feature/Architecture');
