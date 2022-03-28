const { Button } = wp.components;

/**
 * Post Component.
 *
 * @param {string} postTitle - Current post title.
 * @param {function} clickHandler - this is the handling function for the add/remove function
 * @param {Integer} postId - Current post ID
 * @param {string|boolean} featuredImage - Posts featured image
 * @param icon
 * @returns {*} Post HTML.
 */
export const Post = ({
  title: { rendered: postTitle } = {},
  clickHandler,
  id: postId,
  featured_image: featuredImage = false,
  icon,
  movePost = false,
}) => (
  <article className="post">
    {movePost && (
      <div className="button-directions">
        <Button icon="arrow-up-alt2" onClick={movePost(-1)} />
        <Button icon="arrow-down-alt2" onClick={movePost(1)} />
      </div>
    )}
    <figure className="post-figure" style={{ backgroundImage: `url(${featuredImage})` }} />
    <div className="post-body">
      <span className="post-title">{postTitle}</span>
    </div>
    {icon && (
      <button className="button-action" type="button" onClick={() => clickHandler(postId)}>
        {icon}
      </button>
    )}
  </article>
);

export default Post;
