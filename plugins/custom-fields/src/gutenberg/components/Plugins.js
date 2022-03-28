import { map } from 'lodash-es';

const Plugins = ({ plugins, ...props }) => {
  return map(plugins, Comp => <Comp {...props} />);
};

export default Plugins;
