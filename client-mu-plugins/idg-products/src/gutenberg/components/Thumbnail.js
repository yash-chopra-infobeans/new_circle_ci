import { get, isObject, capitalize, isArray, flatten } from 'lodash-es';

import gets from '../utils/gets';

const { __ } = wp.i18n;
const { TabPanel, ExternalLink, Button } = wp.components;
const { decodeEntities } = wp.htmlEntities;

const parseJSON = string => {
  try {
    return JSON.parse(string);
  } catch (e) {
    return false;
  }
};

const Data = ({ data }) => {
  if (!data) {
    return null;
  }

  if (isArray(data)) {
    return (
      <ul className="productData-list">
        {data.map(item => (
          <li className="productData-arrayItem">
            {isObject(item) ? (
              <Data data={item} />
            ) : (
              <span className="productData-content">{item}</span>
            )}
          </li>
        ))}
      </ul>
    );
  }

  return (
    <ul className="productData-list">
      {Object.keys(data).map(section => {
        const valueIsObject = isObject(data[section]);
        return (
          <li className="productData-item">
            <span className={`${!valueIsObject ? 'productData-key' : 'productData-title'}`}>
              {capitalize(section.replace('_', ' '))}
            </span>
            {valueIsObject ? (
              <Data data={data[section]} />
            ) : (
              <span className="productData-content">{data[section]}</span>
            )}
          </li>
        );
      })}
    </ul>
  );
};

const ProductThumbnail = ({ product, showExtraData = true, selectMethod, isDisabled }) => {
  if (!product) {
    return null;
  }

  const regionInfo = parseJSON(get(product, 'meta.region_info'));
  const globalInfo = parseJSON(get(product, 'meta.global_info'));
  const terms = flatten(get(product, '_embedded.wp:term'));
  const thumbnail = gets(product, [
    '_embedded.wp:featuredmedia.[0]',
    '_embedded.wp:featuredmedia.[0].media_details.sizes.thumbnail',
    '_embedded.wp:featuredmedia.[0].media_details.sizes.medium',
  ]);

  return (
    <>
      <div className="productThumbnail">
        <div className="productThumbnail-media">
          {thumbnail && (
            <img src={thumbnail?.source_url || ''} alt={product?.title?.rendered || ''} />
          )}
        </div>
        <div className="productThumbnail-body">
          <h3 className="productThumbnail-title">
            <ExternalLink href={`/wp-admin/post.php?post=${product.id}&action=edit`}>
              {decodeEntities(product?.title?.rendered || '')}
            </ExternalLink>
          </h3>
          <ul className="productThumbnail-tags">
            {terms.map(term => (
              <span className="productThumbnail-tag">{term.name}</span>
            ))}
          </ul>
        </div>
        <div className="select-btn">
          {showExtraData && (
            <Button isPrimary disabled={isDisabled} onClick={selectMethod}>
              {__('Select')}
            </Button>
          )}
        </div>
      </div>
      {showExtraData && (globalInfo || regionInfo) && (
        <TabPanel
          className="productData-tabs"
          activeClass="productData-activeTab"
          tabs={[
            ...(globalInfo
              ? [
                  {
                    name: 'global',
                    title: __('Global'),
                  },
                ]
              : []),
            ...Object.keys(regionInfo || {}).map(region => ({
              name: region,
              title: region,
            })),
          ]}
        >
          {tab => (
            <div className="productData">
              <Data data={tab.name === 'global' ? globalInfo : regionInfo[tab.name]} />
            </div>
          )}
        </TabPanel>
      )}
    </>
  );
};

export default ProductThumbnail;
