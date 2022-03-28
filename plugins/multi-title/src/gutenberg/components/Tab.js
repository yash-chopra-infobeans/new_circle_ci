import Input from './WrappedInput';

const { Slot } = wp.components;
const { forwardRef } = wp.element;

export const Tab = forwardRef((props, ref) => {
  const { prefixRef, titleRef } = ref;
  const {
    tab,
    getCurrentTitle,
    getCurrentPlaceholder,
    getCurrentPrefix,
    handleTitleChange,
    getCharLimitValue,
    getCharLimit,
    handleAdditionalFieldsChange,
    getAdditionalFieldValue,
    onTitleBlur,
    onPrefixBlur,
  } = props;
  const currentTitle = getCurrentTitle(tab);
  const placeholder = getCurrentPlaceholder(tab);
  const prefixTitle = getCurrentPrefix(tab);

  // remove className so that of tab properties can be passed to WrappedInput
  const {
    additionalFields,
    className,
    combineCharLimit,
    charLimit,
    prefix: prefixProperties,
    ...tabProperties
  } = tab;

  let additionalFieldsRender = additionalFields?.render || false;

  if (!additionalFieldsRender && typeof additionalFields === 'function') {
    additionalFieldsRender = additionalFields;
  }

  return (
    <div className="editor-post-title tabbed-title display">
      <div className="wp-block editor-post-title__block">
        {tab.prefix && (
          <Input
            name={prefixProperties.name || tabProperties.name}
            titleType="prefix"
            title={prefixProperties.title}
            placeholder="Kicker"
            value={prefixTitle}
            className={`title-PrefixInput title-PrefixInput-${tab.name}`}
            onChange={handleTitleChange(tab, 'prefix')}
            onBlur={onPrefixBlur(tab, 'prefix')}
            showCharLimitAt={0}
            showCharLimitOnFocus
            charLimitValue={getCharLimitValue(tab, 'value')}
            charLimit={getCharLimit(tab, 'prefix')}
            ref={prefixRef}
          />
        )}
        <Input
          name={tabProperties.name}
          titleType="value"
          title={tabProperties.title}
          placeholder={placeholder}
          value={currentTitle}
          className={`title-input title-input-${tab.name} title-input--main`}
          onChange={handleTitleChange(tab, 'value')}
          onBlur={onTitleBlur(tab, 'value')}
          showCharLimitAt={0}
          showCharLimitOnFocus
          charLimitValue={getCharLimitValue(tab, 'prefix')}
          charLimit={getCharLimit(tab, 'value')}
          ref={titleRef}
        />
        {additionalFieldsRender
          ? additionalFieldsRender({
              ...props,
              tab,
              handleChange: handleAdditionalFieldsChange,
              getValue: getAdditionalFieldValue,
            })
          : null}
        <Slot name={`multi-title-tab-${tab.name}`} />
      </div>
    </div>
  );
});

export default Tab;
