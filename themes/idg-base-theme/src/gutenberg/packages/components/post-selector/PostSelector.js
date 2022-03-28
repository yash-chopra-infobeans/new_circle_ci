import { PostList } from './PostList';

const { __ } = wp.i18n;
const { BlockIcon } = wp.editor;

const PostSelector = props => {
  const {
    state,
    getSelectedPosts,
    onInputFilterChange,
    onPostTypeChange,
    addPost,
    removePost,
    doPagination,
    movePost,
  } = props;
  const {
    filtering,
    filterLoading,
    filterPosts,
    posts,
    type,
    types,
    filter,
    pages,
    pagesTotal,
    initialLoading,
    loading,
    paging,
  } = state;

  const isFiltered = filtering;
  let postList =
    isFiltered && !filterLoading ? filterPosts : posts.filter(post => post.type === type);
  const pageKey = filter ? 'filter' : type;
  const canPaginate = (pages[pageKey] || 1) < pagesTotal[pageKey];
  const selectedPosts = getSelectedPosts();

  // Removes selected posts from post list.
  postList = postList.filter(val => !selectedPosts.includes(val));

  const addIcon = <BlockIcon icon="plus" />;
  const removeIcon = <BlockIcon icon="minus" />;

  return (
    <div className="wp-block-bigbite-postlist">
      <div className="post-selector">
        <div className="post-selectorHeader">
          <div className="searchbox">
            <label htmlFor="searchinput">
              <BlockIcon icon="search" />
              <input
                id="searchinput"
                type="search"
                placeholder={__('Please enter your search query...', 'idg-base-theme')}
                value={filter}
                onChange={onInputFilterChange}
              />
            </label>
          </div>
          <div className="filter">
            {/* eslint-disable-line react/jsx-one-expression-per-line */}
            <label htmlFor="options">{__('Content Type:', 'idg-base-theme')}</label>
            <select name="options" id="options" onChange={onPostTypeChange}>
              {types.length < 1 ? (
                <option value="">{__('Loading...', 'idg-base-theme')}</option>
              ) : (
                Object.keys(types).map(key => (
                  <option key={key} value={key}>
                    {types[key].name}
                  </option>
                ))
              )}
            </select>
          </div>
        </div>
        <div className="post-selectorContainer">
          <div className="post-selectorAdd">
            <PostList
              posts={postList}
              loading={initialLoading || loading || filterLoading}
              filtered={isFiltered}
              action={addPost}
              paging={paging}
              canPaginate={canPaginate}
              doPagination={doPagination}
              icon={addIcon}
            />
          </div>
          <div className="post-selectorRemove">
            <PostList
              posts={selectedPosts}
              loading={initialLoading}
              action={removePost}
              icon={removeIcon}
              movePost={movePost}
            />
          </div>
        </div>
      </div>
    </div>
  );
};

export default PostSelector;
