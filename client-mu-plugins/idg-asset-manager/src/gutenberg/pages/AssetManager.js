import { useHistory } from 'react-router-dom';

import { STORE_NAME } from '../../settings';
import ManagerView from '../components/Views/ManegerView';

const { dispatch } = wp.data;

export const AssetManager = () => {
  const history = useHistory();

  const setUpload = () => history.push('/upload');

  const editFile = ID => {
    dispatch(STORE_NAME)
      .selectFile(ID)
      .then(() => {
        history.push(`/files/${ID}`);
      });
  };

  return (
    <div className="assetManager-page">
      <ManagerView editFile={editFile} setUpload={setUpload} />
    </div>
  );
};
export default AssetManager;
