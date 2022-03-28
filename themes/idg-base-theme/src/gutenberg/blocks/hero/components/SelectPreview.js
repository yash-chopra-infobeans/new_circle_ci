import PostItem from './display/PostItem';
import PostItemAlt from './display/PostItemAlt';

const { __ } = wp.i18n;

const SelectPreview = ({ loading, posts = [], ...props }) => {
  if (loading) {
    return <p>{__('Loading...', 'idg-base-theme')}</p>;
  }

  if (!posts.length > 0) {
    return <p>{__('No posts selected.', 'idg-base-theme')}</p>;
  }

  return (
    <div className="hero-inner">
      {posts.length === 4 || posts.length === 3 ? (
        <div className={`hero-inner hero-${posts.length}`}>
          <div className="hero-col hero-col-1">
            {posts.slice(0, 1).map(result => (
              <PostItem
                key={`${props.prefix}-${result.id}`}
                {...result}
                displayEyebrows={props.displayEyebrows}
                displayBylines={props.displayBylines}
              />
            ))}
          </div>
          <div className={`hero-col hero-col-${posts.length - 1}`}>
            {posts.length === 4
              ? posts
                  .slice(1, posts.length)
                  .map(result => (
                    <PostItemAlt
                      key={`${props.prefix}-${result.id}`}
                      {...result}
                      displayEyebrows={props.displayEyebrows}
                      displayBylines={props.displayBylines}
                    />
                  ))
              : posts
                  .slice(1, posts.length)
                  .map(result => (
                    <PostItem
                      key={`${props.prefix}-${result.id}`}
                      {...result}
                      displayEyebrows={props.displayEyebrows}
                      displayBylines={props.displayBylines}
                    />
                  ))}
          </div>
        </div>
      ) : (
        <div className={`hero-inner hero-${posts.length}`}>
          {posts.map(result => (
            <PostItem
              key={`${props.prefix}-${result.id}`}
              {...result}
              displayEyebrows={props.displayEyebrows}
              displayBylines={props.displayBylines}
            />
          ))}
        </div>
      )}
    </div>
  );
};

export default SelectPreview;
