# acf-field-group-composer

[![standard-readme compliant](https://img.shields.io/badge/readme%20style-standard-brightgreen.svg?style=flat-square)](https://github.com/RichardLitt/standard-readme)
[![Build Status](https://img.shields.io/travis/flyntwp/acf-field-group-composer.svg?style=flat-square)](https://travis-ci.org/flyntwp/acf-field-group-composer)
[![Code Quality](https://img.shields.io/scrutinizer/g/flyntwp/acf-field-group-composer.svg?style=flat-square)](https://scrutinizer-ci.com/g/flyntwp/acf-field-group-composer)
[![Code Coverage](https://img.shields.io/coveralls/flyntwp/acf-field-group-composer.svg?style=flat-square)](https://coveralls.io/github/flyntwp/acf-field-group-composer)

> Configuration builder for advanced custom fields field groups

This plugin helps you create reusable ACF fields and field groups with code.

## Table of Contents

- [Background](#background)
- [Install](#install)
- [Usage](#usage)
- [API](#api)
- [Maintainers](#maintainers)
- [Contribute](#contribute)
- [License](#license)

## Background

ACF provides a great interface to create custom meta boxes in WordPress. However, there are to major downsides using it out of the box:

1. Configuring ACF via its GUI can be cumbersome if you have a lot of custom fields. Also fields created via the GUI are only stored in the database. This means that migrating fields between different stages of a development setup can only be done by migrating the database as well.
2. The local JSON feature and `add_local_field_group` are handy for checking the config into SCM and deploying it to different environments, but it lacks some customizability, like reusing previously specified fields in different contexts.

The **acf-field-group-composer** helps circumventing these downsides. It let's you use the build-in `add_local_field_group` function, and automatically adds unique keys to all fields added. Plus, if a field is not an array but a string, it will apply a filter with that name and use the return value as a new field in the config.

## Install

TODO: install via WordPress instructions

To install via composer, run:

```bash
composer require flyntwp/acf-field-group-composer
```

## Usage

To register a field group run:

```php
ACFComposer\ACFComposer::registerFieldGroup($config);
```

The `$config` variable should be of the same format as the one you would pass to `acf_add_local_field_group`. There are only two things that are different:

1. You do not have to specify a `key` in any field or group.
2. A field group requires a unique `name` property.

Following the minimal example [from ACF](https://www.advancedcustomfields.com/resources/register-fields-via-php):

```php
$config = [
  'name' => 'group1',
  'title' => 'My Group',
  'fields' => [
    [
      'label' => 'Subtitle',
      'name' => 'subtitle',
      'type' => 'text'
    ]
  ],
  'location' => [
    [
      [
      'param' => 'post_type',
      'operator' => '==',
      'value' => 'post'
      ]
    ]
  ]
];
```

In order to make use of the additional functionality of **acf-field-group-composer** you can extract the specified field and return it in a filter. The name of the filter can be anything, but we recommend a meaningful naming scheme. For example:

```php
add_filter('MyProject/ACF/fields/field1', function ($field) {
  return [
    'label' => 'Subtitle',
    'name' => 'subtitle',
    'type' => 'text'
  ];
});

$config = [
  'name' => 'group1',
  'title' => 'My Group',
  'fields' => [
    'MyProject/ACF/fields/field1'
  ],
  'location' => [
    [
      [
      'param' => 'post_type',
      'operator' => '==',
      'value' => 'post'
      ]
    ]
  ]
];
```

The same can be done for the location:

```php
add_filter('MyProject/ACF/locations/postTypePost', function ($location) {
  return [
    'param' => 'post_type',
    'operator' => '==',
    'value' => 'post'
  ];
});

$config = [
  'name' => 'group1',
  'title' => 'My Group',
  'fields' => [
    'MyProject/ACF/fields/field1'
  ],
  'location' => [
    [
      'MyProject/ACF/locations/postTypePost'
    ]
  ]
];
```

Combining the previous steps will yield the following result:

```php
add_filter('MyProject/ACF/fields/field1', function ($field) {
  return [
    'label' => 'Subtitle',
    'name' => 'subtitle',
    'type' => 'text'
  ];
});

add_filter('MyProject/ACF/locations/postTypePost', function ($location) {
  return [
    'param' => 'post_type',
    'operator' => '==',
    'value' => 'post'
  ];
});

$config = [
  'name' => 'group1',
  'title' => 'My Group',
  'fields' => [
    'MyProject/ACF/fields/field1'
  ],
  'location' => [
    [
      'MyProject/ACF/locations/postTypePost'
    ]
  ]
];

ACFComposer\ACFComposer::registerFieldGroup($config);
```

Executing this code will add a field with the **name** `subtitle` to all posts. The **key** will be a combination of the field group name, all parent field names (if the field has parent fields), and the field's name itself. In this case, this is `field_group1_subtitle`.

### Filter arguments

There is another caveat when working with reusable components in ACF. While the flexible content field from ACF Pro gives you everything you need for adding multiple components of the same type to one field group, this is not possible for regular field groups.

For example, if you define a set of fields for a simple WYSIWYG component and then want to add it to a field group multiple times, the result will be two Wysiwyg components being displayed, but each one would have the same name, and thus overwrite each other's data.

```php
add_filter('MyProject/ACF/fields/wysiwyg', function ($field) {
  return [
    'label' => 'Content',
    'name' => 'content',
    'type' => 'wysiwyg'
  ];
});

$config = [
  'name' => 'group1',
  'title' => 'My Group',
  'fields' => [
    'MyProject/ACF/fields/wysiwyg',
    'MyProject/ACF/fields/wysiwyg'
  ],
  'location' => [
    [
      'MyProject/ACF/locations/postTypePost'
    ]
  ]
];

ACFComposer\ACFComposer::registerFieldGroup($config);
```

This can be fixed by adding a filter argument. All filter arguments must be appended with #. Once this is done, the respective filter will be called with the suffix as a second argument, and the field names will be prefixed with that string.


For example, taking the previous broken code an adding two unique filter names will resolve the problem:

```php
add_filter('MyProject/ACF/fields/wysiwyg', function ($field, $componentName) {
  return [
    'label' => 'Content',
    'name' => 'content',
    'type' => 'wysiwyg'
  ];
}, 10, 2);

$config = [
  'name' => 'group1',
  'title' => 'My Group',
  'fields' => [
    'MyProject/ACF/fields/wysiwyg#firstWysiwyg',
    'MyProject/ACF/fields/wysiwyg#secondWysiwyg'
  ],
  'location' => [
    [
      'MyProject/ACF/locations/postTypePost'
    ]
  ]
];

ACFComposer\ACFComposer::registerFieldGroup($config);
```

As a result, the following two fields are added to all posts:

| name | key |
|---|---|
| `firstWysiwyg_content` | `field_group1_firstWysiwyg_content` |
| `secondWysiwyg_content` | `field_group1_secondWysiwyg_content` |

These field can be accessed as usual through the ACF functions `get_field()` and `get_fields()`.

### Conditional logic

Conditional logic can be added to a field in the same way as in ACF. The exception is that we can pass `fieldPath` instead of `field`. The `fieldPath` must reference the name of a field defined in the same config:

```json
{
  "label": "Show Content Field",
  "name": "showContentField",
  "type": "true_false"
},
{
  "label": "Content",
  "name": "contentHtml",
  "type": "wysiwyg",
  "conditional_logic": [
    [
      {
        "fieldPath": "showContentField",
        "operator": "==",
        "value": "1"
      }
    ]
  ]
}
```

To reference a field in conditional logic when inside a nested sub field (for example, in a repeater), the `fieldPath` must be passed relative to the current level. For example:

```json
{
  "label": "Show Content Field",
  "name": "showContentField",
  "type": "true_false"
},
{
  "label": "Content Repeater",
  "name": "contentRepeater",
  "type": "repeater",
  "sub_fields": [
    {
      "label": "Content",
      "name": "contentHtml",
      "type": "wysiwyg",
      "conditional_logic": [
        [
          {
            "fieldPath": "../showContentField",
            "operator": "==",
            "value": "1"
          }
        ]
      ]
    }
  ]
}
```

## API

### ACFComposer\ACFComposer (class)

#### registerFieldGroup (static)

The main function of this package. Resolves a given field group config and registers an acf field group via `acf_add_local_field_group`.

```php
public static function registerFieldGroup(array $config)
```

### ACFComposer\ResolveConfig (class)

#### forFieldGroup (static)

Validate and generate a field group config from a given config array by adding keys and replacing filter strings with actual content.

```php
public static function forFieldGroup(array $config)
```

#### forField (static)

Validate and generate a field config from a given config array by adding keys and replacing filter strings with actual content.

```php
public static function forField(array $config, array $parentKeys = [])
```

#### forLayout (static)

Validate and generate a layout config from a given config array by adding keys and replacing filter strings with actual content.

```php
public static function forLayout(array $config, array $parentKeys = [])
```

#### forLocation (static)

Validate a location config from a given config array.

```php
public static function forLocation(array $config)
```

## Maintainers

This project is maintained by [bleech](https://github.com/bleech).

The main people in charge of this repo are:

- [Dominik Tränklein](https://github.com/domtra)
- [Doğa Gürdal](https://github.com/Qakulukiam)

## Contribute

To contribute, please use GitHub [issues](https://github.com/flyntwp/acf-field-group-composer/issues). Pull requests are accepted. Please also take a moment to read the [Contributing Guidelines](https://github.com/flyntwp/guidelines/blob/master/CONTRIBUTING.md) and [Code of Conduct](https://github.com/flyntwp/guidelines/blob/master/CODE_OF_CONDUCT.md).

If editing the README, please conform to the [standard-readme](https://github.com/RichardLitt/standard-readme) specification.

## License

MIT © [bleech](https://www.bleech.de)
