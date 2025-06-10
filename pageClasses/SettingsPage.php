<?php

namespace RockSettings;

use ProcessWire\FieldtypeFile;
use ProcessWire\FieldtypePage;
use ProcessWire\FieldtypeTextarea;
use ProcessWire\HookEvent;
use ProcessWire\Inputfield;
use ProcessWire\InputfieldFieldsetOpen;
use ProcessWire\InputfieldText;
use ProcessWire\Page;
use ProcessWire\RepeaterPage;
use RockMigrations\MagicPage;

use function ProcessWire\rockmigrations;
use function ProcessWire\wire;

function rocksettings(): SettingsPage|Page
{
  return wire()->pages->get('/rocksettings');
}

class SettingsPage extends Page
{
  use MagicPage;

  const tpl = "rocksettings";
  const prefix = "rocksettings_";

  const field_logo        = self::prefix . "logo";
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
  const field_x           = self::prefix . "x";
  const field_youtube     = self::prefix . "youtube";

  public function init(): void
  {
    // load the page into the $settings variable
    // don't use $this here, because $this is a runtime page without data
    $this->wire('rocksettings', rocksettings());

    $this->addRedirectHooks();
    wire()->addHookBefore("Pages::trash",              $this, "preventSettingsTrash");
    wire()->addHookBefore("Pages::delete",             $this, "preventSettingsTrash");
    wire()->addHookAfter("ProcessPageEdit::buildForm", $this, "hookBuildForm");
    wire()->addHookAfter("Inputfield::render",         $this, "hookAddHost");
    wire()->addHookAfter("Pages::saved",               $this, "saveRedirects");
  }

  /** magic */

  /** frontend */

  public function facebook(): string
  {
    return $this->getFormatted(self::field_facebook) ?: '';
  }

  public function insta(): string
  {
    return $this->getFormatted(self::field_insta) ?: '';
  }

  public function mail($link = false): string
  {
    $mail = $this->getFormatted(self::field_mail) ?: '';
    if ($link) return "mailto:$mail";
    return $mail;
  }

  public function mailTag(): string
  {
    $href = $this->mail(true);
    $mail = $this->mail();
    return "<a href='$href'>$mail</a>";
  }

  public function phone($link = false): string
  {
    $phone = $this->getFormatted(self::field_phone) ?: '';
    if ($link) {
      $link = str_replace([' ', '/', '-', '(', ')'], '', $phone);
      return "tel:$link";
    }
    return $phone;
  }

  public function phoneTag(): string
  {
    $href = $this->phone(true);
    $num = $this->phone();
    return "<a href='$href'>$num</a>";
  }

  public function x(): string
  {
    return $this->getFormatted(self::field_x) ?: '';
  }

  public function youtube(): string
  {
    return $this->getFormatted(self::field_youtube) ?: '';
  }

  /** backend */

  /**
   * Add hooks for short-url feature on settings page
   */
  private function addRedirectHooks(): void
  {
    $page = rocksettings();
    if (!$page->id) return;
    $redirects = $page->meta('redirects') ?: [];
    foreach ($redirects as $from => $to) {
      $this->wire->addHook("/$from", function () use ($to) {
        $this->wire->session->redirect($to);
      });
    }
  }

  /**
   * returns module class of installed RTE editor inputfields: InputfieldTinyMCE or InputfieldTinyMCE
   * @return string Inputfield Class
   */
  protected function getRTEModuleClass(): string
  {
    if (wire()->modules->isInstalled('InputfieldTinyMCE')) return 'InputfieldTinyMCE';
    return 'InputfieldCKEditor';
  }

  public function hookAddHost(HookEvent $event): void
  {
    $inputfield = $event->object;
    if (!$inputfield->name) return;
    if (strpos($inputfield->name, 'title_repeater') !== 0) return;
    if (!$inputfield instanceof InputfieldText) return;
    $page = $inputfield->hasPage;
    if (!$page instanceof RepeaterPage) return;
    $forPage = $page->getForPage();
    if (!$forPage instanceof self) return;
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
    $rm->deleteField(self::prefix . "favicon", true);

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
        'collapsed' => Inputfield::collapsedYes,
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
        'gridMode' => 'list', //'grid', left, list
        'columnWidth' => 100,
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

      self::field_x => [
        'type' => 'URL',
        'label' => 'X (Twitter)',
        'icon' => 'twitter-square',
        'textformatters' => [
          'TextformatterEntities',
        ],
        'columnWidth' => 50,
      ],
      self::field_youtube => [
        'type' => 'URL',
        'label' => 'YouTube',
        'icon' => 'youtube',
        'textformatters' => [
          'TextformatterEntities',
        ],
        'columnWidth' => 50,
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
        'inputfieldClass' => $this->getRTEModuleClass(),
        'contentType' => FieldtypeTextarea::contentTypeHTML,
        'label' => 'Contact',
        'rows' => 5,
        'icon' => 'map-pin',
        'inlineMode' => true,
        'settingsFile' => $this->getRTEModuleClass() === 'InputfieldTinyMCE'
          ? '/site/modules/RockMigrations/TinyMCE/simple.json'
          : '',
        'columnWidth' => 33,
      ],
      self::field_hours => [
        'type' => 'textarea',
        'inputfieldClass' => $this->getRTEModuleClass(),
        'contentType' => FieldtypeTextarea::contentTypeHTML,
        'label' => 'Opening Hours',
        'rows' => 5,
        'icon' => 'clock-o',
        'inlineMode' => true,
        'settingsFile' => $this->getRTEModuleClass() === 'InputfieldTinyMCE'
          ? '/site/modules/RockMigrations/TinyMCE/simple.json'
          : '',
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
    $fieldsTemplateContext = array_merge([
      'title' => [
        'collapsed' => Inputfield::collapsedHidden,
      ],
    ], array_keys($fields));
    $rm->migrate([
      'fields' => $fields,
      'templates' => [
        self::tpl => [
          'fields' => $fieldsTemplateContext,
          'icon' => 'cogs',
          'noSettings' => true,
          'noChildren' => true,
          'noParents' => -1, // only one page allowed
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
          $f->notes = trim($f->notes . "\nAPI: \$rocksettings->$short()");
          if ($short == "phone") {
            $f->notes .= "\nAPI: \$rocksettings->$short(true) for tel:... link in href";
            $f->notes .= "\nAPI: \$rocksettings->phoneTag() for <a href=tel:...>...</a>";
          }
          if ($short == "mail") {
            $f->notes .= "\nAPI: \$rocksettings->$short(true) for mailto:... link in href";
            $f->notes .= "\nAPI: \$rocksettings->mailTag() for <a href=mailto:...>...</a>";
          }
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
