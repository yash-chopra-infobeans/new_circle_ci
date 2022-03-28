import { useParams, useHistory } from 'react-router-dom';

import { STORE_NAME } from '../../settings';
import Page from '../components/Page';
import dataToFile from '../utils/dataToFile';
import api from '../api';
import FileView from '../components/Views/FileView';

const { useState, useEffect } = wp.element;
const { Spinner } = wp.components;
const { dispatch, withSelect } = wp.data;
const { compose } = wp.compose;

const File = ({ selectedFile }) => {
  const { assetId: ID } = useParams();
  const [isLoading, setLoading] = useState(true);

  const history = useHistory();

  const getItem = () =>
    api.getFile(ID).then(response => {
      const { data } = response;

      dispatch(STORE_NAME)
        .setFiles([dataToFile(data)])
        .then(() => {
          dispatch(STORE_NAME).selectFile(ID);
          setLoading(false);
        });
    }, []);

  useEffect(() => {
    if (selectedFile && `${selectedFile?.id}` === ID) {
      setLoading(false);
    } else {
      getItem();
    }
  }, []);

  const goBack = () => history.push('/');

  if (isLoading || !selectedFile) {
    return (
      <Page>
        <Spinner />
      </Page>
    );
  }

  return <FileView goBack={goBack} />;
};

export default compose(
  withSelect(select => ({
    selectedFile: select(STORE_NAME).getSelectedFile(),
  })),
)(File);
