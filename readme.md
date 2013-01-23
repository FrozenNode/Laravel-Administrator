# Laravel Administrator Bundle

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is extend your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 3.0.0

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/3.0.0/examples/images/overview.jpg" />


## Documentation

The complete docs for Administrator can be found at http://administrator.frozennode.com. You can also find the docs in the `/docs` directory.


## Copyright and License
Administrator was written by Jan Hartigan of Frozen Node for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.


## Changelog

### 3.0.0
- Model configuration must now be done in model config files instead of in an Eloquent model
- Revamped the docs to make it more accessible/readable
- You can now group together models into menu groups
- New color field
- New image field
- Custom column outputs
- Admin users can now set a custom number of rows in each model's interface
- You can now add custom action buttons in the $actions property of a model
- You can now apply per-model permissions for creating, saving, and deleting items
- Renamed 'permission_check' and 'auth_check' to the uniform 'permission'
- Renamed 'global_per_page' to 'global_rows_per_page'
- The $edit property is now the 'edit_fields' option in the model config
- The $expand property is now the 'form_width' option in the model config
- Removed the before_delete() method. This can be handled by using the "eloquent.delete: {{classname}}" event
- Migrated from the old string-based jQuery template engine to the faster, smarter Knockout comment bindings
- Bugfix: BelongsTo filter no longer does a LIKE search (since it's an explicit key)


See *changelog.md* for the changelog from previous versions