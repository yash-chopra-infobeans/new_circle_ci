import { STORE_NAME } from '../../settings';

const { dispatch } = wp.data;
const labelNames = [
  'asset_tag',
  'publication',
  'asset_image_rights',
  'meta.credit',
  'meta.credit_url',
  'meta.image_rights_notes',
];

const autoPopulateMediaAsset = (selectedFile, customKeys) => {
  const ID = selectedFile.id;
  if (customKeys) {
    const data = { ...selectedFile };
    labelNames.forEach(key => {
      if (key.includes('.')) {
        const tempArr = key.split('.');
        const selectedObj = customKeys.filter(ele => ele.key === tempArr[1]);
        if (selectedObj && selectedObj.length > 0) {
          if (!data[tempArr[0]]) {
            data[tempArr[0]] = {};
          }
          data[tempArr[0]][tempArr[1]] = selectedObj[0].value;
        }
      } else {
        const selectedObj = customKeys.filter(ele => ele.key === key);
        if (selectedObj && selectedObj.length > 0) {
          data[key] = selectedObj[0].value;
        }
      }
    });
    if (data) {
      dispatch(STORE_NAME).editFile(data, ID);
    }
  }
};

const setDefaultValue = files => {
  const customKeys = [];
  const firstElement = files[0];
  Object.keys(firstElement).forEach(key => {
    if (typeof firstElement[key] === 'object' && key === 'meta') {
      Object.keys(firstElement[key]).forEach(childKey => {
        if (
          firstElement[key][childKey] &&
          customKeys.filter(ele1 => ele1.key === childKey).length === 0
        ) {
          customKeys.push({ key: childKey, value: firstElement[key][childKey] });
        }
      });
    } else if (firstElement[key] && customKeys.filter(ele1 => ele1.key === key).length === 0) {
      customKeys.push({ key, value: firstElement[key] });
    }
  });
  files.forEach(ele => {
    if (firstElement.id !== ele.id) {
      autoPopulateMediaAsset(ele, customKeys);
    }
  });
};

export default setDefaultValue;
