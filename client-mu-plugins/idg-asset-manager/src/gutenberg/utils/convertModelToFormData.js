// Source: https://gist.github.com/ghinda/8442a57f22099bdb2e34#gistcomment-2719686
const convertModelToFormData = (data = {}, form = null, namespace = '') => {
  const files = {};
  let model = {};

  Object.keys(data).forEach(key => {
    if (data[key] instanceof File) {
      files[key] = data[key];
    } else {
      model[key] = data[key];
    }
  });

  model = JSON.parse(JSON.stringify(model));
  const formData = form || new FormData();

  Object.keys(model).forEach(key => {
    const formKey = namespace ? `${namespace}[${key}]` : key;

    if (model[key] instanceof Date) {
      formData.append(formKey, model[key].toISOString());
    } else if (model[key] instanceof File) {
      formData.append(formKey, model[key]);
    } else if (model[key] instanceof Array) {
      model[key].forEach((element, index) => {
        const tempFormKey = `${formKey}[${index}]`;

        if (typeof element === 'object') {
          convertModelToFormData(element, formData, tempFormKey);
        } else {
          formData.append(tempFormKey, element.toString());
        }
      });
    } else if (typeof model[key] === 'object' && !(model[key] instanceof File)) {
      convertModelToFormData(model[key], formData, formKey);
    } else {
      formData.append(formKey, model[key].toString());
    }
  });

  Object.keys(files).forEach(key => {
    formData.append(key, files[key]);
  });

  return formData;
};

export default convertModelToFormData;
