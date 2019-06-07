# Advanced Custom Fields: Memberium Tag Field

Create a select field for InfusionSoft tag ID powered by Memberium2.

-----------------------

## Description

Create a select or select multiple field for InfusionSoft tag ID powered by Memberium2. Field can return value as array or comma separated string.

## Compatibility

This ACF field type is compatible with:
* ACF 5

## Installation

1. Copy the `acf-memberium-is-tag-field` folder into your `wp-content/plugins` folder
2. Activate the `Advanced Custom Fields: Memberium Tag Field` plugin via the plugins admin page
3. Create a new field via ACF and select the Memberium Infusion Tag field type

## Usage

Didn't test if memberium functions work with array value, but they work with comma separated string without a problem.

```
$tags = get_field('field_name');
if(memb_hasAnyTags($tags)) {
	// do stuff
}

// or

if(memb_hasAllTags($tags)) {
	// do stuff
}
```

## Changelog

### 1.0.0
* Initial Release
