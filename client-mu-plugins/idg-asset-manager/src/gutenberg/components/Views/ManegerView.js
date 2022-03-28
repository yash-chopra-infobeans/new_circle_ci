import ReactPaginate from 'react-paginate';
import { debounce, uniqBy } from 'lodash-es';

import { STORE_NAME, I18N_DOMAIN } from '../../../settings';
import api from '../../api';
import dataToFile from '../../utils/dataToFile';
import Files from '../FilesList';
import Header from '../Header';
import SearchSidebar from '../SearchSidebar';
import SearchTopbar from '../SearchTopbar';

const { __ } = wp.i18n;
const { useState, useCallback, useEffect } = wp.element;
const { dispatch, withSelect } = wp.data;
const { compose } = wp.compose;
const { Button } = wp.components;

const ModalManager = ({
  onClose,
  files,
  selectedFile,
  selectedFiles,
  setUpload,
  editFile,
  params,
  additionalParams = {},
  multiple = false,
  onSelect,
  value: attachments,
  showErrMessageOnImageSelection,
  ...props
}) => {
  const [loading, setLoading] = useState(true);
  const [pages, setPages] = useState(0);
  const [total, setTotal] = useState(0); // eslint-disable-line no-unused-vars

  const insertAsset = () => {
    if (selectedFiles.length <= 0) {
      return;
    }

    const selectData = selectedFiles.map(file => ({
      ...file,
      url: file.source_url,
    }));

    onSelect(
      uniqBy(
        [
          ...selectData,
          ...attachments.map(attachment => ({ ...attachment, id: parseInt(attachment.id, 10) })),
        ],
        'id',
      ),
    );
    onClose();
  };

  const hideErrorMessage = () => {
    dispatch(STORE_NAME).clearSelectedFile();
  };

  const getItems = () =>
    api
      .getFiles({ params: { page: 1, per_page: 18, ...params, ...additionalParams } })
      .then(response => {
        const { data = [], headers } = response;

        setPages(headers?.['x-wp-totalpages'] || 0);
        setTotal(headers?.['x-wp-total'] || 0);

        setLoading(false);

        if (data.length <= 0) {
          dispatch(STORE_NAME).setFiles([]);
          return;
        }

        const uploadedFiles = data.map(uploadedFile => dataToFile(uploadedFile));

        dispatch(STORE_NAME).setFiles(uploadedFiles);
      }, []);

  const debouncedGetItems = useCallback(debounce(getItems, 300), [params]);

  const setSearchParams = prop => value =>
    dispatch(STORE_NAME).setParams({
      ...params,
      [prop]: value,
      page: prop === 'page' ? value : 1,
    });

  const setMultipleParams = newParams =>
    dispatch(STORE_NAME).setParams({ ...params, ...newParams, page: newParams?.page || 1 });

  const componentUnmount = () => {
    dispatch(STORE_NAME).setFiles([]);
    debouncedGetItems.cancel();
  };

  useEffect(() => {
    debouncedGetItems(params);

    return componentUnmount;
  }, [params]);

  // If add argument is false, the media item is removed from the selectedFiles array.
  const onMultipleSelect = id => {
    const isSelected = selectedFiles.find(selFile => selFile.id === id);
    const media = files.find(file => file.id === id);

    if (!media) {
      return;
    }

    if (isSelected) {
      dispatch(STORE_NAME).removeSelectedFile(media);
      return;
    }

    dispatch(STORE_NAME).addSelectedFile(media);
  };

  const initialPage = params?.page - 1 || 0;

  return (
    <div className="assetManager-view">
      <div className="assetManager-main">
        <Header title={__('Media Management', I18N_DOMAIN)} isDismissible={false} onClose={onClose}>
          <Button isSecondary onClick={setUpload}>
            {__('Upload', I18N_DOMAIN)}
          </Button>
        </Header>
        <SearchTopbar
          params={params}
          onChange={setSearchParams}
          setMultipleParams={setMultipleParams}
        />
        <Files
          editFile={editFile}
          files={files}
          selectedFiles={selectedFiles}
          loading={loading}
          onMultipleSelect={onMultipleSelect}
          multiple={multiple}
          {...props}
        />
        {showErrMessageOnImageSelection && (
          <div class="error-msg-container">
            <Button
              className="hide-error-message"
              onClick={hideErrorMessage}
              icon="no-alt"
              label={__('Close dialog', I18N_DOMAIN)}
            />
            <p className="disabled-button-error-message">
              {__(
                'This image does not meet the minimum dimensions for a Featured Image. To use this image as featured, upload a version with minimum dimensions of 1200 x 800px.',
                I18N_DOMAIN,
              )}
            </p>
          </div>
        )}
        <div className="assetManager-viewFooter">
          <ReactPaginate
            initialPage={initialPage}
            forcePage={initialPage}
            disableInitialCallback={false}
            previousLabel={__('Previous', I18N_DOMAIN)}
            nextLabel={__('Next', I18N_DOMAIN)}
            breakLabel={'...'}
            pageCount={pages}
            marginPagesDisplayed={1}
            pageRangeDisplayed={4}
            onPageChange={({ selected }) => setSearchParams('page')(selected + 1)}
            containerClassName={'assetManager-pagination'}
            activeClassName={'active'}
          />
          {multiple && onSelect && selectedFiles.length > 0 && (
            <div className="assetManager-actions">
              <Button isSecondary onClick={insertAsset}>
                {__('Select', I18N_DOMAIN)}
              </Button>
            </div>
          )}
        </div>
      </div>
      <div className="assetManager-sidebar">
        <SearchSidebar onChange={setSearchParams} params={params} onClose={onClose} />
      </div>
    </div>
  );
};

export default compose(
  withSelect(select => ({
    files: select(STORE_NAME).getFiles(),
    selectedFile: select(STORE_NAME).getSelectedFile(),
    selectedFiles: select(STORE_NAME).getSelectedFiles(),
    params: select(STORE_NAME).getParams(),
  })),
)(ModalManager);
