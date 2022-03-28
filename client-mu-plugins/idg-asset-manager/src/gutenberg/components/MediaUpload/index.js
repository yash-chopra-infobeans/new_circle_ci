import { noop } from 'lodash-es';

import '../../store';
import { STORE_NAME, I18N_DOMAIN } from '../../../settings';
import Modal from './Modal';

const { __ } = wp.i18n;
const { Button } = wp.components;
const { useState, useEffect } = wp.element;
const { dispatch } = wp.data;

// WordPress core reference: https://github.com/WordPress/gutenberg/blob/9441c16de761212b959897b6e4d09f011f97d597/packages/media-utils/src/components/media-upload/index.js
const MediaUpload = ({
  label = __('Asset Manager', I18N_DOMAIN),
  isTertiary,
  isSecondary,
  isPrimary,
  displayUpload = false,
  render,
  ...props
}) => {
  const [modal, toggleModal] = useState(false);
  const [renderComp, setRenderComp] = useState(null);

  const onClick = id => {
    if (id && !modal) {
      dispatch(STORE_NAME).selectFile(id);
    } else {
      dispatch(STORE_NAME).clearSelectedFile();
    }

    toggleModal(!modal);
  };

  /**
   * Below is a work around to prevent an infinite loop which was occuring when inserting an inline image.
   *
   * The inline image render prop calls the open prop and returns null, calling the open prop causes this component to
   * re-render which in turn calls the render prop again which causes the infinite loop.
   *
   * Inline image reference: https://github.com/WordPress/gutenberg/blob/34ed2a6042d42fa18a5dcd0853d59bdff6a068d9/packages/format-library/src/image/index.js#L161
   *
   * You can see that the featured image render prop actually returns something to render and doesn't just call open and
   * return null.
   *
   * Featured image reference: https://github.com/WordPress/gutenberg/blob/470b41522acac1dde125f240836db144d5e52c8c/packages/editor/src/components/post-featured-image/index.js#L130
   */
  useEffect(() => {
    const testRender = render ? render({ open: noop }) : null;

    if (render && testRender) {
      setRenderComp(true);
    } else if (render && !testRender) {
      onClick();
    }
  }, []);

  return (
    <>
      {renderComp && render && render({ open: onClick, isOpen: modal })}
      {!renderComp && (
        <Button
          isPrimary={isPrimary}
          isSecondary={isSecondary}
          isTertiary={isTertiary}
          onClick={onClick}
        >
          {label}
        </Button>
      )}
      {modal && <Modal {...props} onClose={onClick} displayUpload={displayUpload} />}
    </>
  );
};

export default MediaUpload;
