import { findIndex } from 'lodash-es';
import PropTypes from 'prop-types';

const { createContext } = window.React;
const { Component } = wp.element;

const Context = createContext([]);

class Approval extends Component {
  static contextType = Context;

  static RequiresApprovalString = 'approval';

  static IsBlockedString = 'blocker';

  render() {
    const { name, display, type = 'approval', children } = this.props;

    const item = {
      type,
      name: `${name}-${type}`,
      display,
      children,
    };

    const index = findIndex(this.context, { name: item.name });

    if (index === -1) {
      this.context.push(item);
    } else {
      this.context[index] = item;
    }

    return '';
  }
}

Approval.propTypes = {
  name: PropTypes.string.isRequired,
  display: PropTypes.func.isRequired,
  type: PropTypes.oneOf([Approval.RequiresApprovalString, Approval.IsBlockedString]).isRequired,
};

export default Approval;
