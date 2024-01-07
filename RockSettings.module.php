<?php

namespace ProcessWire;

use RockSettings\SettingsPage;

/**
 * @author Bernhard Baumrock, 06.01.2024
 * @license COMMERCIAL DO NOT DISTRIBUTE
 * @link https://www.baumrock.com
 */
class RockSettings extends WireData implements Module, ConfigurableModule
{
  public $showPage = false;

  public function init(): void
  {
    if (!$this->showPage) $this->hideSettingsPage();
  }

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

  /**
   * Add hooks to hide settingspage from pagetree
   */
  protected function hideSettingsPage(): void
  {
    $this->wire->addHookAfter(
      "ProcessPageList::find",
      function (HookEvent $event) {
        if ($event->arguments("page")->id !== 1) return;
        $event->return->remove("template=" . SettingsPage::tpl);
      }
    );
    $this->wire->addHookAfter(
      "ProcessPageListRender::getNumChildren",
      function (HookEvent $event) {
        if ($event->arguments("page")->id !== 1) return;
        $event->return = $event->return - 1;
      }
    );
  }

  /**
   * Config inputfields
   * @param InputfieldWrapper $inputfields
   */
  public function getModuleConfigInputfields($inputfields)
  {
    $inputfields->add([
      'type' => 'checkbox',
      'name' => 'showPage',
      'label' => 'Show SettingsPage in Pagetree',
      'checked' => $this->showPage ? 'checked' : '',
    ]);
    return $inputfields;
  }
}
