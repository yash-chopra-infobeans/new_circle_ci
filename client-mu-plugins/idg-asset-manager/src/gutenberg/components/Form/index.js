import axios from 'axios';

import { STORE_NAME, I18N_DOMAIN } from '../../../settings';
import Tags from '../Tags';
import FormField from './Field';
import RichText from '../RichText';

const { __ } = wp.i18n;
const { root, publishingFlowSites = [] } = window.assetManager;
const { useState, useEffect } = wp.element;

const {
  Button,
  ToggleControl,
  TextControl,
  TextareaControl,
  SelectControl,
  Panel,
  PanelHeader,
  PanelRow,
  CheckboxControl,
  ExternalLink,
  Dashicon,
} = wp.components;
const { compose } = wp.compose;
const { withSelect, dispatch } = wp.data;
const Form = ({ onSave, selectedFile, defaultsObj }) => {
  const [defaults, changeDefaults] = useState({ ...defaultsObj });
  useEffect(() => {
    changeDefaults(defaultsObj);
  }, [defaultsObj]);

  if (!selectedFile) {
    return null;
  }
  const [rightsOptions, setRightsOptions] = useState([]);

  const getImageRights = () =>
    axios
      .get(`${root}wp/v2/asset_image_rights`, {
        params: {
          per_page: 100, // Note: per_page is capped at 100 as per https://developer.wordpress.org/rest-api/using-the-rest-api/pagination/
        },
      })
      .then(response => {
        const { data } = response;

        const options = data.reduce(
          (carry, current) => {
            carry.push({ value: current.id, label: current.name });

            return carry;
          },
          [{ value: 0, label: __('Select image rights', I18N_DOMAIN), disabled: true }],
        );

        setRightsOptions(options);
      });

  useEffect(() => {
    getImageRights();
  }, []);

  const onChange = name => value => {
    const { errors } = selectedFile;

    // if any errors for field being changes, remove them
    if (errors?.[name]) {
      delete errors[name];
    }
    changeDefaults({ ...defaults, [name]: '' });
    // update file in store with new value
    dispatch(STORE_NAME).editFile({ [name]: value, errors }, selectedFile.id);
  };

  const onChangeMeta = name => value => {
    const { errors } = selectedFile;

    if (errors?.[`meta.${name}`]) {
      delete errors[`meta.${name}`];
    }
    changeDefaults({ ...defaults, [name]: '' });
    dispatch(STORE_NAME).editFile(
      { meta: { ...selectedFile.meta, [name]: value } },
      selectedFile.id,
    );
  };

  const publicationsOptions = [
    ...publishingFlowSites.map(site => ({
      value: site.value,
      label: site.label,
    })),
  ];
  return (
    <div className="fileForm">
      <Panel>
        <PanelHeader label={__('Basic Information', I18N_DOMAIN)} />
        <PanelRow>
          <FormField
            id="title"
            label={__('Image title:', I18N_DOMAIN)}
            errors={selectedFile?.errors?.title}
          >
            <TextControl
              id="title"
              value={selectedFile?.title || ''}
              onChange={onChange('title')}
            />
          </FormField>
        </PanelRow>
        <PanelRow>
          <div className={!selectedFile?.asset_tag ? defaults?.asset_tag : ''}>
            <PanelHeader label={__('Tags', I18N_DOMAIN)} />
            <p className="blockText smallText greyText">
              {__(
                'Type tag out and hit enter to either select the existing tag highlighted in the suggestion list or hit enter key to create tag.',
                I18N_DOMAIN,
              )}
            </p>
            <FormField
              id="tags"
              errors={selectedFile?.errors?.asset_tag} // eslint-disable-line camelcase
              label={__('Search tags', I18N_DOMAIN)}
            >
              <Tags
                create
                onChange={onChange('asset_tag')}
                value={selectedFile?.asset_tag || []} // eslint-disable-line camelcase
              />
            </FormField>
          </div>
        </PanelRow>
      </Panel>
      <Panel>
        <PanelHeader label={__('Image Meta', I18N_DOMAIN)} />
        <PanelRow>
          {!selectedFile?.meta?.isAltRequired ? (
            <FormField
              id="alt"
              label={__('Alt:', I18N_DOMAIN)}
              errors={selectedFile?.errors?.alt}
              isRequired
            >
              <TextControl id="alt" value={selectedFile?.alt || ''} onChange={onChange('alt')} />
            </FormField>
          ) : (
            <FormField id="alt" label={__('Alt:', I18N_DOMAIN)}>
              <TextControl id="alt" value={selectedFile?.alt || ''} onChange={onChange('alt')} />
            </FormField>
          )}
        </PanelRow>
        <PanelRow>
          <ExternalLink href="https://www.w3.org/WAI/tutorials/images/decision-tree/">
            Describe the purpose of the image.
          </ExternalLink>
        </PanelRow>
        <PanelRow className="panel-row-with-tooltip">
          <FormField id="checkbox" errors={selectedFile?.errors?.['meta.isAltRequired']}>
            <CheckboxControl
              className="checkboxLabel"
              id="checkbox"
              label={__('Use empty alt attribute', I18N_DOMAIN)}
              checked={selectedFile?.meta?.isAltRequired}
              onChange={onChangeMeta('isAltRequired')}
            />
          </FormField>
          <p class="tooltip">
            <Dashicon icon="editor-help" className="tooltip" />
            <span className="tooltiptext">
              <b>{__('When to use empty alt tag. For images that are decorative:', I18N_DOMAIN)}</b>
              <ul className="listItem">
                <li> {__('Visual styling such as borders, spacers, and corners.', I18N_DOMAIN)}</li>
                <li>
                  {__(
                    'Supplementary to link text to improve its appearance or increase the clickable area.',
                    I18N_DOMAIN,
                  )}
                </li>
                <li>
                  {__(
                    'Illustrative of adjacent text but not contributing information (“eye-candy”).',
                    I18N_DOMAIN,
                  )}
                </li>
                <li>{__('Identified and described by surrounding text.', I18N_DOMAIN)}</li>
              </ul>
            </span>
          </p>
        </PanelRow>
        <PanelRow className="caption-panel">
          <FormField
            id="caption"
            label={__('Caption:', I18N_DOMAIN)}
            errors={selectedFile?.errors?.caption}
          >
            <RichText
              id="caption"
              value={selectedFile?.caption || ''}
              onChange={onChange('caption')}
            />
          </FormField>
        </PanelRow>
        <PanelRow>
          <FormField
            id="publication"
            label={__('Publication:', I18N_DOMAIN)}
            errors={selectedFile?.errors?.publication}
          >
            <SelectControl
              className={!selectedFile?.publication ? defaults?.publication : ''}
              id="publication"
              value={selectedFile?.publication || 0}
              options={publicationsOptions}
              onChange={onChange('publication')}
            />
          </FormField>
        </PanelRow>
        <PanelRow>
          <FormField
            id="active"
            label={__('Active:', I18N_DOMAIN)}
            help={__(
              'Active state dictates whether or not the image can be inserted into articles.',
              I18N_DOMAIN,
            )}
            fieldClass="toggleControl"
            errors={selectedFile?.errors?.['meta.active']}
          >
            <ToggleControl
              id="active"
              checked={selectedFile?.meta?.active}
              onChange={onChangeMeta('active')}
            />
          </FormField>
        </PanelRow>
      </Panel>
      <Panel className="fileForm-imageRights">
        <PanelHeader label={__('Images Rights Information', I18N_DOMAIN)} />
        <div className="fileForm-imageRightsGrid">
          <div>
            <PanelRow>
              <FormField
                id="rights"
                label={__('Image rights:', I18N_DOMAIN)}
                errors={selectedFile?.errors?.asset_image_rights} // eslint-disable-line camelcase
                isRequired
              >
                <SelectControl
                  id="rights"
                  className={!selectedFile?.asset_image_rights ? defaults?.asset_image_rights : ''}
                  value={selectedFile?.asset_image_rights || 0} // eslint-disable-line camelcase
                  options={rightsOptions}
                  onChange={onChange('asset_image_rights')}
                />
              </FormField>
            </PanelRow>
            <PanelRow>
              <FormField
                id="credit"
                label={__('Credit:', I18N_DOMAIN)}
                errors={selectedFile?.errors?.['meta.credit']}
                isRequired
              >
                <TextControl
                  className={!selectedFile?.meta?.credit ? defaults?.credit : ''}
                  id="credit"
                  value={selectedFile?.meta?.credit || ''}
                  onChange={onChangeMeta('credit')}
                />
              </FormField>
            </PanelRow>
            <PanelRow>
              <FormField
                id="credit-url"
                label={__('Credit url:', I18N_DOMAIN)}
                errors={selectedFile?.errors?.['meta.credit_url']}
              >
                <TextControl
                  id="credit-url"
                  className={!selectedFile?.meta?.credit_url ? defaults?.credit_url : ''}
                  value={selectedFile?.meta?.credit_url || ''}
                  onChange={onChangeMeta('credit_url')}
                />
              </FormField>
            </PanelRow>
          </div>
          <div>
            <PanelRow>
              <FormField
                id="rightsNotes"
                label={__('Notes:', I18N_DOMAIN)}
                errors={selectedFile?.errors?.meta?.image_rights_notes} // eslint-disable-line camelcase
              >
                <TextareaControl
                  className={
                    !selectedFile?.meta?.image_rights_notes ? defaults?.image_rights_notes : ''
                  }
                  id="rightsNotes"
                  value={selectedFile?.meta?.image_rights_notes || ''} // eslint-disable-line camelcase
                  onChange={onChangeMeta('image_rights_notes')}
                />
              </FormField>
            </PanelRow>
          </div>
        </div>
      </Panel>
      {onSave && (
        <Button isPrimary onClick={() => onSave(selectedFile)}>
          Save
        </Button>
      )}
    </div>
  );
};

export default compose(
  withSelect(select => ({
    selectedFile: select(STORE_NAME).getSelectedFile(),
  })),
)(Form);
