<?php

namespace RockSettings;

use ProcessWire\FieldtypeFile;
use ProcessWire\FieldtypePage;
use ProcessWire\FieldtypeTextarea;
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

  public function init(): void
  {
    $this->wire('settings', settings());
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
    ];
    foreach ($fields as &$data) $data['tags'] = "RockSettings";
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
}
