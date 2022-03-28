import className from 'classnames';

const { BaseControl } = wp.components;

const FormField = ({
  id,
  label = '',
  help = '',
  errors = [],
  children,
  fieldClass = '',
  isRequired = false,
}) => {
  const classes = className('assetManager-field', fieldClass, {
    'assetManager-field--hasErrors': errors.length > 0,
    'assetManager-field--requiredField': isRequired,
  });

  return (
    <BaseControl className={classes} id={id} label={label} help={help} hideLabelFromVision>
      <BaseControl.VisualLabel>
        {label} {isRequired && <span className="isRequired-field">*</span>}
      </BaseControl.VisualLabel>
      {errors.length > 0 && (
        <ul className="assetManager-fieldErrors">
          {errors.map(error => (
            <li>{error}</li>
          ))}
        </ul>
      )}
      <div className="assetManager-fieldInput">{children}</div>
    </BaseControl>
  );
};

export default FormField;
