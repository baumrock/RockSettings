<?php

namespace RockSettings;

use ProcessWire\FieldtypeFile;
use ProcessWire\FieldtypePage;
use ProcessWire\FieldtypeTextarea;
use ProcessWire\HookEvent;
use ProcessWire\Inputfield;
use ProcessWire\InputfieldFieldsetOpen;
use ProcessWire\Page;
use RockMigrations\MagicPage;

use function ProcessWire\rockmigrations;
use function ProcessWire\wire;

function settings(): SettingsPage|Page
{
  return wire()->pages->get('/rocksettings');
}

class SettingsPage extends Page
{
  use MagicPage;

  const tpl = "rocksettings";
  const prefix = "rocksettings_";

  const field_logo        = self::prefix . "logo";
  const field_favicon     = self::prefix . "favicon";
  const field_redirects   = self::prefix . "redirects";
  const field_phone       = self::prefix . "phone";
  const field_mail        = self::prefix . "mail";
  const field_facebook    = self::prefix . "facebook";
  const field_insta       = self::prefix . "insta";
  const field_linkedin    = self::prefix . "linkedin";
  const field_contact     = self::prefix . "contact";
  const field_hours       = self::prefix . "hours";
  const field_footerlinks = self::prefix . "footerlinks";
  const field_topbar      = self::prefix . "topbar";
  const field_footer      = self::prefix . "footer";
  const field_target      = self::prefix . "target";
  const field_media       = self::prefix . "media";
  const field_images      = self::prefix . "images";
  const field_files       = self::prefix . "files";
  const field_ogimage     = self::prefix . "ogimage";

  public function init(): void
  {
    // load the page into the $settings variable
    // don't use $this here, because $this is a runtime page without data
    $this->wire('settings', settings());

    $this->addRedirectHooks();
    $this->addHookBefore("Pages::trash", $this, "preventSettingsTrash");
    $this->addHookBefore("Pages::delete", $this, "preventSettingsTrash");
    $this->addHookAfter("ProcessPageEdit::buildForm", $this, "hookBuildForm");
    $this->addHookAfter("Inputfield::render", $this, "hookAddHost");
    $this->addHookAfter("Pages::saved", $this, "saveRedirects");
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
  private function addRedirectHooks(): void
  {
    $page = settings();
    if (!$page->id) return;
    $redirects = $page->meta('redirects') ?: [];
    foreach ($redirects as $from => $to) {
      $this->wire->addHook("/$from", function () use ($to) {
        $this->wire->session->redirect($to);
      });
    }
  }

  public function hookAddHost(HookEvent $event): void
  {
    if (!str_starts_with($event->object->name, "title_repeater")) return;
    $markup = $event->return;
    $markup = "<div class='uk-flex uk-flex-middle'>
      <span class='uk-margin-small-right uk-visible@m'>{$this->wire->config->httpHost}/</span>
      $markup
      </div>";
    $event->return = $markup;
  }

  public function migrate()
  {
    $rm = rockmigrations();

    $rm->createField(self::field_target, [
      'type' => 'URL',
      'label' => 'Target',
      'icon' => 'bullseye',
      'textformatters' => [
        'TextformatterEntities',
      ],
      'required' => true,
      'tags' => 'RockSettings',
    ]);

    $fields = [
      self::field_media => [
        'type' => 'FieldsetOpen',
        'label' => 'Media',
        'icon' => 'file-image-o',
      ],
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
        'collapsed' => Inputfield::collapsedNo,
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
        'collapsed' => Inputfield::collapsedNo,
      ],
      self::field_ogimage => [
        'type' => 'image',
        'label' => 'og:image',
        'notes' => 'You can use this image as fallback for the og:image meta tag.',
        'maxFiles' => 1,
        'descriptionRows' => 0,
        'columnWidth' => 100,
        'extensions' => 'png jpg jpeg',
        'maxSize' => 3, // max 3 megapixels
        'icon' => 'picture-o',
        'outputFormat' => FieldtypeFile::outputFormatSingle,
        'collapsed' => Inputfield::collapsedBlank,
      ],
      self::field_images => [
        'type' => 'image',
        'label' => 'Images',
        'maxFiles' => 0,
        'descriptionRows' => 1,
        'extensions' => 'jpg jpeg gif png svg',
        'maxSize' => 3, // max 3 megapixels
        'okExtensions' => ['svg'],
        'icon' => 'picture-o',
        'outputFormat' => FieldtypeFile::outputFormatSingle,
        'gridMode' => 'grid', // left, list
        'collapsed' => Inputfield::collapsedBlank,
      ],
      self::field_files => [
        'type' => 'file',
        'label' => 'Files',
        'maxFiles' => 0,
        'descriptionRows' => 1,
        'extensions' => 'pdf zip',
        'icon' => 'files-o',
        'outputFormat' => FieldtypeFile::outputFormatArray,
        'collapsed' => Inputfield::collapsedBlank,
      ],
      self::field_media . "_END" => [
        'type' => 'FieldsetClose',
      ],

      self::field_redirects => [
        'label' => 'Redirects',
        'type' => 'FieldtypeRepeater',
        'fields' => [
          'title' => [
            'label' => 'Url-Segment',
            'columnWidth' => 50,
          ],
          self::field_target => [
            'columnWidth' => 50,
          ],
        ],
        'repeaterTitle' => '{title} ➜ {' . self::field_target . '}',
        'familyFriendly' => 1,
        'repeaterDepth' => 0,
        'repeaterAddLabel' => 'Add New Item',
        'columnWidth' => 100,
        'collapsed' => Inputfield::collapsedBlank,
        'icon' => 'forward',
        'notes' => '',
      ],

      // top-bar
      self::field_topbar => [
        'type' => 'FieldsetOpen',
        'label' => 'Top-Bar',
        'collapsed' => Inputfield::collapsedYes,
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
        'collapsed' => Inputfield::collapsedYes,
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

    // make redirects be single-language
    $tpl = $rm->getRepeaterTemplate(self::field_redirects);
    $rm->setTemplateData($tpl, ['noLang' => true]);

    // if the Site module is installed we trigger Site::migrateSettingsPage
    $site = $this->wire->modules->get('Site');
    if ($site and method_exists($site, "migrateSettingsPage")) {
      $site->migrateSettingsPage($this);
    }
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
   * Modifications for the page editor
   */
  protected function hookBuildForm(HookEvent $event): void
  {
    $page = $event->object->getPage();
    if (!$page instanceof SettingsPage) return;
    $form = $event->return;

    // remove settings tab
    $fieldset = $form->find("id=ProcessPageEditDelete")->first();
    $event->object->removeTab("ProcessPageEditDelete");
    $form->remove($fieldset);

    // add superuser notes
    if ($this->wire->user->isSuperuser()) {
      $notes = $page->fields->each('name');
      foreach ($notes as $name) {
        if ($f = $form->get($name)) {
          if ($f instanceof InputfieldFieldsetOpen) continue;
          $parts = explode("_", $name);
          $short = end($parts);
          if ($short == "redirects") continue;
          $f->notes = trim($f->notes . "\nAPI: \$settings->$short()");
        }
      }
    }

    $event->return = $form;
  }

  protected function saveRedirects(HookEvent $event): void
  {
    $page = $event->arguments('page');
    if (!$page instanceof self) return;
    $arr = [];
    foreach ($page->getFormatted(self::field_redirects) as $item) {
      $arr[(string)$item->title] = $item->getFormatted(self::field_target);
    }
    $page->meta('redirects', $arr);
  }
}
