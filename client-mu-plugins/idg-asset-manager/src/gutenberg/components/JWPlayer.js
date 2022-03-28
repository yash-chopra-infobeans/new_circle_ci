import { v4 as uuidv4 } from 'uuid';
import ReactJWPlayer from 'react-jw-player';
import api from '../api';

import { I18N_DOMAIN } from '../../settings';
import useInterval from '../hooks/useInterval';

const { __ } = wp.i18n;
const { useEffect, useState } = wp.element;
const { Spinner } = wp.components;
const { embedPlayer } = window.assetManager.jwPlayer.players;

const JWPlayer = ({ id, mediaId, title, status, autoplay = false }) => {
  const [videoReady, setVideoReady] = useState(false);
  const [isLoading, setLoading] = useState(true);
  const [isError, setError] = useState(false); // eslint-disable-line no-unused-vars

  useEffect(() => {
    // If no attachment ID just display the JW Player and let it try to display the video using the mediaId.
    if (!id || id === undefined) {
      setVideoReady(true);
      setLoading(false);
      return;
    }

    // Since we have an ID get the status of the video on componentDidMount.
    api
      .getFile(id)
      .then(media => {
        setLoading(false);

        // If status is not processing, video is ready to display.
        if (media?.data?.meta?.status !== 'processing') {
          setVideoReady(true);
        }
      })
      .catch(() => {
        // If error, display the JW Player which will handle any errors if the media item isn't ready of there's an error.
        setVideoReady(true);
        setLoading(false);
      });
  }, []);

  useInterval(async () => {
    // If status if not processing just return as we assume it's ready.
    if (status !== 'processing' || videoReady) {
      return;
    }

    const fileStatus = await api.getFileStatus(id);

    if (Array.isArray(fileStatus.data) && !fileStatus.data[0]) {
      return;
    }

    setVideoReady(true);
  }, 3000);

  if (isError) {
    return (
      <div className="videoPlaceholder videoLoader">
        <div className="videoPlaceholder-content videoLoader-content">
          <span>
            {__(
              'An eror has occured when trying to render the video - check browser console for error.',
              I18N_DOMAIN,
            )}
          </span>
        </div>
      </div>
    );
  }

  // If media id exists, meaning it's a JW Player video, however it still being processed render loader.
  if (!videoReady || isLoading) {
    return (
      <div className="videoPlaceholder videoLoader">
        <div className="videoPlaceholder-content videoLoader-content">
          <Spinner />
          <span>
            {__(isLoading ? 'Loading...' : 'Video is currently being processed...', I18N_DOMAIN)}
          </span>
        </div>
      </div>
    );
  }

  // If media id exists and video is ready, display JW Player.
  const domId = uuidv4();

  return (
    <>
      {title && (
        <div className="jwplayer-videoTitle">
          <span>{title}</span>
        </div>
      )}
      <ReactJWPlayer
        className="jwplayer-wrapper"
        playerId={`jwplayer-wrapper-${domId}`}
        playerScript={`https://cdn.jwplayer.com/libraries/${embedPlayer}.js`}
        playlist={`https://content.jwplatform.com/feeds/${mediaId}.json`}
        isAutoPlay={autoplay}
        customProps={{ displaytitle: false }}
      />
    </>
  );
};

export default JWPlayer;
