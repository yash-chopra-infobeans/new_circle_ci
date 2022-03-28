import axios from 'axios';
import { isEmpty } from 'lodash-es';

import { I18N_DOMAIN, DOCUMENT_MIME_TYPES } from '../../settings';
import JWPlayer from './JWPlayer';

const { __ } = wp.i18n;
const { useState, useEffect } = wp.element;
const { moment } = window;
const { Dashicon } = wp.components;

const { root, nonce } = window.assetManager;

const Preview = ({
  id,
  source_url: sourceUrl,
  date,
  author: authorId,
  mime_type: mineType,
  upload = false,
  children,
  title = '',
  meta,
}) => {
  const [isLoading, setLoading] = useState(true);
  const [authorData, setAuthorData] = useState(false);
  const [imageSize, setSize] = useState(false);

  const { CancelToken } = axios;
  const source = CancelToken.source();

  const getUser = () =>
    axios
      .get(`${root}wp/v2/users/${authorId}`, {
        cancelToken: source.token,
        headers: {
          'X-WP-Nonce': nonce,
        },
      })
      .then(response => {
        const { data } = response;

        setAuthorData(data);
        setLoading(false);
      }, [])
      .catch(thrown => {
        console.log('Preview.js', thrown.message);
      });

  const componentUnmount = () => {
    source.cancel();
  };

  useEffect(() => {
    /**
     * We only want to fetch user data when the file has been uploaded as on upload it'll be the
     * logged in user's details which we can get from `window.assetManager.currentUser`
     */
    if (!upload) {
      getUser();
    } else {
      const { currentUser } = window.assetManager;
      setAuthorData(currentUser);
      setLoading(false);
    }

    return componentUnmount;
  }, []);

  const image = new Image();
  image.src = sourceUrl;

  if (!imageSize && !DOCUMENT_MIME_TYPES.includes(mineType) && isEmpty(meta?.jw_player_media_id)) {
    image.onload = () => {
      setSize({ width: image.width, height: image.height });
    };
  }

  const PreviewContent = () => {
    // If uploading and file is a video.
    if (upload && mineType.includes('video')) {
      return (
        <video controls>
          <source src={sourceUrl} />
        </video>
      );
    }

    // If media id exists, meaning it's a JW Player video.
    if (!isEmpty(meta?.jw_player_media_id)) {
      return <JWPlayer id={id} mediaId={meta.jw_player_media_id} status={meta.status} />;
    }

    // If mime type is a document, display an icon.
    if (DOCUMENT_MIME_TYPES.includes(mineType)) {
      return (
        <div className="fileDocument">
          <Dashicon icon="media-document" />
          <span>{title}</span>
        </div>
      );
    }

    // Last but not least display image as above conditions will render other formats.
    return <img src={sourceUrl} />;
  };

  return (
    <div className="assetFile-Preview">
      <div className="assetFile-previewContent">{PreviewContent()}</div>
      <div className="assetFile-previewData">
        <ul>
          {id && (
            <li>
              <strong>{__('Media ID: ', I18N_DOMAIN)}</strong>
              {id}
            </li>
          )}
          {!DOCUMENT_MIME_TYPES.includes(mineType) &&
            isEmpty(meta?.jw_player_media_id) &&
            !mineType.includes('video') && (
              <li>
                <strong>{__('Original size: ', I18N_DOMAIN)}</strong>
                {`${imageSize?.width} x
            ${imageSize?.height}`}
              </li>
            )}
          {!upload && (
            <>
              <li>
                <strong>{__('Uploaded date: ', I18N_DOMAIN)}</strong>{' '}
                {moment(date).format('DD-MM-YYYY')}
              </li>
              <li>
                <strong>{__('Uploaded by: ', I18N_DOMAIN)} </strong>
                {!isLoading && authorData && (authorData?.displayName || authorData.name)}
              </li>
            </>
          )}
        </ul>
      </div>
      <div className="assetFile-previewActions">{children}</div>
    </div>
  );
};

export default Preview;
