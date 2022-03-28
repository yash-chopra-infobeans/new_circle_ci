import { CompositeDecorator } from 'draft-js';

import createHandlebarDecorator from './Handlebar';

export default props => new CompositeDecorator([createHandlebarDecorator(props)]);
