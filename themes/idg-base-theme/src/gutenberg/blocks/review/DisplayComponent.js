import Sidebar from './components/Sidebar';
import StarRating from './components/StarRating';

const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;
const { InnerBlocks } = wp.editor;

const DisplayComponent = ({ attributes, setAttributes }) => (
  <>
    <Sidebar attributes={attributes} setAttributes={setAttributes} />
    <div className="review">
      {attributes.editorsChoice && !attributes.comparisonProductId && (
        <div className="review-banner">{__("Editors' Choice")}</div>
      )}
      <RichText
        className="review-title"
        value={attributes.heading}
        tagName="h2"
        onChange={heading => setAttributes({ heading })}
      />
      {!attributes.comparisonProductId && (
        <>
          <StarRating rating={attributes.rating} />
          <div className="review-columns">
            <div className="review-column">
              <h3 className="review-subTitle">Pros</h3>
              <RichText
                value={attributes.pros}
                tagName="ul"
                multiline="li"
                className="pros review-list"
                placeholder="Add pros..."
                onChange={pros => {
                  // eslint-disable-next-line no-param-reassign
                  pros = pros.replace(/^<li><\/li>$/gm, '');
                  setAttributes({ pros });
                }}
              />
            </div>
            <div className="review-column">
              <h3 className="review-subTitle">Cons</h3>
              <RichText
                value={attributes.cons}
                tagName="ul"
                multiline="li"
                placeholder="Add cons..."
                className="cons review-list"
                onChange={cons => {
                  // eslint-disable-next-line no-param-reassign
                  cons = cons.replace(/^<li><\/li>$/gm, '');
                  setAttributes({ cons });
                }}
              />
            </div>
          </div>
        </>
      )}
      <h3
        className={`review-subTitle ${
          !attributes.comparisonProductId ? 'review-subTitle--borderTop' : ''
        }`}
      >
        {__('Our Verdict')}
      </h3>
      <RichText
        value={attributes.verdict}
        tagName="p"
        className="verdict"
        placeholder="Add a verdict..."
        onChange={verdict => setAttributes({ verdict })}
      />
    </div>
    {attributes.primaryProductId && !attributes.comparisonProductId && (
      <>
        <RichText
          value={attributes.pricingTitle}
          tagName="h3"
          placeholder={__('Pricing title...', 'idg-base-theme')}
          onChange={pricingTitle => setAttributes({ pricingTitle })}
        />
        <p>
          {__(
            `This value will show the geolocated pricing text for product ${attributes.primaryProductId}`,
            'idg-base-theme',
          )}
        </p>
        <RichText
          value={attributes.bestPricingTitle}
          tagName="h3"
          placeholder={__('Best pricing title...', 'idg-base-theme')}
          onChange={bestPricingTitle => setAttributes({ bestPricingTitle })}
        />
        <InnerBlocks
          template={[
            ['idg-base-theme/price-comparison-block', { productId: attributes.primaryProductId }],
          ]}
          templateLock="all"
        />
      </>
    )}
  </>
);

export default DisplayComponent;
