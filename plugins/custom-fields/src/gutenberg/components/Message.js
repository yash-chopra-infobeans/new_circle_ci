import PropTypes from 'prop-types';

const { Card, CardBody, Icon } = wp.components;

const Message = ({ icon = 'info', message }) => (
  <Card size="small">
    <CardBody>
      <div className="cf-message">
        <Icon className="cf-messageIcon" icon={icon} size="18" />
        <span className="cf-messageText">{message}</span>
      </div>
    </CardBody>
  </Card>
);

Message.propTypes = {
  icon: PropTypes.string,
  message: PropTypes.string.isRequired,
};

export default Message;
