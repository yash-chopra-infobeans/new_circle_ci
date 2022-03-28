const { __ } = wp.i18n;
const { Button } = wp.components;

const Header = ({ title, isDismissible = true, onClose, closeLabel, children }) => {
  const label = closeLabel || __('Close dialog');

  return (
    <div className="assetManager-header">
      <div className="assetManager-headerContent">
        {title && <h1 className="assetManager-heading">{title}</h1>}
        {children}
      </div>
      {isDismissible && onClose && <Button onClick={onClose} icon="no-alt" label={label} />}
    </div>
  );
};

export default Header;
