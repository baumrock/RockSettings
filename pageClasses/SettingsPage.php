<?php

namespace RockSettings;

use ProcessWire\FieldtypeFile;
use ProcessWire\FieldtypePage;
use ProcessWire\FieldtypeTextarea;
use ProcessWire\HookEvent;
use ProcessWire\Inputfield;
use ProcessWire\Page;
use RockMigrations\MagicPage;

use function ProcessWire\rockmigrations;
use function ProcessWire\wire;

function settings(): SettingsPage|Page
{
  return wire()->pages->get('/settings');
}

class SettingsPage extends Page
{
  use MagicPage;

  const tpl = "rocksettings";
  const prefix = "rocksettings_";

  const field_logo = self::prefix . "logo";
  const field_favicon = self::prefix . "favicon";
  const field_redirects = self::prefix . "redirects";
  const field_phone = self::prefix . "phone";
  const field_mail = self::prefix . "mail";
  const field_facebook = self::prefix . "facebook";
  const field_insta = self::prefix . "insta";
  const field_linkedin = self::prefix . "linkedin";
  const field_contact = self::prefix . "contact";
  const field_hours = self::prefix . "hours";
  const field_footerlinks = self::prefix . "footerlinks";
  const field_topbar = self::prefix . "topbar";
  const field_footer = self::prefix . "footer";

  public function init(): void
  {
    $this->wire('settings', settings());
    $this->addSettingsRedirects();
    $this->addHookBefore("Pages::trash", $this, "preventSettingsTrash");
    $this->addHookBefore("Pages::delete", $this, "preventSettingsTrash");
    $this->addHookAfter("ProcessPageEdit::buildForm", $this, "removeSettingsDelete");
  }

  /** magic */

  /** frontend */

  public function mail($link = false): string
  {
    $mail = $this->getFormatted('email');
    if ($link) return "mailto:$mail";
    return $mail;
  }

  public function phone($link = false): string
  {
    $phone = $this->getFormatted(self::field_phone);
    if ($link) {
      $link = str_replace([' ', '/', '-', '(', ')'], '', $phone);
      return "tel:$link";
    }
    return $phone;
  }

  /** backend */

  /**
   * Add hooks for short-url feature on settings page
   */
  private function addSettingsRedirects(): void
  {
    return; // TBD

    // reset cache if field was saved
    if (
      $this->wire->page->id === 10 // page edit
      && $data = $this->wire->input->post('settings_redirects')
    ) {
      $data = $this->parseRedirects($data);
      $this->wire->cache->save('settings-redirects', $data);
      $this->message("Saved " . count($data) . " redirect rules to cache");
    }

    // get redirects from cache
    $redirects = $this->wire->cache->get('settings-redirects');
    if (!is_array($redirects)) return;

    // add redirect hook for every item
    foreach ($redirects as $from => $to) {
      $this->wire->addHook("/$from", function (HookEvent $event) use ($to) {
        $event->wire->session->redirect($to);
      });
    }
  }

  public function migrate()
  {
    $rm = rockmigrations();

    $fields = [
      self::field_logo => [
        'type' => 'image',
        'label' => 'Logo',
        'maxFiles' => 1,
        'descriptionRows' => 0,
        'extensions' => 'jpg jpeg gif png svg',
        'maxSize' => 3, // max 3 megapixels
        'okExtensions' => ['svg'],
        'icon' => 'picture-o',
        'outputFormat' => FieldtypeFile::outputFormatSingle,
        'gridMode' => 'grid', // left, list
        'columnWidth' => 50,
      ],
      self::field_favicon => [
        'type' => 'image',
        'label' => 'Favicon',
        'maxFiles' => 1,
        'descriptionRows' => 0,
        'extensions' => 'png svg ico',
        'okExtensions' => ['svg'],
        'icon' => 'picture-o',
        'outputFormat' => FieldtypeFile::outputFormatSingle,
        'gridMode' => 'grid', // left, list
        'columnWidth' => 50,
      ],

      self::field_redirects => [
        'type' => 'textarea',
        'label' => 'Redirects',
        'rows' => 5,
        'icon' => 'forward',
        'notes' => "Enter one redirect per line.
          example --> https://www.example.com",
        'collapsed' => Inputfield::collapsedBlank,
      ],

      // top-bar
      self::field_topbar => [
        'type' => 'FieldsetOpen',
        'label' => 'Top-Bar',
      ],
      self::field_phone => [
        'type' => 'text',
        'label' => 'Phone',
        'icon' => 'phone-square',
        'textformatters' => [
          'TextformatterEntities',
        ],
        'columnWidth' => 50,
      ],
      self::field_mail => [
        'type' => 'text',
        'label' => 'E-Mail',
        'icon' => 'envelope-o',
        'textformatters' => [
          'TextformatterEntities',
        ],
        'columnWidth' => 50,
      ],
      self::field_facebook => [
        'type' => 'URL',
        'label' => 'Facebook',
        'icon' => 'facebook',
        'textformatters' => [
          'TextformatterEntities',
        ],
        'columnWidth' => 33,
      ],
      self::field_insta => [
        'type' => 'URL',
        'label' => 'Instagram',
        'icon' => 'instagram',
        'textformatters' => [
          'TextformatterEntities',
        ],
        'columnWidth' => 33,
      ],
      self::field_linkedin => [
        'type' => 'URL',
        'label' => 'LinkedIn',
        'icon' => 'linkedin-square',
        'textformatters' => [
          'TextformatterEntities',
        ],
        'columnWidth' => 33,
      ],
      self::field_topbar . "_END" => [
        'type' => 'FieldsetClose',
      ],

      self::field_footer => [
        'type' => 'FieldsetOpen',
        'label' => 'Footer',
      ],
      self::field_contact => [
        'type' => 'textarea',
        'inputfieldClass' => 'InputfieldTinyMCE',
        'contentType' => FieldtypeTextarea::contentTypeHTML,
        'label' => 'Contact',
        'rows' => 5,
        'icon' => 'map-pin',
        'inlineMode' => true,
        'settingsFile' => '/site/modules/RockMigrations/TinyMCE/simple.json',
        'columnWidth' => 33,
      ],
      self::field_hours => [
        'type' => 'textarea',
        'inputfieldClass' => 'InputfieldTinyMCE',
        'contentType' => FieldtypeTextarea::contentTypeHTML,
        'label' => 'Opening Hours',
        'rows' => 5,
        'icon' => 'clock-o',
        'inlineMode' => true,
        'settingsFile' => '/site/modules/RockMigrations/TinyMCE/simple.json',
        'columnWidth' => 33,
      ],
      self::field_footerlinks => [
        'type' => 'page',
        'label' => 'Footer-Menu',
        'derefAsPage' => FieldtypePage::derefAsPageArray,
        'inputfield' => 'InputfieldPageListSelectMultiple',
        'findPagesSelector' => 'id>0,template!=admin',
        'labelFieldName' => 'title',
        'icon' => 'sitemap',
        'columnWidth' => 33,
      ],
      self::field_footer . "_END" => [
        'type' => 'FieldsetClose',
      ],
    ];
    foreach ($fields as &$data) {
      if (!is_array($data)) continue;
      $data['tags'] = "RockSettings";
    }
    $rm->migrate([
      'fields' => $fields,
      'templates' => [
        self::tpl => [
          'fields' => array_merge([
            'title' => [
              'collapsed' => Inputfield::collapsedHidden,
            ],
          ], $fields),
          'icon' => 'cogs',
          'noSettings' => true,
          'noChildren' => true,
        ],
      ],
    ]);
    $rm->createPage(
      template: SettingsPage::tpl,
      parent: 1,
      name: 'rocksettings',
      title: 'Settings',
      status: ['hidden'],
    );
  }

  /**
   * Allow trashing the settings page only for superusers
   */
  protected function preventSettingsTrash(HookEvent $event): void
  {
    $page = $event->arguments('page');
    if (!$page instanceof SettingsPage) return;
    if ($this->wire->user->isSuperuser()) return;
    $this->error("Deleting this page is only allowed for superusers!");
    $event->return = false;
    $event->replace = true;
  }

  /**
   * Remove delete tab of settingspage
   */
  protected function removeSettingsDelete(HookEvent $event): void
  {
    $page = $event->object->getPage();
    if (!$page instanceof SettingsPage) return;
    $form = $event->return;
    $fieldset = $form->find("id=ProcessPageEditDelete")->first();
    $form->remove($fieldset);
    $event->object->removeTab("ProcessPageEditDelete");
    $event->return = $form;
  }
}
