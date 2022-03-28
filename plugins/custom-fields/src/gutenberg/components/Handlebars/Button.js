const Button = ({ onPress, active, children }) => {
  const onMouseDown = e => {
    e.preventDefault();
    onPress();
  };

  const className = !active
    ? 'cf-handlebars-button'
    : 'cf-handlebars-button cf-handlebars-activeButton';

  return (
    <span className={className} onMouseDown={onMouseDown}>
      {children}
    </span>
  );
};

export default Button;
