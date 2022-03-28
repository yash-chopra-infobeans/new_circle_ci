import { HashRouter, Switch, Route } from 'react-router-dom';

import '../styles/admin.scss';
import './store';
import Manager from './pages/AssetManager';
import File from './pages/File';
import UploadPage from './pages/UploadPage';

const { render } = wp.element;
const { SlotFillProvider } = wp.components;

const App = () => (
  <HashRouter>
    <Switch>
      <SlotFillProvider>
        <Route exact path="/" component={Manager} />
        <Route exact path="/upload" component={UploadPage} />
        <Route exact path="/files/:assetId" component={File} />
        <Route exact path="/files" component={File} />
      </SlotFillProvider>
    </Switch>
  </HashRouter>
);

render(<App />, document.getElementById('assetManager-container'));
