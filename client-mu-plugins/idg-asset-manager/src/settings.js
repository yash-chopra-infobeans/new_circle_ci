export const NAMESPACE = 'idg-asset-manager';

export const WINDOW_NAMESPACE = 'IDGAssetManager';

export const STORE_NAME = 'idg/asset-manager';
export const FILTER_NAMESPACE = STORE_NAME;

export const I18N_DOMAIN = NAMESPACE;

export const DOCUMENT_MIME_TYPES = [
  'application/pdf',
  'application/msword',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'application/gzip',
  'application/json',
  'text/javascript',
  'application/vnd.ms-powerpoint',
  'application/vnd.openxmlformats-officedocument.presentationml.presentation',
  'application/rtf',
  'application/xhtml+xml',
  'application/xml',
  'text/xml',
  'application/vnd.ms-excel',
  'application/x-7z-compressed',
  'application/zip',
];

export const MEDIA_TYPE_IMAGE = 'image';

export const VIDEO_EXTENSIONS = [
  'avi',
  'mpg',
  'mpeg',
  'mov',
  'mp4',
  'm4v',
  'ogg',
  'ogv',
  'webm',
  'wmv',
];

export const DEFAULT_ASSET = {
  title: '',
  alt: '',
  caption: '',
  source: '',
  credit: '',
  active: true,
  publication: null,
};
