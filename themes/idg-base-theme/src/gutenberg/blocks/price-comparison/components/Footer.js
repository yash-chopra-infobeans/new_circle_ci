/**
 * External dependencies.
 */
import Proptypes from 'prop-types';

/**
 * WordPress dependencies.
 */
const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;

/**
 * Footer component. Renders header of the Price Comparion block.
 *
 * @return {*} JSX markup.
 */
const Footer = ({
  attributes,
  setAttributes,
  showViewMoreButton,
  isShowingAllRecords,
  setIsShowingAllRecords,
}) => {
  const { footerText } = attributes;

  const getViewMoreButtonText = () => {
    if (isShowingAllRecords) {
      return __('View fewer prices', 'idg-base-theme');
    }
    return __('View more prices', 'idg-base-theme');
  };

  return (
    <div className="price-comparison__record price-comparison__record--footer">
      <RichText
        tagName="span"
        className="price-comparison__footer-text"
        placeholder={__('Block footer text...', 'idg-base-theme')}
        value={footerText}
        onChange={value => {
          setAttributes({ footerText: value });
        }}
        formattingControls={[]}
      />
      {showViewMoreButton && (
        <button
          onClick={() => setIsShowingAllRecords(!isShowingAllRecords)}
          className="price-comparison__view-more-button"
        >
          {getViewMoreButtonText()}
        </button>
      )}
    </div>
  );
};

Footer.exports = {
  showViewMoreButton: Proptypes.bool.isRequired,
  isShowingAllRecords: Proptypes.bool.isRequired,
  setIsShowingAllRecords: Proptypes.func.isRequired,
};

export default Footer;
