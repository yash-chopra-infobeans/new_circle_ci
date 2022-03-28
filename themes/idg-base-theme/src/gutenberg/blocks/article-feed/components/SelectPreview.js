import PostItem from './display/PostItem';

const { __ } = wp.i18n;

const SelectPreview = ({ loading, posts = [], ...props }) => {
  if (loading) {
    return <p>{__('Loading...', 'idg-base-theme')}</p>;
  }

  if (!posts.length > 0) {
    return <p>{__('No posts selected.', 'idg-base-theme')}</p>;
  }

  return (
    <div className="articleFeed-inner">
      <div className={`articleFeed-inner articleFeed-${posts.length}`}>
        {posts.map(result => (
          <PostItem
            key={`${props.prefix}-${result.id}`}
            {...result}
            displayEyebrows={props.displayEyebrows}
            displayExcerpt={props.displayExcerpt}
            displayBylines={props.displayBylines}
            displayDate={props.displayDate}
          />
        ))}
        {props.displayButton && (
          <div className="articleFeed-button">
            <span aria-label="View More" role="button" className="btn">
              {props.buttonText || __('More stories', 'idg-base-theme')}
            </span>
          </div>
        )}
      </div>
    </div>
  );
};

export default SelectPreview;
