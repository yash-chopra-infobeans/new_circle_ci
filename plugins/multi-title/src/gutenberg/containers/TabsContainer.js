import { isEqual, isEmpty, flatten, forEach, cloneDeep } from 'lodash-es';
import classNames from 'classnames';
import { Tabs } from '../components/Tabs';
import { tabs } from '../defaultTabs';

const { __ } = wp.i18n;
const { Component, createRef } = wp.element;
const { applyFilters } = wp.hooks;
const { withSelect, withDispatch } = wp.data;
const { compose } = wp.compose;

const getMetaKeys = tab => {
  const value = [];

  Object.keys(tab).some(key => {
    if (['metaKey', 'metaKeys'].includes(key)) {
      value.push(tab[key]);
    } else if (tab[key] && typeof tab[key] === 'object') {
      value.push(getMetaKeys(tab[key]));
    }

    return false;
  });

  return flatten(value);
};

export class TabsContainer extends Component {
  constructor(...props) {
    super(...props);

    /**
     * Filter to allow developers to define titles, a map is done at the end
     * to set default classe(s) for titles using the title name property.
     */
    this.tabs = applyFilters('multi_title_tabs', tabs).map(tab => ({
      ...tab,
      className: classNames('tab', {
        [`tab-${tab.name}`]: tab.name,
        [`${tab.className}`]: tab.className,
      }),
    }));

    this.titleTab = this.tabs.find(tab => tab?.isTitle === true);
    if (!this.titleTab) {
      this.titleTab = this.tabs[0]; // eslint-disable-line prefer-destructuring
    }
    const metaKeys = ['multi_title'];

    this.tabs.forEach(tab => metaKeys.push(getMetaKeys(tab)));
    // eslint-disable-next-line react/destructuring-assignment
    this.props.setBlockMetaFields(flatten(metaKeys));

    // default active tab on mount will be the first tab if tabs are set
    this.state = {
      errorMessages: {},
      activeTab: this.tabs.length > 0 ? this.tabs[0].name : '',
    };

    this.prefixRef = createRef();
    this.titleRef = createRef();
  }

  componentDidMount() {
    // validates tabs and will prevent the post from being published if criteria isn't met
    this.setMultiTitleData();
    this.validateTabs();
  }

  shouldComponentUpdate(nextProps) {
    if (isEqual(nextProps, this.props)) {
      return false;
    }

    return true;
  }

  componentDidUpdate(prevProps) {
    const { titles: oldTitles } = prevProps;
    const { titles } = this.props;

    /**
     * Validate tabs when data has changed, to add/remove post lock(s).
     */
    if (!isEqual(oldTitles, titles)) {
      this.validateTabs();
    }
  }

  selectTab = tabName => {
    const { isRevisionsOpen } = this.props;

    this.setState({ activeTab: tabName }, () => {
      const inputRef = this.prefixRef.current ? this.prefixRef.current : this.titleRef.current;
      const { value } = inputRef;

      if (isRevisionsOpen) {
        return;
      }

      inputRef.focus();
      inputRef.setSelectionRange(value.length, value.length);
    });
  };

  setMultiTitleData = () => {
    const { titles = {}, subtitles, meta, editPost, title = '' } = this.props;
    const newTitles = cloneDeep(titles);
    let individualMeta = {};

    // return if there's no data. ie new post
    if (isEmpty(newTitles)) {
      return;
    }

    this.tabs.forEach(tab => {
      if (!newTitles?.[tab.name]) {
        newTitles[tab.name] = {};
      }

      // if title value in meta set it
      const titleDiff = tab.name === this.titleTab.name && title !== titles?.[tab.name]?.value;
      if (titleDiff || (tab.metaKey && titles?.[tab.name]?.value !== meta?.[tab.metaKey])) {
        newTitles[tab.name].value = titleDiff ? title : meta?.[tab?.metaKey] || '';

        this.tabs.forEach(tabtwo => {
          const { inheritValueFrom } = tabtwo;
          const inheritTabTwo =
            typeof inheritValueFrom === 'function' ? inheritValueFrom(titles) : inheritValueFrom;
          if (
            inheritTabTwo !== tab.name ||
            titles?.[tabtwo.name]?.value !== titles?.[inheritTabTwo]?.value
          ) {
            return;
          }

          if (tabtwo?.metaKey) {
            individualMeta = {
              ...individualMeta,
              [tabtwo.metaKey]: newTitles[tab.name].value,
            };
          }

          newTitles[tabtwo.name] = {
            ...(titles?.[tabtwo?.name] || {}),
            value: newTitles[tab.name].value,
          };
        });
      }

      if (tab?.prefix?.metaKey && titles?.[tab.name]?.prefix !== meta?.[tab.prefix.metaKey]) {
        newTitles[tab.name].prefix = meta?.[tab?.prefix?.metaKey] || '';

        this.tabs.forEach(tabthree => {
          const { prefix } = tabthree;
          const inheritTabThree =
            typeof prefix?.inheritValueFrom === 'function'
              ? prefix?.inheritValueFrom(titles)
              : prefix?.inheritValueFrom;

          if (
            inheritTabThree !== tab.name ||
            titles?.[tabthree.name]?.prefix !== titles?.[inheritTabThree]?.prefix
          ) {
            return;
          }

          if (tabthree?.prefix?.metaKey) {
            individualMeta = {
              ...individualMeta,
              [tabthree.prefix.metaKey]: newTitles[tab.name].prefix,
            };
          }

          newTitles[tabthree.name] = {
            ...(newTitles?.[tabthree?.name] || {}),
            prefix: newTitles[tab.name].prefix,
          };
        });
      }

      /**
       * if additional field keys present see if meta exists,
       * if it does update multiTitle meta field
       */
      if (tab?.additionalFields?.metaKeys) {
        newTitles[tab.name].additional = newTitles[tab.name]?.additional || {};

        tab.additionalFields.metaKeys.forEach(metaKey => {
          if (meta?.[metaKey]) {
            newTitles[tab.name].additional[metaKey] = meta[metaKey];
          }
        });
      }
    });

    const updatedPost = {
      title: applyFilters('multi_title_post_title', newTitles[this.titleTab.name].value, titles),
      meta: {
        multi_title: JSON.stringify({
          titles: newTitles,
          subtitles,
        }),
        ...individualMeta,
      },
    };

    // eslint-disable-next-line camelcase
    if (!meta?.multi_title && meta?.multi_title !== '') {
      return;
    }

    const currentPost = {
      title,
      meta: {
        multi_title: meta.multi_title,
        ...individualMeta,
      },
    };

    if (isEqual(updatedPost, currentPost)) {
      return;
    }

    editPost(updatedPost);
  };

  handleSubtitleChange = name => value => {
    const { titles, subtitles, editPost } = this.props;
    const newAttrs = {
      titles,
      subtitles: {
        ...(subtitles || {}),
        [name]: value,
      },
    };

    // update post meta
    editPost({
      meta: {
        multi_title: JSON.stringify(newAttrs),
      },
    });
  };

  blockPublishing = tab => ({
    [tab.name]: {
      title: tab.title,
      message: __('You have exceeded the maximum character limit.', 'multi-title'),
      preventPublish: true,
    },
  });

  validateTabs() {
    const { lockPostSaving, unlockPostSaving } = this.props;
    let errorMessages = {};

    forEach(this.tabs, tab => {
      const validation = this.validateTab(tab);

      if (!isEmpty(validation)) {
        errorMessages = {
          ...errorMessages,
          ...validation,
        };
        // validation failed APPLY publishing block
        lockPostSaving(`multitle-${tab.name}`);
      } else {
        // everything okay REMOVE publishing block
        unlockPostSaving(`multitle-${tab.name}`);
      }
    });

    this.setState({
      errorMessages,
    });
  }

  validateTab(tab) {
    const { titles } = this.props;
    const tabAttrs = titles?.[tab.name];

    if (!tabAttrs) {
      return {};
    }

    let { charLimit = 0 } = tab;
    let { value = '' } = tabAttrs;
    const { prefix = '' } = tabAttrs;

    // if combined and prefix has a charLimit set, update charLimit & charLimit variables
    if (
      (tab?.blockPublishOnError || tab?.prefix?.blockPublishOnError) &&
      (tab?.combineCharLimit || tab?.prefix?.combineCharLimit)
    ) {
      charLimit += tab?.prefix?.charLimit || 0;
      value = `${value}${prefix}`;

      // block publishing as value length exceeds character limit
      if (value.length > charLimit) {
        return this.blockPublishing(tab);
      }

      return {};
    }

    // if title value greater than title charLimit block publishing
    if (tab?.blockPublishOnError && tab?.charLimit && value.length > tab?.charLimit) {
      return this.blockPublishing(tab);
    }

    // if prefix value greater than prefix charLimit block publishing
    if (
      tab?.prefix?.blockPublishOnError &&
      tab?.prefix?.charLimit &&
      prefix.length > tab?.prefix?.charLimit
    ) {
      return this.blockPublishing(tab);
    }

    return {};
  }

  render() {
    const { errorMessages, activeTab } = this.state;

    return (
      <Tabs
        {...this.props}
        tabs={this.tabs}
        titleTab={this.titleTab}
        handleSubtitleChange={this.handleSubtitleChange}
        errorMessages={errorMessages}
        activeTab={activeTab}
        selectTab={this.selectTab}
        onError={this.onError}
        ref={{
          prefixRef: this.prefixRef,
          titleRef: this.titleRef,
        }}
      />
    );
  }
}

const getMeta = (select, ownProps) => {
  const { getPostFromBlockAttributes = false } = select('bigbite/revisions') || {};

  if (getPostFromBlockAttributes) {
    return getPostFromBlockAttributes(ownProps?.attributes, 'meta');
  }

  return select('core/editor').getEditedPostAttribute('meta');
};

/* istanbul ignore next */
const applyWithSelect = withSelect((select, ownProps) => {
  const isRevisionsOpen = select('bigbite/revisions')?.isOpen() || false;
  const meta = getMeta(select, ownProps);
  const title = select('core/editor').getEditedPostAttribute('title');
  const multiTitle = meta?.multi_title; // eslint-disable-line camelcase
  const parsedMultiTitle = multiTitle ? JSON.parse(multiTitle) : {};

  return {
    isRevisionsOpen,
    meta,
    title,
    titles: parsedMultiTitle?.titles || {},
    subtitles: parsedMultiTitle?.subtitles || {},
  };
});

/* istanbul ignore next */
const applyWithDispatch = withDispatch(dispatch => ({
  editPost: data => dispatch('core/editor').editPost(data),
  lockPostSaving: name => dispatch('core/editor').lockPostSaving(name),
  unlockPostSaving: name => dispatch('core/editor').unlockPostSaving(name),
  setBlockMetaFields: blocks =>
    dispatch('bigbite/revisions')?.setBlockMetaFields?.('bigbite/multi-title', blocks),
}));

export default compose(applyWithSelect, applyWithDispatch)(TabsContainer);
