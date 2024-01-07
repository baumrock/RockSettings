# Custom Fields

## Via GUI

You can add custom fields as usual via the PW template editor.

The only thing you need to know is that you have to apply all your changes to fields that are handled by the module with template context, otherwise RockSettings will overwrite your changes on the next migration!

### Example

You create a new field `my-social-field`. RockSettings comes with 3 social inputfields: Facebook, Instagram and LinkedIn (and maybe more in the future).

The `columnWidth` of these fields is set to `33` so that they are displayed side-by-side in one row. If you want to add your new field to that row simply add the field to the template and change the width of all four fields to `25` in the context of the `rocksettings` template.

### Removing fields

If you want to remove fields from the settings page just hide them (again using template context) otherwise RockMigrations will recreate them on the next modules refresh!

## Via RockMigrations

RockSettings will trigger `Site::migrateSettingsPage` after each `migrate()` of the SettingsPage. There you can add fields specific to your project or overwrite any migrations done by the module.

## Via Hooks

Of course you can also modify any Aspect of your SettingsPage via Hooks. Here is an example how to lock one field to superusers:

```php
// in site/ready.php
$wire->addHookAfter("ProcessPageEdit::buildFormContent", function (HookEvent $event) {
  $page = $event->object->getPage();
  if (!$page instanceof \RockSettings\SettingsPage) return;

  // execute this only for non-superusers
  if($this->wire->user->isSuperuser()) return;

  $form = $event->return;
  if ($f = $form->get($page::field_logo)) {
    $f->collapsed = Inputfield::collapsedNoLocked;
  }
});
```