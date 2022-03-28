import usePrevious from '../../utils/usePrevious';
import getMedia from '../../api/getMedia';

const { createHigherOrderComponent } = wp.compose;

const CoreImage = createHigherOrderComponent(
  BlockEdit => props => {
    const { name, attributes, setAttributes } = props;

    if (name !== 'core/image') {
      return <BlockEdit {...props} />;
    }

    const prevAttachmentId = usePrevious(attributes?.id || 0);

    if (attributes?.id && prevAttachmentId !== attributes.id) {
      getMedia(attributes.id).then(repsponse => {
        setAttributes({
          credit: repsponse?.data?.meta?.credit || '',
          creditUrl: repsponse?.data?.meta?.credit_url || '',
        });
      });
    }

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

    const { align } = attributes;
    const alignment = align === 'undefined' ? 'left' : align;

    return (
      <div className={`extendedBlock-wrapper block-coreImage ${alignment}`}>
        <BlockEdit {...props} />
        {getCredit()}
      </div>
    );
  },
  'CoreImage',
);

export default CoreImage;
