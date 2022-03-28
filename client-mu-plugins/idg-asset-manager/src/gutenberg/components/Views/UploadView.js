import { I18N_DOMAIN } from '../../../settings';
import Header from '../Header';
import Upload from '../Upload';

const { __ } = wp.i18n;
const { Button, Slot } = wp.components;

const UploadView = ({ onClose, onCancel, afterUpload, ...props }) => (
  <>
    <Slot name="assetManager-aboveHeader" />
    <Header title={__('Media Management', I18N_DOMAIN)} onClose={onClose}>
      <Button isSecondary onClick={onCancel}>
        {__('Cancel', I18N_DOMAIN)}
      </Button>
    </Header>
    <Upload afterUpload={afterUpload} {...props} />
  </>
);

export default UploadView;
