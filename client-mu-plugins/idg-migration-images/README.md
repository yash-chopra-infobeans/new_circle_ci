# IDG Migration

## Commands

### Arguments
* `--type` [`content`, `db`, `users`, `taxonomy`, `featured_image`] - Type determines what content will be migrated. `content` will migrate images found within the posts body, as well as the featured image of the post. `users` will migrate user profile photos. `taxonomy` will migrate taxonomy images. `featured_image` will migrate the featured images for the chosen post type(`post_type`).
* `--amount` - Number of posts to migrate images for.
* `--offset` - Number of posts to skip.
* `--taxonomy` [`blogs`, `podcast_series`, `sponsorships`] - Taxonomy to migrate images for, `--type` must be set to `taxonomy`.
* `--include` - Object IDs to migrate images for.
* `--post_type` [`post`, `page`, `product`] - Post type to migrate image for, only used for when `--type` is set to `featured_image`.
* `--publish` - If value is set, objects processed will be published to specified delivery sites(`--publications`).
* `--publications` - Array of publication ID's that processed objects should be published too.

## Example commands
* `wp idg migrate attachments --type=content --amount=10` - Migrate images within an article.
* `wp idg migrate attachments --type=featured_image --amount=10 --post_type=product` - Migrate product featured images.
* `wp idg migrate attachments --type=taxonomy --amount=10 --taxonomy=sponsorships` - Migrate taxonomy images.
* `wp idg migrate attachments --type=users --amount=10` - Migrate user profile images.
* `wp idg migrate attachments --include=331915 --type=content` - Migrate a specific posts images using `--include=12345678` argument.
* `wp idg migrate attachments --include=8510 --type=taxonomy --taxonomy=sponsorships --publish=true --publications=3` - Migrate and publish a specific term to a delivery site.
* `wp idg migrate attachments --include=334323 --type=content --publish=true` - Migrate and deploy a post.
