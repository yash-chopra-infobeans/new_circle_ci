import { I18N_DOMAIN } from '../settings';
import '../styles/embargo.scss';
import PrePublishHeader from './prepublish/PrePublishHeader';

const { moment } = window;
const { compose } = wp.compose;
const { Button, DateTimePicker, Dropdown } = wp.components;
const { dispatch, select, withDispatch, withSelect } = wp.data;
const { PluginPostStatusInfo } = wp.editPost;
const { Component } = wp.element;
const { __ } = wp.i18n;

export const subscribeForceSchedule = () => {
  const date = select('core/editor').getEditedPostAttribute('date');
  const meta = select('core/editor').getEditedPostAttribute('meta');

  if (!meta) {
    return;
  }

  const { embargo_date: embargoDate = '' } = meta;

  if (!embargoDate || embargoDate === '') {
    return;
  }

  if (date && moment(date).isSameOrAfter(embargoDate)) {
    return;
  }

  dispatch('core/editor').editPost({
    date: embargoDate,
  });
};

class EmbargoComponent extends Component {
  constructor() {
    super();

    this.onChange = this.onChange.bind(this);
    this.onPublishAnyway = this.onPublishAnyway.bind(this);
  }

  onChange(value) {
    const { editPost } = this.props;

    let storedDate = value;

    const isAfter = moment().isAfter(value);

    if (isAfter) {
      storedDate = '';
    }

    editPost({
      date: storedDate,
      meta: {
        embargo_date: storedDate,
      },
    });
  }

  onPublishAnyway(publishAnyway) {
    const publishButton = document.querySelector('.editor-post-publish-button');

    if (!publishButton) {
      return;
    }

    publishButton.disabled = !!publishAnyway;

    this.setState({
      publishAnyway,
    });
  }

  isBlocked = () => {
    const { date, embargoDate } = this.props;

    const currentEmbargoDate = moment(embargoDate);
    const currentDate = moment();
    const difference = currentEmbargoDate.diff(currentDate);

    if (moment(date).isSameOrAfter(currentEmbargoDate)) {
      return false;
    }

    const timeUntil = moment.duration(difference).asDays();
    return timeUntil > 0;
  };

  render() {
    const { embargoDate, embargoDateFormatted } = this.props;

    return (
      <>
        <PrePublishHeader.Approval name="idg-embargo" display={this.isBlocked} type="blocker">
          <p>
            Publising blocked due to <strong>embargo</strong> set for{' '}
            <strong>{embargoDateFormatted}</strong>.
          </p>
          <p>To publish this article now, you must first remove the embargo date.</p>
        </PrePublishHeader.Approval>
        <PluginPostStatusInfo className="edit-post-post-embargo">
          <span>{__('Embargo', I18N_DOMAIN)}</span>
          <Dropdown
            position="botton left"
            contentClassName="edit-post-post-schedule__dialog"
            renderToggle={({ onToggle, isOpen }) => (
              <Button
                className="edit-post-post-schedule__toggle"
                onClick={onToggle}
                aria-expanded={isOpen}
                isTertiary
              >
                {embargoDateFormatted}
              </Button>
            )}
            renderContent={() => (
              <DateTimePicker currentDate={embargoDate} onChange={this.onChange} />
            )}
          />
        </PluginPostStatusInfo>
      </>
    );
  }
}

export default compose([
  withSelect(withSel => {
    const { getCurrentPostAttribute, getEditedPostAttribute } = withSel('core/editor');

    let { embargo_date: currentEmbargoDate } = getCurrentPostAttribute('meta');
    const { embargo_date: updatedEmbargoDate = null } = getEditedPostAttribute('meta');
    const date = getEditedPostAttribute('date');

    if (updatedEmbargoDate !== null) {
      currentEmbargoDate = updatedEmbargoDate;
    }

    const isAfter = moment().isAfter(currentEmbargoDate);

    if (isAfter) {
      currentEmbargoDate = '';
    }

    return {
      embargoDate: currentEmbargoDate ? currentEmbargoDate : new Date(), // eslint-disable-line no-unneeded-ternary
      embargoDateFormatted: currentEmbargoDate
        ? moment(currentEmbargoDate).format('MMM Do, YYYY \\a\\t HH:mm')
        : __('Set Date', I18N_DOMAIN),
      isPastEmbargo: moment(currentEmbargoDate).isAfter(),
      date,
    };
  }),
  withDispatch(withDis => ({
    editPost: withDis('core/editor').editPost,
  })),
])(EmbargoComponent);
