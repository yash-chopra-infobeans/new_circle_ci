/* eslint-disable camelcase */
const fileToData = file => {
  const {
    title,
    caption = '',
    alt = '',
    publication = '',
    asset_tag: assetTag,
    asset_image_rights: assetImageRights,
    meta = {},
  } = file;

  // remove meta that should not be updated via UI
  delete meta.image_sizes;
  delete meta._wp_attachment_metadata; // eslint-disable-line camelcase, no-underscore-dangle

  /**
   * Create the object of data we wish to update in the database, we create the object rather than just passing `file`
   * so we don't pass in any properities that are purely for the UI such as `file.progress`, `file.uploading` etc.
   */
  const fileData = {
    ...(title && { title }),
    caption,
    meta: { ...meta, _wp_attachment_image_alt: alt },
    publication,
    ...(assetTag && { asset_tag: assetTag }),
    ...(assetImageRights && { asset_image_rights: assetImageRights }),
  };

  return fileData;
};

export default fileToData;
