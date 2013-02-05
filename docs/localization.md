# Localization

- [Change language](#change-language)
- [Use multiple languages](#multiple-languages)
- [Text localization in Model Config files](#text-localization-modelconfig)
- [Available languages](#available-languages)

<a name="change-language"></a>
## Change language

The default language for Administrator is English. You can simply change it, by changing laravel's 'language' property in application config file to yourl language.

In Model Config files simply use your languge texts in the proper places.

Check out your language's support details: [Available languages](#available-languages)


<a name="multiple-languages"></a>
## Use multiple languages

In case you want to use multiple languages, you need to define all laguages in Laravel's 'languages' array in the application config file. Set the 'language' property to your default language for initial language.

The Administrator menu is now extended by a new language selector part, which let's you change language on the fly.

In Model Config files you are presented two ways of localization described here: [Text localization in Model Config files](#text-localization-modelconfig)

Check out your language's support details: [Available languages](#available-languages)

<a name="text-localization-modelconfig"></a>
## Text localization in Model Config files

In Model Config files located in /config/administartor/, and the config file /config/administrator.php Laravel's defalult Lang class dosen't work as expected, so you are presented two ways to define your desired text translation:

### Use titles, menus, singles property for language reference

You still can use Laravel's language sturcture by using 'titles', 'menus', 'singles' properies in addition to 'title', 'menu', 'single'. For value, use the desired language line path, that you would use for Lang::line('line_path') or __('line_path') functions.

### Use custum language properties like title_en, menu_en, single_en

You are also presented a way to use inline translation for 'title', 'menu', 'single' properties by using costum property names like 'title_en', 'menu_en', 'single_en', where you can replace the '_en' part with any language code you want to use. You can use as many language variation for the same property as you want (title_en, title_de, title_ru). For value, use the proper translation of the text.


In most places 'title' property is used, and you have one 'single' property in each model's config file. It's currently only used for the create 'New' item button.

You only need to translate 'menu' array, if there is submenu definded in menu. In this case, the array key is used for the title of container menu.
If you use the 'menus' property, you need to set up the same array as 'menu', but change those array keys to the language line path.
If you use costum language properties, like 'menu_en', you have to set up each array as 'menu', and replace the array keys to the proper language text.

The evaluation order is always like: 'title_en', 'titles', 'title'.

<a name="available-languages"></a>
## Available languages

You can find Administartor's language files in /language folder, each language in a language code folder, and each containing two files: administrator.php and knockout.php. If your langugae is not presented here, you make your own translation based on /language/en.

Administrator uses several plugins, and those use their own localization setup files. You can check your language support in the following plugins:

### CKEditor
Used in the wysiwyg field type.

Folder in Administrator: /public/js/ckeditor/lang/

Web page: [CKEditor](http://ckeditor.com/)

### jQuery date picker:
Used in date field type.

Folder in Administrator: /public/js/jquery/i18n/

Web page: [jQueryUI](http://jqueryui.com/datepicker/)

### jQuery time picker:
Used in time field type.

Folder in Administrator: /public/js/jquery/localization/

GitHub page: [jQuery TimePicker](http://jonthornton.github.com/jquery-timepicker/)

### plupload:
Used for file upload.

Folder in Administrator: /public/js/plupload/js/i18n/

Web page: [Plupload](http://www.plupload.com/)

