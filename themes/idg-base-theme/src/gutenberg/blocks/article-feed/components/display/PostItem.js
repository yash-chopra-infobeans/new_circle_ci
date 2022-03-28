const PostItem = props => {
  const {
    title,
    featuredImage,
    eyebrow,
    eyebrowStyle,
    author,
    id,
    displayEyebrows,
    displayExcerpt,
    displayBylines,
    displayDate,
    displayScore,
    excerpt,
    date,
    score,
  } = props;

  return (
    <article className={`item post-${id}`}>
      <span>
        <div className="item-inner">
          <div className="item-image">{featuredImage && <img src={featuredImage} />}</div>
          <div className="item-text">
            <div className="item-text-inner">
              {eyebrow && displayEyebrows && (
                <span className={`item-eyebrow item-eyebrow--${eyebrowStyle}`}>{eyebrow}</span>
              )}
              <h3>{wp.htmlEntities.decodeEntities(title)}</h3>
              {excerpt && displayExcerpt && (
                <span className="item-excerpt">{wp.htmlEntities.decodeEntities(excerpt)}</span>
              )}
              <div className="item-meta">
                {author && displayBylines && (
                  <span className="item-byline">{wp.htmlEntities.decodeEntities(author)}</span>
                )}
                {date && displayDate && <span className="item-date">{date}</span>}
                {score && displayScore && (
                  <span className="item-score">
                    <div
                      className="starRating"
                      style={{ '--rating': score }}
                      aria-label={score}
                    ></div>
                  </span>
                )}
              </div>
            </div>
          </div>
        </div>
      </span>
    </article>
  );
};

export default PostItem;
