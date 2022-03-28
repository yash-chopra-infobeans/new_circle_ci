# IDG Golden taxonomy Plugin
This plugin syncs the golden taxonomies from the IDG database to WordPress category taxonomy.

## Contents
- [Changes in default taxonomies](#changes-in-default-taxonomies)
- [Custom Metaboxes](#custom-metaboxes)
  - [Categories](#categories)
  - [Tags](#tags)

## Changes in default taxonomies
- `Categories` can not be edited or created.
- `Tags` can not be edited or created except for user with `Administrator` roles.

## Custom Metaboxes

## Categories
- Custom metabox for assigning categories to articles. Uses [select2](https://select2.org/) library's multi-select component. Selected categories can be re-ordered, and **first category in the selection will be used as the `Primary Category`** on front-end for breadcrumbs.
- Primary categories are also highlighted in the editor to make it easier to visually identify.
- Order of categories can be preserved as selected categories' `IDs` are stored in the `post-meta` in same order in which they are ordered in the meta field.
- **Meta key**: `_idg_post_categories` (Array of category IDs)

## Tags
- Custom metabox for assigning tags to articles. Uses [select2](https://select2.org/) library's multi-select component. Tags can not be created using this field unlike the default Tags field.
