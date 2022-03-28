const dataToFile = file => {
  const { title, caption } = file;

  const fileObject = {
    ...file,
    title: title?.raw || title?.rendered || '',
    caption: caption.rendered,
    alt: file?.meta?._wp_attachment_image_alt || '', // eslint-disable-line camelcase, no-underscore-dangle
    thumbnail: file?.media_details?.sizes?.['300-r1:1']?.source_url || file?.source_url,
  };

  return fileObject;
};

export default dataToFile;
