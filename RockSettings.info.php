<?php

namespace ProcessWire;

$info = [
  'title' => 'RockSettings',
  'version' => json_decode(file_get_contents(__DIR__ . "/package.json"))->version,
  'summary' => 'Manage common site settings like a boss 😎🤘',
  'autoload' => true,
  'singular' => true,
  'icon' => 'cogs',
  'requires' => [
    'PHP>=8.1',
    'RockMigrations>=3.35',
  ],
];
