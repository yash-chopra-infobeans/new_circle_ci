import { Post } from './Post';

const { __ } = wp.i18n;

/**
 * PostList Component
 * @param object props - Component props.
 * @returns {*}
 * @constructor
 */
export const PostList = props => {
  const {
    filtered = false,
    loading = false,
    posts = [],
    action = () => {},
    icon = null,
    canPaginate,
    doPagination,
    paging,
    movePost = () => {},
  } = props;

  if (loading) {
    return <p>{__('Loading Content...', 'idg-base-theme')}</p>;
  }

  if (filtered && posts.length < 1) {
    return (
      <div className="post-list">
        <p>{__('Your query yielded no results, please try again.', 'idg-base-theme')}</p>
      </div>
    );
  }

  if (!posts || posts.length < 1) {
    return <p>{__('No Content.', 'idg-base-theme')}</p>;
  }

  return (
    <div className="post-list">
      {posts.map((post, index) => (
        <Post
          key={post.id}
          {...post}
          clickHandler={action}
          icon={icon}
          movePost={movePost(index) || false}
        />
      ))}
      {/* eslint-disable-next-line react/jsx-handler-names */}
      {canPaginate ? (
        <button type="button" onClick={doPagination} disabled={paging}>
          {paging ? __('Loading...', 'idg-base-theme') : __('Load More', 'idg-base-theme')}
        </button>
      ) : null}
    </div>
  );
};

export default PostList;
