import { useHistory } from 'react-router-dom';

import UploadView from '../components/Views/UploadView';

const UploadPage = () => {
  const history = useHistory();

  const afterUpload = () => history.push('/');

  return <UploadView onCancel={afterUpload} afterUpload={afterUpload} />;
};

export default UploadPage;
