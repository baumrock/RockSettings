<?php

namespace ProcessWire;

/**
 * @author Bernhard Baumrock, 06.01.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class RockSettings extends WireData implements Module
{
  public function ready(): void
  {
    $this->addSettingsIcon();
  }

  private function addSettingsIcon(): void
  {
    if ($this->wire->page->template != 'admin') return;
    if ($this->wire->config->ajax) return;
    if ($this->wire->external) return;
    $settingsPage = $this->wire->pages->get("/rocksettings");
    if (!$settingsPage->id) return;
    if (!$settingsPage->editable()) return;
    $this->addHookAfter("Page::render", function (HookEvent $event) use ($settingsPage) {
      $html = $event->return;
      $icon = "<a
          href='{$settingsPage->editUrl()}'
          class='uk-link-reset'
          title='Settings' uk-tooltip='pos:bottom'
        >
        <i class='fa fa-cogs'></i>
        </a>";
      $html = str_replace(
        '<div class="uk-navbar-right">',
        '<div class="uk-navbar-right">' . $icon,
        $html
      );
      $event->return = $html;
    });
  }
}
