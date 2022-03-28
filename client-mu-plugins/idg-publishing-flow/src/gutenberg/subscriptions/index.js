/* eslint-disable no-unused-expressions */
import { subscribeForceSchedule } from '../components/Embargo';

const { dispatch, select, subscribe } = wp.data;

subscribe(() => {
  subscribeForceSchedule();

  if (!select('core/editor').isPublishSidebarEnabled()) {
    /**
     * We require forcing this enabled to ensure that the
     * PubFlow pre-publish checks can be performed.
     */
    dispatch('core/editor').enablePublishSidebar();
  }

  if (select('core/editor').isCleanNewPost()) {
    return;
  }

  const publishButton = document.querySelector('button.editor-post-publish-button__button');

  if (!publishButton) {
    return;
  }

  // const publication = select('core/editor').getEditedPostAttribute('publication');

  // publishButton.disabled = publication.length <= 0;

  const currentStatus = select('core/editor').getCurrentPostAttribute('status');
  const updatedStatus = select('core/editor').getEditedPostAttribute('status');

  const checkedStatus = ['publish', 'updated'];

  if (checkedStatus.includes(currentStatus) && !checkedStatus.includes(updatedStatus)) {
    publishButton?.classList?.add('editor-post-publish-button--unpublish');
  } else {
    publishButton?.classList?.remove('editor-post-publish-button--unpublish');
    publishButton?.classList?.remove('editor-post-publish-button--changes');
  }

  const { __idg_publishing_status: publishingStatus = 'draft' } = select(
    'core/editor',
  ).getCurrentPostAttribute('meta');

  if (!publishingStatus) {
    return;
  }

  if (currentStatus !== 'publish' && publishingStatus === 'published') {
    publishButton?.classList?.add('publishing-flow__publish-button--changes');
  }
});
