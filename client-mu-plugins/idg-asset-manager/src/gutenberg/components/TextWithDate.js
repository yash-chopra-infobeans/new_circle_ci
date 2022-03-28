const { TextControl, DatePicker, Popover, Dashicon } = wp.components;
const { useState } = wp.element;
const { moment } = window;

const DATE_FORMAT = 'YYYY-MM-DD';

const TextWithDate = ({ label = '', value, onChange, hideLabel = true }) => {
  const [openDatePopup, setOpenDatePopup] = useState(false);

  return (
    <>
      <div className={`dateRangeField fieldWithIcon ${!hideLabel ? 'has-label' : ''}`}>
        <TextControl
          label={label}
          placeholder={label}
          value={value ? moment(value).format(DATE_FORMAT) : ''}
          onClick={() => setOpenDatePopup(true)}
          onChange={onChange}
          hideLabelFromVision={hideLabel}
        />
        <Dashicon onClick={() => setOpenDatePopup(true)} icon="calendar-alt" />
        {openDatePopup && (
          <Popover position="bottom" onClose={setOpenDatePopup.bind(null, false)}>
            <DatePicker label={label} currentDate={value} onChange={onChange} />
          </Popover>
        )}
      </div>
    </>
  );
};

export default TextWithDate;
