# Laravel Administrator Bundle

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is extend your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 3.1.0

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />


## Documentation

The complete docs for Administrator can be found at http://administrator.frozennode.com. You can also find the docs in the `/docs` directory.


## Copyright and License
Administrator was written by Jan Hartigan of Frozen Node for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.


## Changelog

### 3.1.0
- Localization support. Big thanks to [Andrew Dworn](https://github.com/andrewdworn) for all the work he put into this
- New editable option for most edit fields lets you disable field editing
- Image field originals can now be stored in any location (not just the public directory)
- Bugfix: If a relationship has no value for a field, the previously-selected item's relationships will be cleared out
- Bugfix: Bool field now doesn't revert back to false on edit if checked
- Bugfix: CKEditor no longer has funny cursor behavior when editing
- Bugfix: BelongsTo edit fields now load even if they aren't specified in the columns list
- Bugfix: HasOne and HasMany relationship columns weren't being constructed properly


See *changelog.md* for the changelog from previous versions