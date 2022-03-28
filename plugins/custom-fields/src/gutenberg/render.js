import Editor from './components/Editor';
import Header from './components/Header';

const { render } = wp.element;

const RENDER_ID = 'cf-fields';

wp.domReady(() => {
  const renderElement = document.getElementById(RENDER_ID);

  if (!renderElement) {
    return;
  }

  render(
    <>
      <Header />
      <Editor />
    </>,
    renderElement,
  );
});
