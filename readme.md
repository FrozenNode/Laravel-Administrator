# Laravel Administrator Bundle

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is extend your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 3.2.0

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />

## Laravel 4

This bundle currently only works with **Laravel 3**. Laravel 4 should probably be finalized sometime in May 2013, and as such I'm planning on making Administrator 4 compatible with Laravel 4. At that point, I will move Administrator <4 to another GitHub repo and make this repo the primary Administrator 4+ composer repo.

## Documentation

The complete docs for Administrator can be found at http://administrator.frozennode.com. You can also find the docs in the `/docs` directory.


## Copyright and License
Administrator was written by Jan Hartigan of Frozen Node for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.


## Changelog

### 3.2.0
- Added support for a file field
- You can now choose to provide a custom dashboard or a default home page from your menu
- Settings pages are now available
- It is now possible to set a sort_field on HMABT relationships for inline reordering of related values
- You can now select nested belongs_to relationships in the columns array
- Primary key fields are now hidden by default in the edit window unless you put them in your edit array
- New languages: Spanish (es), Basque (eu), French (fr), Dutch (nl), Polish (pl), Serbian (sr)
- Moved from Chosen select boxes to Select2
- The item link now uses the single name of the model instead of "item"
- Bugfix: Constraints on autocomplete fields now constrain the autocomplete search
- Bugfix: Multiple constraints now work properly
- Bugfix: Character limits on text fields no longer limit the string on every keystroke
- Bugfix: Relationship options now sort by the name field
- Bugfix: Getter columns now visibly show if they're being sorted
- Bugfix: Fixed some issues with the page not resizing properly
- Bugfix: WYSIWYG editor now resets properly after saving and then creating a new item


See *changelog.md* for the changelog from previous versions