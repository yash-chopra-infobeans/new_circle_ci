import { I18N_DOMAIN } from '../../settings';

const { moment } = window;
const { compose } = wp.compose;
const { Button, DateTimePicker, Dropdown } = wp.components;
const { withDispatch, withSelect } = wp.data;
const { PluginPostStatusInfo } = wp.editPost;
const { Component } = wp.element;
const { __ } = wp.i18n;

class UpdatedDateComponent extends Component {
  constructor() {
    super();
    this.onChange = this.onChange.bind(this);
    this.clearData = this.clearData.bind(this);
  }

  onChange(value) {
    const { editPost } = this.props;
    const storedDate = value;
    const formattedStoredDate = moment(storedDate).format('YYYY-MM-DD');

    editPost({
      meta: {
        _idg_updated_date: formattedStoredDate,
      },
    });
  }

  clearData() {
    const { editPost } = this.props;
    editPost({
      meta: {
        _idg_updated_date: '',
      },
    });
  }

  render() {
    const { editDate, editDateFormatted } = this.props;

    return (
      <>
        <PluginPostStatusInfo className="edit-post-post-embargo">
          <span>{__('Post Updated Date', I18N_DOMAIN)}</span>
          <Dropdown
            position="bottom left"
            contentClassName="edit-post-post-schedule__dialog"
            renderToggle={({ onToggle, isOpen }) => (
              <Button
                className="edit-post-post-schedule__toggle"
                onClick={onToggle}
                aria-expanded={isOpen}
                isTertiary
              >
                {editDateFormatted}
              </Button>
            )}
            renderContent={() => <DateTimePicker currentDate={editDate} onChange={this.onChange} />}
          />
          <Button onClick={this.clearData} isTertiary>
            Reset
          </Button>
        </PluginPostStatusInfo>
      </>
    );
  }
}

export default compose([
  withSelect(withSel => {
    const { getCurrentPostAttribute, getEditedPostAttribute } = withSel('core/editor');

    let { _idg_updated_date: currentEditDate } = getCurrentPostAttribute('meta');
    const { _idg_updated_date: updatedEditDate = null } = getEditedPostAttribute('meta');

    if (updatedEditDate !== null) {
      currentEditDate = updatedEditDate;
    }

    return {
      editDateFormatted: currentEditDate
        ? moment(currentEditDate).format('MMM Do, YYYY')
        : __('Set Date', I18N_DOMAIN),
    };
  }),
  withDispatch(withDis => ({
    editPost: withDis('core/editor').editPost,
  })),
])(UpdatedDateComponent);
