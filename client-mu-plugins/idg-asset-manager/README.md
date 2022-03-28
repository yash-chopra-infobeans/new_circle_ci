# IDG Asset Manager Plugin

This plugin replaces the stanard media library and related components with our custom implementation.

## Preview

![Asset manager page](https://cdn-std.droplr.net/files/acc_690254/34CCRC)
![Asset manager modal, editing file](https://cdn-std.droplr.net/files/acc_690254/UCXTyQ)

---

## Table of Contents

- [Overview](#overview)
- [Setup](#setup)
- [Components](#components)
- [Attachment Records](#attachment-records)
- [Helpful Resources](#helpful-resources)

---

### Overview

---

### Setup

#### JW Player webook

In order to display the processing message while JW Player is creating poster image and converting the video to various formats to support the majority of browsers/devices etc we need to setup a webhook so that when the video is ready we can tell WordPress it is.

1. You can create a webhook and get the secret required to authenticate the JWT by running the following in your terminal:
```
curl -X POST https://api.jwplayer.com/v2/webhooks \
 -H 'Authorization: ${JW PLAYER PROPERTY SECRET - V2}' \
 -H 'Content-Type: application/json' \
 -d '{"metadata": {"name" : "Media Available Webhook - Develop", "description": "", "webhook_url": "${SITE BASE URL}/wp-json/idg/v1/video/webhooks/video-ready", "events": ["media_available"], "site_ids": [${SITE ID KEY CRED - V1"]}}'
```

2. Using the secret returned from the above call set the `JW_PLAYER_API_WEBHOOK_SECRET` constant in the relevant vip config.
___

### Components

#### Media Upload

Use this component where ever you wish to upload/select media. 

The below documentatio is very similar to the [core Media Upload component documentation](https://github.com/WordPress/gutenberg/tree/52f53e049db914fe4264d385ddda061dbe7a758b/packages/block-editor/src/components/media-upload) however the one's listed below are the props that are currently supported.

##### Props

###### `allowedTypes`
Array with the types of the media to upload/select from the asset manager. Each type is a string that can contain the general mime type e.g: 'image', 'audio', 'text', or the complete mime type e.g: 'audio/mpeg', 'image/gif'. If allowedTypes is unset all mime types should be allowed.

* Type: Array
* Required: No

###### `multiple` (Work in progress)

Whether to allow multiple selections or not.

* Type: Boolean
* Required: No
* Default: false

###### `value`

Media ID (or media IDs if multiple is true) to be selected by default when opening the asset manager.

* Type: Number|Array
* Required: No

###### `onSelect`

Callback called when the media modal is closed after media is selected.

This is called subsequent to onClose when media is selected. The selected media are passed as an argument.

* Type: Function
* Required: Yes

###### `render`

A callback invoked to render the Button opening the asset manager.

* Type: Function
* Required: Yes

The first argument of the callback is an object containing the following properties:

* open: A function opening the media modal when called

##### Example Usage

Full example can be found in `./src/gutenberg/filters/ReplaceMediaReplaceFlow/ReplaceMediaReplaceFlow.js`.

```
  const { MediaUpload } = wp.blockEditor;

  <MediaUpload
    value={mediaId}
    onSelect={media => selectMedia(media)}
    allowedTypes={allowedTypes}
    render={({ open, isOpen }) => (
      <ToolbarButton
        onClick={open}
        onKeyDown={openOnArrowDown}
      >
        {name}
      </ToolbarButton>
    )}
  />
```

---

### Attachment Records

Attachment records are just posts under the `attachment` post type (default WordPress post type for media). So by default standard data can be returned by passing the attachment id to [`get_post`](https://developer.wordpress.org/reference/functions/get_post/) which will return attachment data.

Below are details of additional attachment data that this plugin add's:

#### Image rights

A attachment's image rights stored as the taxonomy `asset_image_rights`.

#### Tags

A attachment's tags stored as the taxonomy `asset_tag`.

#### Publications

A attachment's publications stored as the taxonomy `publication`.

#### Additional meta

Key | Type | Description
--- | --- | --- 
`source` | `string` | Where the media has come from.
`active` | `boolean` | Dictates whether or not the media is visible in the asset manager (defaults to `true`).
`credit` | `string` | Owner of the media.
`image_rights_notes` | `string` | Any additional notes for the media.

---

### PHP Utility Functions

---

### Helpful Resources
