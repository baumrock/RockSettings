<?php

namespace ProcessWire;

$info = [
  'title' => 'RockSettings',
  'version' => json_decode(file_get_contents(__DIR__ . "/package.json"))->version,
  'summary' => 'Creates a dedicated SettingsPage with common fields and features',
  'autoload' => true,
  'singular' => true,
  'icon' => 'cogs',
  'requires' => [
    'PHP>=8.1',
    'RockMigrations>=3.35',
  ],
];
