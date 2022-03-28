import PropTypes from 'prop-types';

const { TabPanel } = wp.components;

const Tabs = ({ tabs, children }) => {
  if (!tabs || tabs?.length === 0) {
    return children(false);
  }

  return (
    <TabPanel className="cf-tabs" tabs={tabs}>
      {tab => children(tab)}
    </TabPanel>
  );
};

Tabs.propTypes = {
  tabs: PropTypes.array.isRequired,
  children: PropTypes.func.isRequired,
};

export default Tabs;
