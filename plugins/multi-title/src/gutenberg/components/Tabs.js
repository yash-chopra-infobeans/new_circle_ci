import TextareaAutosize from 'react-autosize-textarea';
import { isEmpty } from 'lodash-es';
import TabComp from '../containers/TabContainer';

const { __ } = wp.i18n;
const { TabPanel, Slot, Fill, PanelBody } = wp.components;
const { applyFilters } = wp.hooks;
const { forwardRef } = wp.element;

export const Tabs = forwardRef((props, ref) => {
  const {
    errorMessages = {},
    activeTab,
    selectTab,
    tabs,
    onError,
    handleSubtitleChange,
    subtitles,
  } = props;
  // below filter allows developers to enable/disable standfirst
  const standfirst = applyFilters('multi_title_standfirst_enabled', false);
  const errors = Object.keys(errorMessages).reduce((obj, key) => {
    const newObj = obj;

    if (!errorMessages[key].preventPublish) {
      return newObj;
    }

    newObj[key] = errorMessages[key];

    return newObj;
  }, {});

  return (
    <div>
      {/*  if errors display them within the publishing panel
        so users know why they cannot publish */}
      {!isEmpty(errors) && (
        <Fill name="PluginPrePublishPanel">
          <PanelBody title={__('Errors', 'multi-title')}>
            {Object.keys(errorMessages).map(key => (
              <div className="publishErrors-section">
                <p>
                  <strong>{errorMessages[key].title}</strong>
                </p>
                <ul>
                  <li>{errorMessages[key].message}</li>
                </ul>
              </div>
            ))}
          </PanelBody>
        </Fill>
      )}
      <TabPanel
        className="title-tab-panel"
        activeClass="active-tab"
        tabs={tabs}
        onSelect={selectTab}
      >
        {tab => <TabComp {...props} activeTab={activeTab} tab={tab} tabs={tabs} ref={ref} />}
      </TabPanel>
      <Slot name="multi-title-below-tabs" />
      {standfirst && (
        <TextareaAutosize
          rows={1}
          placeHolder="Standfirst"
          className="title-input title-input-sm title-input-standfirst"
          onChange={e => handleSubtitleChange('standfirst')(e.target.value)}
          onError={onError}
          value={subtitles?.standfirst}
        />
      )}
    </div>
  );
});

export default Tabs;
