# Laravel Administrator Bundle

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to manage the data models on your site as you define them. In its most basic configuration, all you have to do is extend your application's Eloquent data models and provide a couple more configuration options.

- **Author:** Jan Hartigan
- **Website:** [http://frozennode.com](http://frozennode.com)
- **Version:** 3.3.0

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />

## Laravel 4

This bundle currently only works with **Laravel 3**. Laravel 4 should probably be finalized sometime in May 2013, and as such I'm planning on making Administrator 4 compatible with Laravel 4. At that point, I will move Administrator <4 to another GitHub repo and make this repo the primary Administrator 4+ composer repo.

## Documentation

The complete docs for Administrator can be found at http://administrator.frozennode.com. You can also find the docs in the `/docs` directory.


## Copyright and License
Administrator was written by Jan Hartigan of Frozen Node for the Laravel framework.
Administrator is released under the MIT License. See the LICENSE file for details.


## Changelog

### 3.3.0
- You can now define your custom action's permissions in the model config's action_permissions option
- Returning Response or Redirect objects is now possible for admin and model configs
- New language: Brazilian Portuguese (pt-BR)
- Bugfix: Self-relationships weren't updating properly since the move to select2
- Bugfix: Columns for HasMany, HasOne, and HMABT relationships now work


See *changelog.md* for the changelog from previous versions