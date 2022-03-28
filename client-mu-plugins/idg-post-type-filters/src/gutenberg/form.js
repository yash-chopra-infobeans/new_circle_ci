import { I18N_DOMAIN } from './settings';
import TextWithDate from './components/TextWithDate';

const { __ } = wp.i18n;
const { useState } = wp.element;
const { moment } = window;

const DATE_FORMAT = 'YYYY-MM-DD';

const Form = () => {
  const urlString = window.location.href;
  const url = new URL(urlString);

  const [startDate, setStartDate] = useState(url.searchParams.get('from_date'));
  const [endDate, setEndDate] = useState(url.searchParams.get('to_date'));

  return (
    <>
      <div className="dateRange">
        <TextWithDate
          className="dateRange--startDate"
          label={__('Start date:', I18N_DOMAIN)}
          value={startDate}
          onChange={date => setStartDate(moment(date).format(DATE_FORMAT))}
          autoComplete="off"
        />
        <input type="hidden" name="from_date" value={startDate} />
        <TextWithDate
          className="dateRange--endDate"
          label={__('End date:', I18N_DOMAIN)}
          value={endDate}
          onChange={date => setEndDate(moment(date).format(DATE_FORMAT))}
          autoComplete="off"
        />
        <input type="hidden" name="to_date" value={endDate} />
      </div>
    </>
  );
};

export default Form;
