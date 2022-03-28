const { __ } = wp.i18n;

const PostItemAlt = props => {
  const {
    title,
    featuredImage,
    eyebrow,
    eyebrowStyle,
    author,
    id,
    displayEyebrows,
    displayBylines,
  } = props;

  const renderBlock = () => {
    return (
      <article className={`item post-${id}`}>
        <span>
          <div className="item-inner">
            {featuredImage && (
              <div className="item-image">
                <img src={featuredImage} />
              </div>
            )}
            <div className="item-text">
              <div className="item-text-inner">
                {eyebrow && displayEyebrows && (
                  <span className={`item-eyebrow item-eyebrow--${eyebrowStyle}`}>{eyebrow}</span>
                )}
                <h3>{wp.htmlEntities.decodeEntities(title)}</h3>
                {author && displayBylines && (
                  <span className="item-byline">
                    {__('By ', 'idg-base-theme')}
                    {wp.htmlEntities.decodeEntities(author)}
                  </span>
                )}
              </div>
            </div>
          </div>
        </span>
      </article>
    );
  };

  return renderBlock();
};

export default PostItemAlt;
