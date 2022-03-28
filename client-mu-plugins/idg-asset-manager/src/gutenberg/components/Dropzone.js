import className from 'classnames';
import { noop } from 'lodash';
import { useDropzone } from 'react-dropzone';

import { I18N_DOMAIN } from '../../settings';
import formatBytes from '../utils/formatBytes';

const { __ } = wp.i18n;
const { maxUploadSize, allowedMimeTypes } = window.assetManager;
const { useEffect, useState } = wp.element;

const fileTypes = Object.keys(allowedMimeTypes)
  .map(mimeType => allowedMimeTypes[mimeType])
  .join(', ');

const Dropzone = ({
  onDrop,
  onError = noop,
  displayUpload = false,
  errors = [],
  accept = fileTypes,
}) => {
  const {
    acceptedFiles,
    fileRejections,
    getRootProps,
    getInputProps,
    open,
    isDragActive,
  } = useDropzone({
    onDrop,
    noDragEventsBubbling: true,
    accept,
    maxSize: maxUploadSize,
  });

  const [dropzoneErrors, setDropzoneErrors] = useState(errors);

  useEffect(() => {
    if (displayUpload) {
      open();
    }

    if (fileRejections && fileRejections.length > 0) {
      const errorMessages = fileRejections.reduce((carry, current) => {
        const errorMessage = current.errors
          .map(error => {
            if (error.code === 'file-too-large') {
              return __(
                `file size exceeds the maximum limit of ${formatBytes(maxUploadSize)}`,
                I18N_DOMAIN,
              );
            }

            if (error.code === 'file-invalid-type') {
              return __(`invalid file type`, I18N_DOMAIN);
            }

            return error.message;
          })
          .join(', ');

        carry.push({
          id: current.file.name,
          message: `${current.file.name}: ${errorMessage}.`,
        });

        return carry;
      }, []);

      setDropzoneErrors(errorMessages);
      onError(acceptedFiles, errorMessages);
      return;
    }

    onError([]);
    setDropzoneErrors([]);
  }, [fileRejections]);

  const classes = className('dropzone', {
    'dropzone--hasErrors': dropzoneErrors.length > 0,
  });

  return (
    <>
      <div className={classes} {...getRootProps()}>
        <input {...getInputProps()} />
        <h2 className="dropzoneTitle">
          {isDragActive
            ? __('Drop files here', I18N_DOMAIN)
            : __('Drag files here to upload', I18N_DOMAIN)}
        </h2>
        <p className="dropzoneOr">{__('or', I18N_DOMAIN)}</p>
        <button type="button" className="browser button button-hero">
          {__('Select Files', I18N_DOMAIN)}
        </button>
        <p className="maxUploadSize">
          {__(`Maximum upload file size: ${formatBytes(maxUploadSize)}`, I18N_DOMAIN)}
        </p>
        {dropzoneErrors.length > 0 && (
          <ul className="dropzoneErrors">
            {dropzoneErrors.map(dropzoneError => (
              <li key={dropzoneError.id}>{dropzoneError.message}</li>
            ))}
          </ul>
        )}
      </div>
    </>
  );
};

export default Dropzone;
