import className from 'classnames';
import axios from 'axios';
import { isEmpty } from 'lodash-es';

import { I18N_DOMAIN, STORE_NAME } from '../../settings';
import { validate } from '../utils/validateAsset';
import fileToData from '../utils/fileToData';
import dataToFile from '../utils/dataToFile';
import Dropzone from './Dropzone';
import Files from './FilesList';
import Preview from './Preview';
import Form from './Form';
import convertModelToFormData from '../utils/convertModelToFormData';
import setDefaultValue from './setDefaultValue';
import ConfirmBulkModal from './ConfirmBulkModal';

const { __ } = wp.i18n;
const { withSelect, dispatch } = wp.data;
const { compose } = wp.compose;
const { Button, Fill, Notice, Spinner } = wp.components;
const { useEffect, useState } = wp.element;
const { root, nonce } = window.assetManager;

const Upload = ({ files, selectedFile, afterUpload, displayUpload = false }) => {
  const [uploading, setUploading] = useState(false);
  const [errors, setErrors] = useState([]);
  const [isChecked, setChecked] = useState(false);
  const [isModalOpen, toggleModal] = useState(false);

  const componentUnmount = () => {
    dispatch(STORE_NAME).clearSelectedFile();
  };

  useEffect(() => {
    // clear the store so that we don't display previously uploaded assets
    dispatch(STORE_NAME).setFiles([]);
    dispatch(STORE_NAME).clearSelectedFile();
    return componentUnmount;
  }, []);

  const onDrop = filesToUpload => {
    const uploads = filesToUpload.map((file, index) =>
      dispatch(STORE_NAME).addFile({
        id: `${index}-${file.name}`,
        source_url: URL.createObjectURL(file),
        uploading: false,
        uploaded: false,
        progress: 0,
        date: Date.now(),
        fileToUpload: file,
        mime_type: file.type,
        meta: {
          active: true,
        },
      }),
    );

    const results = Promise.all(uploads);
    results.then(data => {
      if (data.length < 1) {
        return;
      }

      dispatch(STORE_NAME).selectFile(data[data.length - 1].value.id);
    });
  };

  const deleteFile = ID => dispatch(STORE_NAME).deleteFile(ID);

  const editFile = ID =>
    dispatch(STORE_NAME)
      .clearSelectedFile()
      .then(() => {
        dispatch(STORE_NAME).selectFile(ID);
      });

  const uploadFiles = () => {
    const validationErrors = {};
    let isError = false;

    files.forEach(file => {
      validationErrors[file.id] = validate(file);

      if (!isEmpty(validationErrors[file.id])) {
        dispatch(STORE_NAME).editFile({ errors: validationErrors[file.id] }, file.id);
        isError = true;
      } else {
        // clear any previous errors for file that may have been set
        dispatch(STORE_NAME).editFile({ errors: {} }, file.id);
      }
    });

    // if there were any error's amongst any files to be uploaded, return
    if (isError) {
      return;
    }

    setUploading(true);

    const uploaders = files.map(file => {
      // set file uploading state in store
      dispatch(STORE_NAME).editFile({ loading: 0, uploading: true }, file.id);

      const assetData = {
        ...fileToData({
          ...file,
          meta: file?.meta,
        }),
        file: file.fileToUpload,
      };
      const formData = convertModelToFormData(assetData);
      /**
       * In order to use wp_handle_upload $_POST['action'] must be set and its value must
       * equal $overrides['action']
       *
       * WordPress codex: https://developer.wordpress.org/reference/functions/wp_handle_upload/
       */
      formData.append('action', 'wp_handle_upload');

      const onUploadProgress = progressEvent => {
        let progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);

        /**
         * We only want to set files to 100% once all files have been generated which is why we only
         * show 95% completion until the data has also been inserted into the database.
         */
        if (progress > 94) {
          progress = 95;
        }

        // update temp store items upload progress
        dispatch(STORE_NAME).editFile({ progress }, file.id);
      };

      if (file.fileToUpload.type.includes('video')) {
        return axios.post(`${root}idg/v1/video`, formData, {
          headers: {
            'X-WP-Nonce': nonce,
            // 'X-Requested-With': 'XMLHttpRequest',
            'content-type': 'multipart/form-data',
          },
          onUploadProgress,
        });
      }

      return axios
        .post(`${root}wp/v2/media`, formData, {
          headers: {
            'X-WP-Nonce': nonce,
            'X-Requested-With': 'XMLHttpRequest',
          },
          onUploadProgress,
        })
        .then(responseAsset => {
          const { data: createdAsset } = responseAsset;

          dispatch(STORE_NAME).editFile(
            { ...dataToFile(createdAsset), progress: 100, uploading: false, uploaded: true },
            file.id,
          );
        });
    });

    axios.all(uploaders).then(() => {
      setUploading(false);

      if (!afterUpload) {
        return;
      }

      afterUpload();
    });
  };

  const onErrorNoticeRemove = id => setErrors(errors.filter(error => error.id !== id));

  const classes = className('assetManager-uploadGrid', {
    'assetManager--hasSelected': selectedFile,
    'assetManager--isMultiple': files.length > 1,
  });

  const classBorder = files.length > 1 ? 'default-border' : '';
  const defaultsObj = {
    asset_tag: classBorder,
    publication: classBorder,
    asset_image_rights: classBorder,
    credit: classBorder,
    credit_url: classBorder,
    image_rights_notes: classBorder,
  };

  const noticesUI = () => {
    if (isEmpty(errors)) {
      return null;
    }

    return (
      <Fill name="assetManager-aboveHeader">
        <div className="assetManager-notices">
          {errors.map(error => (
            <Notice key={error.id} status="error" onRemove={() => onErrorNoticeRemove(error.id)}>
              {error.message}
            </Notice>
          ))}
        </div>
      </Fill>
    );
  };

  if (files.length <= 0) {
    return (
      <>
        {noticesUI()}
        <Dropzone
          onError={(acceptedFiles = [], errorMessages) => {
            if (isEmpty(acceptedFiles)) {
              return;
            }

            setErrors(errorMessages);
          }}
          onDrop={onDrop}
          displayUpload={displayUpload}
        />
      </>
    );
  }

  const openModal = () => {
    if (!isChecked) {
      toggleModal(true);
    }
  };

  const checkModal = isConfirm => {
    if (isConfirm && !isChecked) {
      setChecked(true);
      setDefaultValue(files);
    }
    toggleModal(false);
  };
  return (
    <>
      {noticesUI()}
      {files.length > 1 && (
        <div>
          {isModalOpen && (
            <ConfirmBulkModal
              onClose={() => toggleModal(false)}
              onConfirm={isConfirm => {
                checkModal(isConfirm);
              }}
            />
          )}
        </div>
      )}
      <div className={classes}>
        {(!selectedFile || files.length > 1) && (
          <div className="assetManager-uploadWrapper vhScroll">
            <Files
              editFile={editFile}
              deleteFile={deleteFile}
              files={files}
              selectedFiles={[selectedFile?.id] || []}
            />
          </div>
        )}
        {selectedFile && (
          <div className="assetManager-uploadForm">
            <div className="vhScroll">
              <Preview {...selectedFile} id={null} upload />
            </div>
            <div className="vhScroll">
              <Form file={selectedFile} defaultsObj={defaultsObj} />
            </div>
          </div>
        )}
      </div>
      <div className="assetManager-fixedBottomBar assetManager-uploadBar">
        <div className="uploadBtn-wrapper">
          {files.length > 1 && (
            <div class="apply-btn">
              <span class="bottom-text">
                {__(
                  'Copy Tags, Publication, Image rights, Credit, Credit URL and Notes from first image to all other images',
                  I18N_DOMAIN,
                )}
              </span>
              <Button className="is-secondary" disabled={isChecked} onClick={openModal}>
                {isChecked ? __('Applied', I18N_DOMAIN) : __('Apply', I18N_DOMAIN)}
              </Button>
            </div>
          )}
          {files.length <= 1 && <div></div>}
          {uploading && <Spinner />}
          <Button isPrimary disabled={uploading} onClick={uploadFiles}>
            {uploading
              ? __(`Uploading... ${selectedFile?.progress || 0}%`, I18N_DOMAIN)
              : __('Upload', I18N_DOMAIN)}
          </Button>
        </div>
      </div>
    </>
  );
};

export default compose(
  withSelect(select => ({
    files: select(STORE_NAME)
      .getFiles()
      .filter(file => file.uploaded !== undefined),
    selectedFile: select(STORE_NAME).getSelectedFile(),
  })),
)(Upload);
