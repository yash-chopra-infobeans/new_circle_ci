import findWithRegex from '../utils/findWithRegex';

const Handlebar = props => {
  return <span className="cf-handlebars-handlebar">{props.children}</span>;
};

export default ({ handlebars }) => ({
  strategy: (contentBlock, callback) => {
    findWithRegex(new RegExp(`{{(${handlebars.join('|')})}}`, 'g'), contentBlock, callback);
  },
  component: Handlebar,
});
