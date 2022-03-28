import { isEmpty } from 'lodash-es';
import { Tab } from '../components/Tab';
import withForwardedRef from '../utils/forwardRef';

const { Component } = wp.element;
const { applyFilters } = wp.hooks;
const { withDispatch } = wp.data;
const { compose } = wp.compose;

export class TabContainer extends Component {
  handleTitleChange = (tab, name) => value => {
    const { titles, subtitles, editPost, tabs = [], titleTab = {} } = this.props;

    const tabOptions = tabs.find(ttab => ttab.name === tab.name);
    const newValue = tabOptions?.onChange ? tabOptions?.onChange({ tab, name, value }) : value;

    const newTitles = {
      ...(titles || {}),
      [tab.name]: {
        ...(titles?.[tab?.name] || {}),
        [name]: newValue,
      },
    };

    // get keys that inherit this tabs value/prefix
    const inheritValueKeys = tabs.reduce(
      (accu, curr) => {
        const currentTabsValue = titles?.[tab.name]?.[name];
        const reducerTabsValue = titles?.[curr.name]?.[name];

        // if current tabs value and inherited tabs value are not the same do nothing
        if (currentTabsValue !== reducerTabsValue) {
          return accu;
        }

        // current tab IN reducer inherits value from current tabs value add to keys
        if (name === 'value' && curr?.inheritValueFrom) {
          const { inheritValueFrom } = curr;
          const inheritTabName =
            typeof inheritValueFrom === 'function' ? inheritValueFrom(titles) : inheritValueFrom;
          if (inheritTabName === tab.name) {
            accu.attributes.push(curr.name);

            if (curr?.metaKey) {
              accu.metaKeys.push(curr.metaKey);
            }

            return accu;
          }
        }

        // current tab IN reducer inherits prefix from current tabs prefix add to keys
        if (name === 'prefix' && curr?.prefix?.inheritValueFrom) {
          const { inheritValueFrom } = curr.prefix;
          const inheritTabName =
            typeof inheritValueFrom === 'function' ? inheritValueFrom(titles) : inheritValueFrom;
          if (inheritTabName === tab.name) {
            accu.attributes.push(curr.name);

            if (curr?.prefix?.metaKey) {
              accu.metaKeys.push(curr.prefix.metaKey);
            }

            return accu;
          }
        }

        return accu;
      },
      { attributes: [], metaKeys: [] },
    );

    const newAttrs = {
      ...newTitles,
      ...inheritValueKeys.attributes.reduce(
        (carry, key) => ({
          ...carry,
          [key]: {
            ...(titles?.[key] || {}),
            [name]: newValue,
          },
        }),
        {},
      ),
    };

    let defaultTitle = newAttrs?.[titleTab?.name]?.value || '';

    if (titleTab?.name === tab.name && name === 'value') {
      defaultTitle = newValue;
    }

    /**
     * Update post title
     * Update multi-title meta field that stores the data in a single meta field
     * Update the individual meta field if the changed value has a metaKey
     */
    editPost({
      title: applyFilters('multi_title_post_title', defaultTitle, newAttrs),
      meta: {
        multi_title: JSON.stringify({
          titles: newAttrs,
          subtitles,
        }),
        ...(tab?.metaKey && name === 'value' && { [tab.metaKey]: newValue }),
        ...(tab?.prefix?.metaKey && name === 'prefix' && { [tab.prefix.metaKey]: newValue }),
        ...inheritValueKeys.metaKeys.reduce(
          (carry, key) => ({
            ...carry,
            [key]: newValue,
          }),
          {},
        ),
      },
    });

    return newValue;
  };

  handleAdditionalFieldsChange = (tab, name, saveIndividually = false) => value => {
    const { editPost, titles, subtitles, titleTab } = this.props;
    const newTitles = {
      ...(titles || {}),
      [tab.name]: {
        ...(titles?.[tab.name] || {}),
        additional: {
          ...(titles?.[tab.name]?.additional || {}),
          [name]: value,
        },
      },
    };

    let defaultTitle = newTitles?.[titleTab?.name]?.value || '';

    if (titleTab?.name === tab.name && name === 'value') {
      defaultTitle = value;
    }

    /**
     * Update post title
     * Update multi-title meta field that stores the data in a single meta field
     * Update individual meta field if third parameter (saveIndividually) is true
     */
    editPost({
      title: applyFilters('multi_title_post_title', defaultTitle, titles),
      meta: {
        multi_title: JSON.stringify({
          titles: newTitles,
          subtitles,
        }),
        ...(saveIndividually && { [name]: value }),
      },
    });
  };

  getCurrentPlaceholder = tab => {
    const { titles } = this.props;
    let placeholder = tab.title;

    // check if placeholder value should be inherited from another tab
    if (
      titles?.[tab.inheritPlaceholderFrom]?.value &&
      titles[tab.inheritPlaceholderFrom].value.length > 0
    ) {
      placeholder = titles[tab.inheritPlaceholderFrom].value;
      return placeholder;
    }

    // check if current tab has placeholder
    if (tab.placeholder) {
      const { placeholder: ph } = tab;
      placeholder = ph;
      return placeholder;
    }

    // default placeholder will be title property of the current tab
    return placeholder;
  };

  getCurrentTitle = tab => {
    const { titles } = this.props;
    let currentTitle = titles?.[tab.name]?.value || '';

    /**
     * return value from tab specified within tab.inheritValueFrom
     * and the inherited tabs value exists
     */
    if (tab?.inheritValueFrom && typeof titles?.[tab.name]?.value === 'undefined') {
      const { inheritValueFrom } = tab;
      const inheritTabName =
        typeof inheritValueFrom === 'function' ? inheritValueFrom(titles || {}) : inheritValueFrom;
      if (inheritTabName && titles?.[inheritTabName]) {
        currentTitle = titles[inheritTabName].value;
      }
    }

    return currentTitle;
  };

  getCurrentPrefix = tab => {
    const { titles } = this.props;
    let prefixTitle = titles?.[tab.name]?.prefix || '';

    if (tab?.prefix?.inheritValueFrom && typeof titles?.[tab.name]?.prefix === 'undefined') {
      const { inheritValueFrom } = tab.prefix;
      const inheritTabName =
        typeof inheritValueFrom === 'function' ? inheritValueFrom(titles || {}) : inheritValueFrom;
      prefixTitle = titles?.[inheritTabName]?.prefix || '';
    }

    return prefixTitle;
  };

  getAdditionalFieldValue = (tabName, name) => {
    const { titles } = this.props;
    const value = titles?.[tabName]?.additional?.[name] || '';

    return value;
  };

  getCharLimit = (tab, name) => {
    const { combineCharLimit } = tab;
    let { charLimit = 0 } = tab;

    // return combined charLimit title charLimit + prefix charLimit
    if ((combineCharLimit || tab?.prefix?.combineCharLimit) && tab?.prefix?.charLimit) {
      charLimit += tab.prefix.charLimit;
      return charLimit;
    }

    // return prefix charLimit
    if (name === 'prefix') {
      return tab?.prefix?.charLimit || 0;
    }

    // return title charLimit
    return charLimit;
  };

  getCharLimitValue = (tab, name) => {
    const { titles } = this.props;
    const tabAttrs = titles?.[tab.name];

    // return empty string if data doesn't exist for field (possible on new post etc)
    if (!tabAttrs) {
      return '';
    }

    const { prefix = '', value = '' } = tabAttrs;

    // only return value from attributes if value is being combined
    if (tab?.combineCharLimit || tab?.prefix?.combineCharLimit) {
      if (name === 'prefix') {
        return prefix;
      }

      return value;
    }

    return '';
  };

  setInheritedValue = (tab, name) => value => {
    if (!isEmpty(value)) {
      return;
    }

    const { titles } = this.props;
    let inheritedValue;
    if (name === 'value') {
      const { inheritValueFrom } = tab || {};
      const inheritTabName =
        typeof inheritValueFrom === 'function' ? inheritValueFrom(titles || {}) : inheritValueFrom;
      inheritedValue = titles?.[inheritTabName]?.value;
    } else {
      const { inheritValueFrom } = tab?.prefix || {};
      const inheritTabName =
        typeof inheritValueFrom === 'function' ? inheritValueFrom(titles || {}) : inheritValueFrom;
      inheritedValue = titles?.[inheritTabName]?.prefix;
    }
    if (!isEmpty(inheritedValue)) {
      this.handleTitleChange(tab, name)(inheritedValue);
    }
  };

  render() {
    const { forwardedRef } = this.props;

    return (
      <Tab
        {...this.props}
        getCurrentTitle={this.getCurrentTitle}
        getCurrentPlaceholder={this.getCurrentPlaceholder}
        getCurrentPrefix={this.getCurrentPrefix}
        handleTitleChange={this.handleTitleChange}
        getCharLimitValue={this.getCharLimitValue}
        getCharLimit={this.getCharLimit}
        handleAdditionalFieldsChange={this.handleAdditionalFieldsChange}
        getAdditionalFieldValue={this.getAdditionalFieldValue}
        onTitleBlur={this.setInheritedValue}
        onPrefixBlur={this.setInheritedValue}
        ref={forwardedRef}
      />
    );
  }
}

/* istanbul ignore next */
const applyWithDispatch = withDispatch(dispatch => ({
  editPost: data => dispatch('core/editor').editPost(data),
}));

export default compose(withForwardedRef, applyWithDispatch)(TabContainer);
