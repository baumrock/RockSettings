# Custom Fields

You can add custom fields as usual via the PW template editor.

The only thing you need to know is that you have to apply all your changes to fields that are handled by the module with template context, otherwise RockSettings will overwrite your changes on the next migration!

## Example

You create a new field `my-social-field`. RockSettings comes with 3 social inputfields: Facebook, Instagram and LinkedIn (and maybe more in the future).

The `columnWidth` of these fields is set to `33` so that they are displayed side-by-side in one row. If you want to add your new field to that row simply add the field to the template and change the width of all four fields to `25` in the context of the `rocksettings` template.

## Removing fields

If you want to remove fields from the settings page just hide them (again using template context) otherwise RockMigrations will recreate them on the next modules refresh!
