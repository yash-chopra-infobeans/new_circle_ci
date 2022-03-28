const { __ } = wp.i18n;
const { FormFileUpload, Spinner, Button, Flex } = wp.components;
const { uploadMedia } = wp.mediaUtils;
const { useState } = wp.element;

const ImageUpload = ({ onChange, value }) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(false);

  const onSuccess = url => {
    onChange(url);
    setLoading(false);
    setError(false);
  };

  const onError = err => {
    setError(err.message);
    setLoading(false);
  };

  const upload = files => {
    setLoading(true);
    setError(false);

    uploadMedia({
      filesList: files,
      onFileChange: ([fileObj]) => (fileObj?.id ? onSuccess(fileObj?.url) : onChange(null)),
      onError,
    });
  };

  const UploadButton = ({ text }) => (
    <FormFileUpload
      isPrimary
      isSmall
      disabled={loading}
      accept="image/*"
      onChange={event => upload(event.target.files)}
    >
      {text}
    </FormFileUpload>
  );

  return (
    <div
      className={`cf-imageUpload ${error ? 'cf-imageUpload--error' : ''}`}
      data-error={error}
      isSmall
    >
      <div className="cf-imageUpload-preview">
        {value && <img className="cf-imageUpload-previewImage" src={value} />}
        {loading && <Spinner />}
        {error && <p className="cf-imageUpload-previewError">{error}</p>}
        {!value && !error && (
          <p className="cf-imageUpload-previewText">
            {__(loading ? 'Uploading...' : 'Upload an image')}
          </p>
        )}
        <Flex justify="center">
          <UploadButton text={value ? __('Replace') : __('Upload')} />
          <Button isSecondary isSmall disabled={loading || !value} onClick={() => onChange(null)}>
            {__('Remove')}
          </Button>
        </Flex>
      </div>
    </div>
  );
};

export default ImageUpload;
