import MediaUpload from '../../components/MediaUpload';

const { MediaUploadCheck } = wp.blockEditor;

const ReplaceMediaUpload = InitialMediaUpload => {
  return class ReplaceMediaUploadClass extends InitialMediaUpload {
    render() {
      return (
        <MediaUploadCheck>
          <MediaUpload {...this.props} />
        </MediaUploadCheck>
      );
    }
  };
};

export default ReplaceMediaUpload;
