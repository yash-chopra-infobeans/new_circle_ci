import '../styles/admin.scss';
import Form from './form';

const { render } = wp.element;

const App = () => <Form />;

render(<App />, document.getElementById('idg-post-filters'));
