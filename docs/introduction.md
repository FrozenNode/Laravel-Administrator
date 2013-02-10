# Introduction

- [Overview](#overview)
- [Customization](#customization)
- [Authentication](#authentication)
- [Eloquent](#eloquent)
- [Installation / Guidance](#installation-guidance)

<img src="https://raw.github.com/FrozenNode/Laravel-Administrator/master/examples/images/overview.jpg" />

<a name="overview"></a>
##Overview

Administrator is a database interface bundle for the Laravel PHP framework that provides a visual interface to administer the Eloquent data models and their relationships.

For each model you can define which fields an administrative user can edit, which columns to display in the table's output rows, and the filters that they will be able to use. These edit fields can also be "belongs to" and "has many and belongs to" relationships, allowing your users to easily manage how data on your site is related.


<a name="customization"></a>
##Customization

Administrator is highly customizable. Among many other things, it gives you control over how table rows are displayed (including custom SQL selects and related columns), the ability to define custom action buttons, and the default sort options for a model.


<a name="authentication"></a>
##Authentication

Unlike many other admin interface systems, Administrator doesn't come with authentication built in. We feel that, instead of providing an extra auth layer on top of what you already have, an admin system should pipe into your existing authentication. By using "permission" anonymous functions, you can use your auth system to determine if the current user should have access something.


<a name="eloquent"></a>
##Eloquent

Most importantly, Administrator is built with [the Eloquent ORM](http://laravel.com/docs/database/eloquent) in mind, so it won't get in the way of you using normal Eloquent features like getters, setters, and events.


<a name="installation-guidance"></a>
##Installation / Guidance

To get started with Administrator, check out the [installation guide](/docs/installation).

If you need some guidance, check out the [tutorials](/docs/tutorials).