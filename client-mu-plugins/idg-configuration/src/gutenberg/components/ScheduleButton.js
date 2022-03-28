import { I18N_DOMAIN } from '../settings';

const { __ } = wp.i18n;
const { select, subscribe } = wp.data;

function showHintMessage() {
  const isBeingSchedule = select('core/editor').isEditedPostBeingScheduled();
  const node = document.createElement('div');
  node.setAttribute('id', 'hintMessage');
  node.setAttribute('class', 'components-panel__row edit-post-post-schedule-hint');
  const textnode = document.createTextNode(
    __('Note: this post is scheduled to be published or updated in the future', I18N_DOMAIN),
  );
  node.appendChild(textnode);
  const hintDivExist = document.getElementById('hintMessage');
  const postSchedule = document.querySelector('.edit-post-post-schedule');
  if (postSchedule) {
    if (isBeingSchedule && (typeof hintDivExist === 'undefined' || hintDivExist === null)) {
      postSchedule.appendChild(node);
    } else if (typeof hintDivExist !== 'undefined' && hintDivExist !== null && !isBeingSchedule) {
      const list = document.getElementById('hintMessage');
      postSchedule.removeChild(list);
    }
  }
}

subscribe(() => {
  const postSchedule = document.querySelector('.edit-post-post-schedule');
  if (postSchedule) {
    showHintMessage();
  } else {
    setTimeout(() => {
      showHintMessage();
    }, 0);
  }
});
