# WEM Content Types

A lightweight WordPress content-structure manager for creating and maintaining Custom Post Types and Taxonomies with a small, extensible core.

**Current stable version: v1.0.0**

WEM Content Types focuses on one job: defining a site's content structure. It deliberately does not become a custom-field builder, page builder, or database migration suite.

```text
What content does this site have?
→ Product / Solution / Case / FAQ / Download

How is that content classified?
→ Product Category / Industry / Region / Application
```

Custom fields remain a separate layer so the core can stay stable and easy to maintain.

```text
WEM Content Types
├── Post Types
└── Taxonomies

Future / separate layer
└── WEM Content Fields or project-specific field modules
```

## Why this plugin exists

For repeatable WordPress company-site development, content structure should not have to be copied between `functions.php` files or rebuilt manually on every new site.

WEM Content Types provides a reusable structure layer with:

- a small WordPress-native architecture
- `wp_options` configuration storage
- standard `register_post_type()` / `register_taxonomy()` registration
- safe enable / disable behavior
- independent public Rewrite Slugs
- portable JSON structure transfer
- extension hooks for later field, project, or AI integrations

No custom database tables are required.

## Requirements

- WordPress 6.4 or newer
- PHP 7.4 or newer

## Installation

1. Upload the `wem-content-types` folder to `/wp-content/plugins/`, or install the ZIP from **Plugins → Add New → Upload Plugin**.
2. Activate **WEM Content Types**.
3. Open **WEM Content Types → Post Types**.
4. Create the content structure before adding project-specific fields or templates.

## Core capabilities

### Post Types

- Create, edit, and delete WEM Post Type definitions
- Enable or disable a definition without deleting existing posts
- Immutable internal Post Type slug after creation
- Automatic WordPress labels from Singular / Plural names
- visual Dashicons picker
- editor Supports: Title, Editor, Featured Image, Excerpt, Revisions, Author, Comments, Page Attributes, Custom Fields
- Public / Archive / REST controls
- advanced visibility controls
- Menu Position
- independent Rewrite Slug
- Rewrite enable / disable
- `with_front` control
- deletion protection while a WEM Taxonomy still depends on the Post Type

### Taxonomies

- Create, edit, and delete WEM Taxonomy definitions
- Enable or disable without deleting term data
- attach to one or more Post Types
- hierarchical Category-style or flat Tag-style behavior
- Admin Column
- REST / Block Editor support
- automatic labels
- advanced visibility controls
- independent Rewrite Slug
- Rewrite enable / disable
- `with_front` control
- preserve relationships to disabled WEM Post Types
- preserve existing relationships to temporarily unavailable third-party Post Types

### Tools

- Export all WEM structure as one JSON file
- Import WEM JSON using a safe merge strategy
- validate the complete document before writing configuration
- reject incompatible schema and slug conflicts
- 1 MB import safety limit
- never delete unrelated destination definitions during import

## Recommended Product example

### Post Type

```text
Enabled: Yes

Post Type Slug: product
Singular Label: Product
Plural Label: Products
Menu Icon: dashicons-products

Public: Yes
Archive: Yes
REST API / Block Editor: Yes

Supports:
Title
Editor
Featured Image
Excerpt
Revisions

Rewrite URLs: Yes
Rewrite Slug: products
Use Permalink Front: No
```

The internal key and public URL are intentionally separate:

```text
product   = stable internal WordPress key
products  = visitor-facing URL base
```

### Taxonomy

```text
Enabled: Yes

Taxonomy Slug: product_category
Singular Label: Product Category
Plural Label: Product Categories
Attach To: Product

Public: Yes
Hierarchical: Yes
Admin Column: Yes
REST API / Block Editor: Yes

Rewrite URLs: Yes
Rewrite Slug: product-category
Use Permalink Front: No
```

Avoid `product_cat` for a general-purpose WEM taxonomy because WooCommerce already uses that key.

## Slug rules

Post Type and Taxonomy slugs are structural identifiers, not labels.

Use only:

```text
lowercase letters
numbers
underscores
hyphens
```

Examples:

```text
product
solution
case
product_category
solution_region
```

WEM does not silently convert malformed structural slugs during normal saving or JSON import. Invalid slugs are rejected so typos do not become permanent data-model identifiers.

Limits follow WordPress conventions:

```text
Post Type: 20 characters maximum
Taxonomy:  32 characters maximum
```

## Internal Slug vs Rewrite Slug

These values solve different problems.

```text
Internal identity: product
Public route:      products
```

The internal slug is locked after creation because existing content is stored against that value.

The Rewrite Slug controls public URLs and may be changed later, but changing a live URL structure can affect links and SEO. Add redirects when changing an established Rewrite Slug.

## Enable, Disable, and Delete

### Disable

Disable is reversible.

```text
Disable definition
→ stop registering it in WordPress
→ keep WEM configuration
→ keep existing posts / terms / relationships
→ enable again later
```

Use Disable when a structure may return later.

### Delete

Delete removes the WEM definition only. WEM does not deliberately delete matching rows from `wp_posts`, term data, or term relationships.

For structural integrity, a Post Type definition cannot be deleted while a saved WEM Taxonomy still references it. Detach the Taxonomy first, or use Disable instead.

## Taxonomy “Attach To” behavior

The selector is ordered for practical site building:

```text
1. WEM-managed Post Types
2. WordPress Posts and Pages
3. Public third-party Post Types
4. Saved third-party relationships that are temporarily unavailable
```

WordPress internal Post Types such as `wp_block` and `wp_navigation` are hidden by default.

If a third-party plugin is temporarily deactivated, an existing Taxonomy relationship is shown as **Unavailable** instead of being silently removed. Once the providing plugin returns, the relationship works again.

The list remains extensible through:

```text
wem_ct_taxonomy_available_post_types
```

## JSON Import / Export

### Export

**WEM Content Types → Tools → Export Structure** downloads one portable JSON document containing WEM-managed:

- Post Type definitions
- Taxonomy definitions
- Enabled / Disabled state
- labels and descriptions
- editor Supports
- visibility settings
- REST settings
- Rewrite settings
- Taxonomy → Post Type relationships

It deliberately does **not** export:

- posts or pages
- taxonomy terms
- media files
- post meta
- custom-field values
- content relationships outside the WEM structure definition

The file is a **content-architecture definition**, not a WordPress backup.

### Import

Import uses a safe merge strategy:

```text
New WEM slug
→ Add

Existing matching WEM slug
→ Update

Other WEM definitions already on the destination site
→ Keep unchanged
```

There is intentionally no automatic “Replace All” mode in v1.0.0.

### Validation before writing

```text
Read JSON
   ↓
Check WEM format + schema
   ↓
Validate every Post Type
   ↓
Validate every Taxonomy + relationship
   ↓
Any validation error?
├── Yes → write no imported configuration
└── No  → perform the safe merge
```

### Portable schema

v1.0.0 continues to use export schema version `1`:

```json
{
  "format": "wem-content-types",
  "schema_version": 1,
  "plugin_version": "1.0.0",
  "post_types": {},
  "taxonomies": {}
}
```

`schema_version` is independent from the plugin version. The plugin can receive maintenance updates without unnecessarily breaking existing structure files.

## Typical enterprise-site workflow

Build and verify the structure once:

```text
Source Site
├── product
├── solution
├── faq
├── download
├── product_category
├── industry
└── region
```

Then:

```text
Source Site
WEM Content Types → Tools → Download JSON

                 ↓

New Site
WEM Content Types → Tools → Import JSON

                 ↓

Same content architecture
```

Actual content, media, and future custom-field values are migrated separately.

## Architecture

```text
Admin UI
   ↓
Validator
   ↓
Storage (wp_options)
   ↓
Registry
   ├── enabled? → register_post_type()
   └── enabled? → register_taxonomy()

Tools
   ↓
Transfer
   ├── canonical export
   └── validate → safe merge import
```

Configuration is stored in:

```text
wem_ct_post_types
wem_ct_taxonomies
```

## Registration order

```text
init priority 5
    ↓
Register enabled Post Types

init priority 6
    ↓
Register enabled Taxonomies

init priority 99
    ↓
Flush rewrite rules only when a structural-change flag exists
```

Rewrite rules are never flushed on every request.

Saving, deleting, enabling / disabling, changing Rewrite settings, or importing structure marks the rules for one later flush.

## Extension API

The extension API is intentionally small so future field modules or project-specific plugins can extend WEM without editing the core plugin.

### Post Type filters

```text
wem_ct_should_register_post_type
wem_ct_post_type_labels
wem_ct_post_type_args
```

### Post Type actions

```text
wem_ct_before_register_post_types
wem_ct_before_register_post_type
wem_ct_post_type_registered
wem_ct_after_register_post_types
```

### Taxonomy filters

```text
wem_ct_should_register_taxonomy
wem_ct_taxonomy_labels
wem_ct_taxonomy_object_types
wem_ct_taxonomy_args
wem_ct_taxonomy_available_post_types
```

### Taxonomy actions

```text
wem_ct_before_register_taxonomies
wem_ct_before_register_taxonomy
wem_ct_taxonomy_registered
wem_ct_after_register_taxonomies
```

### Import / Export

```text
wem_ct_export_payload
wem_ct_before_import
wem_ct_after_import
```

### Management capability

```text
wem_ct_manage_capability
```

Default capability: `manage_options`.

This allows a project to grant management access to another role without changing WEM core code.

## Custom fields and second-stage development

WEM Content Types intentionally stops at the content-structure layer.

A future field engine or project-specific module can use the existing Post Type registration hooks to add fields without modifying this plugin.

```text
WEM Content Types
      ↓
defines product / solution / faq
      ↓
WEM Content Fields or project module
      ↓
defines material / dimensions / images / files
      ↓
Theme / Elementor / Gutenberg
      ↓
renders the content
```

This keeps the dependency direction simple:

```text
Fields may depend on Content Types
Content Types must not depend on Fields
```

## Data-removal policy

WEM prioritizes recoverability.

- Disable never deletes content data.
- Deleting a WEM definition does not intentionally delete posts or term data.
- JSON import never removes unrelated destination definitions.
- The plugin does not include an automatic uninstall data purge in v1.0.0.

If a site requires complete cleanup, remove the WEM options only after confirming the structure and content are no longer needed.

## v1.0.0 Final Review

The stable release includes the full planned first-stage feature set and a final integrity pass:

- Post Type CRUD and registration
- Taxonomy CRUD and relationships
- automatic labels and Dashicons picker
- advanced visibility settings
- Enable / Disable
- independent Rewrite controls
- JSON Import / Export
- strict structural slug validation
- protection against deleting a Post Type still used by WEM Taxonomies
- preservation of temporarily unavailable third-party Taxonomy relationships
- stable extension hooks for second-stage development
- documentation and version cleanup

No custom-field builder, relationship engine, template system, or AI layer has been added to the core.

## Changelog

### v1.0.0

- Stable first release.
- Completed final architecture and compatibility review.
- Added dependency protection for Post Type deletion.
- Preserved existing third-party Taxonomy relationships when their Post Type is temporarily unavailable.
- Hardened structural slug validation for admin input and JSON import.
- Finalized documentation and stable extension boundaries.

### v0.5.0

- Added JSON Import / Export with safe merge and schema validation.

### v0.4.0

- Added Enable / Disable and Rewrite controls.

### v0.3.0

- Added advanced settings, automatic label refinements, and Dashicons picker.

### v0.2.x

- Added Taxonomy CRUD, Post Type relationships, and selector cleanup.

### v0.1.x

- Added Post Type CRUD, storage, validation, registration, and initial extension hooks.

## License

GPL-2.0-or-later.
