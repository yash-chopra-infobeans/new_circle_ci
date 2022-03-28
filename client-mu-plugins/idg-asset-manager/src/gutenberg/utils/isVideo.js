import { VIDEO_EXTENSIONS } from '../../settings';
import getExtension from './getExtension';

/**
 * Checks if a file is a video.
 *
 * @param {string} filename The file name.
 * @return {boolean} Whether the file is a video.
 */
const isVideo = (filename = '') => {
  if (!filename) {
    return false;
  }

  return VIDEO_EXTENSIONS.includes(getExtension(filename));
};

export default isVideo;
