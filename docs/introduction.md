# Introduction

- [Overview](#overview)
- [Customization](#customization)
- [Authentication](#authentication)
- [Eloquent](#eloquent)
- [Installation / Guidance](#installation-guidance)

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />

<a name="overview"></a>
##Overview

Administrator is an administrative interface builder for [Laravel](http://laravel.com). With Administrator you can visually manage your Eloquent models and their relations, and also create stand-alone settings pages for storing site data and performing site tasks.

For each Eloquent model you can define which fields an administrative user can edit, which columns to display in the results table, custom action buttons, and the filters that they will be able to use. These fields can also be "belongsTo" and "belongsToMany" relationships (but not "hasOne" and "hasMany" relationships), allowing your users to easily manage how data on your site is related.


<a name="authentication"></a>
##Authentication

Unlike many other admin interface systems, Administrator doesn't come with authentication built in. Instead of providing an extra auth layer on top of what you already have, Administrator pipes into your existing authentication. By using "permission" anonymous functions, you can use your auth system to determine if the current user should have access to something.


<a name="eloquent"></a>
##Eloquent

Most importantly, Administrator is built with [the Eloquent ORM](http://laravel.com/docs/eloquent) in mind, so it won't get in the way of you using normal Eloquent features like accessors, mutators, and events.

> For more on how to configure your Eloquent models with Administrator, see the [model configuration docs](/docs/model-configuration)


<a name="settings-pages"></a>
##Settings Pages

If you want to just have a simple settings page where you get to define the validation rules, the fields, and the actions, you can do that! It's incredibly easy to create a page with any combination of fields (e.g. a checkbox to determine if your site is online or offline) or custom actions (e.g. a "clear cache" button).

> For more on how to create a settings page, see the [settings configuration docs](/docs/settings-configuration)


<a name="installation-guidance"></a>
##Installation / Guidance

To get started with Administrator, check out the [installation guide](/docs/installation).

If you need some guidance, check out the [tutorials](/docs/tutorials).