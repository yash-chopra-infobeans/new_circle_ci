const Save = (element, blockType, attributes) => {
  const getCredit = () => {
    if (!attributes?.credit) {
      return null;
    }

    if (attributes?.creditUrl) {
      return (
        <a href={attributes.creditUrl} target="_blank" className="imageCredit">
          {attributes.credit}
        </a>
      );
    }

    return <p className="imageCredit">{attributes.credit}</p>;
  };

  if (!element) {
    return null;
  }

  const { align } = attributes;
  const alignment = align === 'undefined' ? 'left' : align;

  if (blockType.name === 'core/image') {
    return (
      <div className={`extendedBlock-wrapper block-coreImage ${alignment}`}>
        {element}
        {getCredit()}
      </div>
    );
  }

  return element;
};

export default Save;
