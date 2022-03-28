const { Spinner } = wp.components;

const Placeholder = ({ title = false, text = false, isLoading = false, children }) => (
  <div className="productPlaceholder">
    {isLoading && <Spinner />}
    <div>
      {title && <h2 className="productPlaceholder-title">{title}</h2>}
      {text && <p className="productPlaceholder-text">{text}</p>}
      {children}
    </div>
  </div>
);

export default Placeholder;
