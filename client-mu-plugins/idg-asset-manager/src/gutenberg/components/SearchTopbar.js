import { isEmpty } from 'lodash-es';

import { I18N_DOMAIN } from '../../settings';
import TextWithDate from './TextWithDate';

const { __ } = wp.i18n;
const { TextControl, Dashicon, Button } = wp.components;

const { moment } = window;

const SearchTopbar = ({ params, onChange, setMultipleParams }) => (
  <div className="searchTopbar">
    <div className="dateRange">
      <div className="dateRange-from">
        <TextWithDate
          label={__('Start Date', I18N_DOMAIN)}
          value={params?.after}
          onChange={newValue => {
            const startOfDay = moment(newValue).startOf('day').format('YYYY-MM-DD HH:mm:ss');
            onChange('after')(startOfDay);
          }}
        />
      </div>
      <div className="dateRange-to">
        <TextWithDate
          label={__('End Date', I18N_DOMAIN)}
          value={params?.before}
          onChange={newValue => {
            const endOfDay = moment(newValue).endOf('day').format('YYYY-MM-DD HH:mm:ss');
            onChange('before')(endOfDay);
          }}
        />
      </div>
      {(!isEmpty(params?.before) || !isEmpty(params?.after)) && (
        <Button
          onClick={() => {
            setMultipleParams({ after: null, before: null });
          }}
        >
          <Dashicon icon="no-alt" />
        </Button>
      )}
    </div>
    <div className="searchTerm">
      <div className="fieldWithIcon fieldWithIcon--left">
        <Dashicon icon="search" />
        <TextControl
          label={__('Search', I18N_DOMAIN)}
          placeholder={__('Search', I18N_DOMAIN)}
          value={params?.search || ''}
          onChange={onChange('search')}
          hideLabelFromVision
        />
      </div>
    </div>
  </div>
);

export default SearchTopbar;
