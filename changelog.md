## Changelog

### 3.2.0
- You can now select nested belongs_to relationships in the columns array
- Primary key fields are now hidden by default in the edit window unless you put them in your edit array
- New languages: Spanish (es), Basque (eu), Dutch (nl), Polish (pl)
- Bugfix: Multiple constraints now work properly

### 3.1.0
- Localization support. Big thanks to [Andrew Dworn](https://github.com/andrewdworn) for all the work he put into this
- New editable option for most edit fields lets you disable field editing
- Image field originals can now be stored in any location (not just the public directory)
- Bugfix: If a relationship has no value for a field, the previously-selected item's relationships will be cleared out
- Bugfix: Bool field now doesn't revert back to false on edit if checked
- Bugfix: CKEditor no longer has funny cursor behavior when editing
- Bugfix: BelongsTo edit fields now load even if they aren't specified in the columns list
- Bugfix: HasOne and HasMany relationship columns weren't being constructed properly

### 3.0.0
- Model configuration must now be done in model config files instead of in an Eloquent model
- Revamped the docs to make it more accessible/readable
- You can now group together models into menu groups
- New 'color' field type
- New 'image' field type
- Custom column outputs
- Admin users can now set a custom number of rows in each model's interface
- You can now add custom action buttons in the $actions property of a model
- You can now apply per-model permissions for creating, saving, and deleting items
- Renamed 'permission_check' and 'auth_check' to the uniform 'permission'
- Renamed 'global_per_page' to 'global_rows_per_page'
- The $edit property is now the 'edit_fields' option in the model config
- The $filters property is now the 'filters' option in the model config
- The $sortOptions property is now the 'sort' option in the model config
- The $expand property is now the 'form_width' option in the model config
- The create_link() method is now the 'link' option in the model config
- Removed the before_delete() method. This can be handled by using the "eloquent.delete: {{classname}}" event
- Migrated from the old string-based jQuery template engine to the faster, smarter Knockout comment bindings
- Bugfix: BelongsTo filter no longer does a LIKE search (since it's an explicit key)

### 2.3.0
- Relationship constraints are now possible if you want to limit one relationship field's options by its relation to another relationship field (only applies when those two fields themselves have a pivot table)
- You can now hit the enter key on text/textarea fields to submit the create/edit form
- Self-relationships are now possible
- Bugfix: Bool field now works properly with SQLite (or any database that returns ints as strings)
- Bugfix: History.js now recognizes base URIs other than '/'
- Bugfix: In PostgreSQL there was an issue with using boolean false to pull back no results on an integer column
- Bugfix: If you are on some high page number and you filter the set such that that page number is now outside the range of the filtered set, you will be brought back to the last page of the set instead of staying on that page
- Bugfix: Adding real-time viewModel updates to the wysiwyg field. Sometimes if you hit "save" fast enough it wouldn't write the changes back to the viewModel from the CKEditor
- Bugfix: There was an array index error when not providing a name_field or when only providing one of the sort options

### 2.2.0
- There is now an autocomplete option for relationships that could have a lot of potential values
- You can now set the $expand property for a model to boolean true or any integer above 285 (i.e. pixels) to get more room for the edit form
- Model config now allows for a 'single' name. Example: Film model would be 'film'. BoxOffice model would be 'take'. i.e. New film, New take
- New 'bool' field type
- New 'enum' field type
- New 'wysiwyg' field type
- New 'textarea' field type
- New 'markdown' field type
- Added 'limit' option for text/textarea/markdown field types
- Added 'height' option for textarea/markdown field types (pixels as an integer)
- You can now provide a create_link method in your model that should return the URL of the string of the item's front-end page
- You can now optionally provide a 'permission_check' closure for each model in the config. This works just like auth_check but on a per-model basis. If provided, and if it evaluates to false, the user will be redirected back to the admin dashboard.
- Bugfix: Multiple commas in number fields were messing up the values
- Bugfix: The custom binding for the number field now uses the user-supplied fields like decimals, thousands_separator, and decimal_separator.
- Bugfix: Various animation bugs in the UI

### 2.1.0
- You can no longer use has_one or has_many fields in the $edit property. This is because those relationships require a new item to be created on the other table.
- The number field now formats nicely in the interface
- Added the first tutorial video to the README and added the code from that video to the examples/application directory
- Bugfix: There was a case sensitivity issue with the libraries folder because of the namespaces I was using. Quickfixed this by changing libraries to Libraries.
- Bugfix: Getting model rows was calling 'SELECT * FROM [whatever_relationship_table]' multiple times. This should alleviate some performance issues.

### 2.0.1
- Bugfix: related to grouping functions in the 'select' option
- Bugfix: related to the model title showing up

### 2.0.0
- Reorganized the libraries
- title_field is now name_field
- relation is now relationship
- currency is now number and non-currency number types are now supported
- $edit and $filters arrays no longer have default values. You must supply them or they won't show up
- $column now accepts a 'select' option for any field to allow for proper sorting
- Temporarily found a work around for a major bug with Laravel paginate() method where it wouldn't properly count the rows when using a grouping (will be fixed in L4)
- Innumerable bugfixes (with plenty more to come)

### 1.2.0
- Added all field types to filters
- Currency (and soon all numbers), date, datetime, and time filters are now a min/max range
- Assorted improvements to make it easier to add field types

### 1.1.0
- Sorting getter columns
- Sorting relational columns with custom select statements
- Fixed several bugs related to sorting
- Fixed several bugs related to using getters as columns

### 1.0.1
- 'id' filter type now works
- Getter values now show up in the result set

### 1.0.0
- Initial release.
