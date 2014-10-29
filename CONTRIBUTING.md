# Contributing To Administrator

> **NOTE**: Submit all pull requests to the `dev` branch!

- [Introduction](#introduction)
- [Bugs, Questions, and Feature Requests](#issues)
- [Pull Requests](#pull-requests)
- [Style Guide](#style-guide)
- [CSS Build](#css-build)

<a name="introduction"></a>
## Introduction

Administrator's source is hosted on [GitHub](https://github.com/FrozenNode/Laravel-Administrator). It's distributed under the MIT license, so you are free to fork it and do whatever you like with it. If you're doing something awesome with Administrator, we'd love to hear about it!

<a name="issues"></a>
## Bugs, Questions, and Feature Requests

If you've found a bug with Administrator, if you have a question, or if you have a feature request, the best way to get our attention is to post an issue on the [GitHub issue tracker](https://github.com/FrozenNode/Laravel-Administrator/issues). A significant portion of Administrator's features have been developed because someone asked if it could be included. So don't be afraid to ask away!

<a name="pull-requests"></a>
## Pull Requests

We love it when people submit pull requests. They don't always get merged into the core, but they almost always make us think about what is possible with Administrator and whether or not our current approach is adequate. If you'd like to submit a pull request, there are a few things that you should do in order to ensure a timely response:

- Fork from the `dev` branch. Also submit your PR to the `dev` branch. PRs that are submitted to the `master` branch will be closed immediately.

- Merge the latest changes from the `dev` branch before you submit the pull request. If you have a request that can't be automatically merged, you may be asked to marge the latest changes and resubmit it.

- Add documentation for your changes to the relevant section in the `/docs` directory.

- Add any necessary unit tests

- Follow the [style guide](/docs/style-guide)!

<a name="style-guide"></a>
## Style Guide

Please see the [style guide](/docs/style-guide) page for more information about the style guide.

<a name="css-build"></a>
## CSS Build

Administrator currently uses [LESS](http://lesscss.org/) to build its CSS. In particular, it uses the [lessphp](https://github.com/leafo/lessphp) library. If put this in your composer.json:

	"leafo/lessphp": "dev-master"

And then if you're building administrator from the workbench, you would run:

	$less = new lessc();
	$adminPath = base_path() . '/workbench/frozennode/administrator/public';

	//compile the less
	$compiled = $less->compileFile($adminPath . '/css/main.less');
	File::put($adminPath . '/css/main.css', $compiled);

In the future, Administrator will move to [SASS](http://sass-lang.com/) and use [Grunt](http://gruntjs.com/) to automatically build both CSS and JS files.