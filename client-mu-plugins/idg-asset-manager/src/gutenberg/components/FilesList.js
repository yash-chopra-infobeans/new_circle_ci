import { isEmpty } from 'lodash-es';
import File from './FilesListItem';
import { STORE_NAME } from '../../settings';

const { compose } = wp.compose;
const { Spinner } = wp.components;
const { useState, useEffect } = wp.element;
const { withSelect } = wp.data;

const Files = ({
  files = [],
  selectedFile,
  selectedFiles = [],
  children,
  loading = false,
  ...props
}) => {
  const [selectedId, setSelectedId] = useState('');
  const setSelectedFileId = id => {
    setSelectedId(id);
  };

  useEffect(() => {
    if (!selectedFile) {
      setSelectedId('');
    }
  });
  if (loading && isEmpty(files)) {
    return <Spinner />;
  }

  return (
    <div className="files">
      <ul className="filesList">
        {children}
        {files.map(file => (
          <File
            key={file?.id}
            {...props}
            {...file}
            selectedId={selectedId}
            setSelectedId={() => setSelectedFileId(file?.id)}
            selectedFiles={selectedFiles}
          />
        ))}
      </ul>
    </div>
  );
};

export default compose(
  withSelect(select => ({
    selectedFile: select(STORE_NAME).getSelectedFile(),
  })),
)(Files);
