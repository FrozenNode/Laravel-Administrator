# Introduction

Administrator is a database interface bundle for the Laravel PHP framework. Administrator provides a visual interface to administer the Eloquent data models on your site and their relationships.

For each model you can define which fields an administrative user can edit, which columns to display in the table's output rows, and the filters that they will be able to use. These edit fields can also be "belongs to" and "has many and belongs to" relationships, allowing your users to easily manage how data on your site is related.

Administrator is also highly customizable. Among many other things, it gives you control over how table rows are displayed (including custom SQL selects and related columns), the ability to define custom action buttons, and the default sort options for a model.

Unlike some admin interface systems, Administrator doesn't come with authentication, but we think that's a good thing. An admin system should pipe into your existing authentication, and Administrator allows you to do that. By default, any user can access the admin section of your site, but if you want to restrict access (either to Administrator, specific models, or specific actions on a model), you can provide a "permission" callback in which you can determine whether the current user has the proper role(s).

Most importantly, Administrator is built with Eloquent in mind, so it won't get in the way of you using normal Eloquent features like getters, setters, and events.

To get started with Administrator, check out the [installation guide](/docs/installation).

If you need some guidance, check out the [tutorials](/docs/tutorials).