import '../styles/gutenberg.scss';
import '../styles/publish-button.scss';
import { I18N_DOMAIN } from '../settings';

const { SelectControl } = wp.components;
const { compose } = wp.compose;
const { withDispatch, withSelect } = wp.data;
const { Component } = wp.element;
const { PluginPostStatusInfo } = wp.editPost;
const { __ } = wp.i18n;

class StatusSelectComponent extends Component {
  getOptions = () => {
    const { statuses } = window.IDGPublishingFlow;

    return statuses.map(status => ({
      label: status.label,
      value: status.name,
      disabled: status.option_disable,
    }));
  };

  render() {
    const { currentStatus, updateStatus } = this.props;

    return (
      <>
        <PluginPostStatusInfo className="article-status">
          <SelectControl
            label={__('Article status:', I18N_DOMAIN)}
            value={currentStatus}
            onChange={updateStatus}
            options={this.getOptions()}
          />
        </PluginPostStatusInfo>
      </>
    );
  }
}

export default compose(
  withSelect(selector => {
    let currentStatus = selector('core/editor').getEditedPostAttribute('status');
    let loadStatus = selector('core/editor').getCurrentPostAttribute('status');

    if (currentStatus === 'auto-draft') {
      currentStatus = 'draft';
    }

    if (loadStatus === 'auto-draft') {
      loadStatus = 'draft';
    }

    return {
      currentStatus,
      loadStatus,
    };
  }),
  withDispatch(dispatch => ({
    updateStatus(status) {
      dispatch('core/editor').editPost({ status });
    },
  })),
)(StatusSelectComponent);
