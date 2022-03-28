const { __ } = wp.i18n;

const PostItem = props => {
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

  const backgroundStyle = {
    backgroundImage: `url(${featuredImage})`,
  };

  const renderBlock = () => {
    return (
      <article className={`item post-${id}`}>
        <span>
          <div className="item-inner" style={backgroundStyle}>
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

export default PostItem;
