const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;

const SaveComponent = ({ attributes }) => (
  <>
    {!attributes.comparisonProductId && (attributes.pros || attributes.cons) && (
      <div>
        <div className="review-columns">
          {attributes.pros && (
            <div className="review-column">
              <h3 className="review-subTitle">Pros</h3>
              <RichText.Content
                value={attributes.pros}
                tagName="ul"
                multiline="li"
                className="pros review-list"
              />
            </div>
          )}
          {attributes.cons && (
            <div className="review-column">
              <h3 className="review-subTitle">Cons</h3>
              <RichText.Content
                value={attributes.cons}
                tagName="ul"
                multiline="li"
                className="cons review-list"
              />
            </div>
          )}
        </div>
      </div>
    )}
    {attributes.verdict && (
      <>
        <h3
          className={`review-subTitle ${
            !attributes.comparisonProductId ? 'review-subTitle--borderTop' : ''
          }`}
        >
          {__('Our Verdict')}
        </h3>
        <RichText.Content value={attributes.verdict} tagName="p" className="verdict" />
      </>
    )}
  </>
);

export default SaveComponent;
