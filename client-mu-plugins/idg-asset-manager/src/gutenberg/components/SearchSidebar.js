/* eslint-disable camelcase */
import { I18N_DOMAIN } from '../../settings';
import TermSelector from './TermSelector';
import TermOptions from './TermsOptions';
import UserOptions from './UserOptions';
import Tags from './Tags';

const { __ } = wp.i18n;
const { Panel, PanelBody, Button } = wp.components;

const { publishingFlowSites: publicationOptions = [] } = window.assetManager;

const SearchSidebar = ({ onChange, params = {}, onClose }) => (
  <div className="searchSidebar">
    <div className="searchSidebar-header">
      <h1 className="assetManager-heading">{__('Filter Media', I18N_DOMAIN)}</h1>
      {onClose && (
        <Button onClick={onClose} icon="no-alt" label={__('Close dialog', I18N_DOMAIN)} />
      )}
    </div>
    <div className="searchSidebar-content">
      <Panel>
        <PanelBody title={__('Users', I18N_DOMAIN)} initialOpen={true}>
          <UserOptions
            value={params?.author || []}
            onChange={onChange('author')}
            search
            searchPlaceholder={__('Search Users...', I18N_DOMAIN)}
          />
        </PanelBody>
      </Panel>
      <Panel>
        <PanelBody title={__('Publications', I18N_DOMAIN)} initialOpen={true}>
          <TermSelector
            value={params?.publication || []}
            options={[...publicationOptions].filter(
              publicationOption => publicationOption.value > 0,
            )}
            onChange={onChange('publication')}
            search
            searchLabel={__('Search Publications', I18N_DOMAIN)}
            searchPlaceholder={__('Search Publications...', I18N_DOMAIN)}
            filter
          />
        </PanelBody>
      </Panel>
      <Panel>
        <PanelBody title={__('Image rights', I18N_DOMAIN)} initialOpen={true}>
          <TermOptions
            taxonomy="asset_image_rights"
            value={params?.asset_image_rights || []}
            onChange={onChange('asset_image_rights')}
            search
            searchPlaceholder={__('Search Image Rights...', I18N_DOMAIN)}
          />
        </PanelBody>
      </Panel>
      <Panel>
        <PanelBody title={__('Tags', I18N_DOMAIN)} initialOpen={true}>
          <Tags value={params?.asset_tag || []} onChange={onChange('asset_tag')} />
        </PanelBody>
      </Panel>
    </div>
  </div>
);

export default SearchSidebar;
