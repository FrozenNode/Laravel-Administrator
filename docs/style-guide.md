# Style Guide

- [Introduction](#introduction)
- [Starting Braces](#starting-braces)
- [Tabs Instead of Spaces](#tabs-instead-of-spaces)
- [Inline Comments](#inline-comments)
- [Function/Method Comments](#function-method-comments)
- [Camel Case](#camel-case)
- [Line Breaks](#line-breaks)
- [Trimming Whitespace](#trimming-whitespace)

<a name="introduction"></a>
## Introduction

In the interest of keeping everything nice and neat, there are a few style conventions that must be followed. This is not a complete list, so it's best to also check the code around what you're changing/adding in order to get a good idea for what style to follow. If you think you have a good reason for why any of these styles shouldn't be here, please post an issue on the GitHub page and we can talk it through! Your suggestions may not be incorporated into the code, but they will never be ignored.

<a name="starting-braces"></a>
## Starting Braces

In almost all cases, a starting brace should be on the next line instead of on the same line. This is called [Allman style](http://en.wikipedia.org/wiki/Indent_style#Allman_style). This applies to everything from `if` statements to `function` declarations.

	//correct
	if ($something)
	{
		//do something
	}

	//incorrect
	if ($something) {

	}

	//correct
	if ($somethingElse)
	{

	}
	else
	{

	}

The only exception to this rule is for class names:

	//correct
	class Someting {

	}

<a name="tabs-instead-of-spaces"></a>
## Tabs Instead of Spaces

Your code should use tabs instead of spaces.

<a name="inline-comments"></a>
## Inline Comments

You should pepper your code with inline comments as much as possible without being overly verbose. Ideally you want to make it so that someone reading through the code the first time has a reasonable chance at understanding it. The correct type of comment should be the double-slash like this:

	//in order to ensure readability, we comment our code
	$code = Comment::my_code();

Always make sure there are line breaks *above* inline comments so they're easier to spot!

<a name="function-method-comments"></a>
## Function/Method Comments

Function or method comments should be in this form:

	/**
	 * Description of the method goes here. It can be as long as you need it to be.
	 *
	 * @param string		$someString
	 * @param int			$someInt
	 *
	 * @return false|array
	 */
	public function myMethod($someString, $someInt)
	{
		if (true)
		{
			return array('yay');
		}
		else
		{
			return false;
		}
	}

If there are no params, you can just put a single line between the description and the `@return`. If there is no return value, you can just include the description.

<a name="camel-case"></a>
## Camel Case

For the most part, Administrator uses the camelCase style in both PHP and JavaScript. The general exceptions to this rule are in CSS class names and config files where snake case is used. An example of this in PHP would be:

	/**
	 * Example of camelCase
	 */
	public function writeTheNameLikeThis()
	{
		//correct
		$varName = 'something';

		//incorrect
		$var_name = 'something_else';
	}

<a name="line-breaks"></a>
## Line Breaks

Be liberal with your line breaks. The goal is to make the code very readable, and line breaks are an essential factor for legibility. You can group certain things together, like if you're setting a bunch of variables, but only do this when it makes sense. Line breaks should come before all comments, all statements (e.g. `if`, `foreach`), all function/method comments, etc. If you're having difficulty deciding whether to add a line break to a section of code, search around the rest of the code base to find a similar section and use that as a guide.

An example of correct spacing:

	//optional descriptive comment
	$someVar = 'value';
	$otherVar = 5;

	//check to see if our value is set correctly
	if ($someVar === 'value')
	{
		//it was set, echo it out
		echo $someVar;
	}

<a name="trimming-whitespace"></a>
## Trimming Whitespace

When possible, you should trim all whitespace at the end of lines. Many IDEs have a feature like this. In SublimeText, for example, you can add...

	"trim_trailing_white_space_on_save": true

...to your user settings.