const { useState } = wp.element;
const { addQueryArgs } = wp.url;

const PATH = '/wp/v2/product';

const DEFAULT_STATE = {
  isCreating: false,
  error: false,
};

const DEFAULT_PARAMS = {
  _embed: 1,
};

const useProductCreation = () => {
  const [state, setState] = useState(DEFAULT_STATE);

  const create = async (data = {}) => {
    setState({ ...state, isCreating: true });

    const response = await wp.apiFetch({
      path: addQueryArgs(PATH, DEFAULT_PARAMS),
      method: 'POST',
      data: {
        ...data,
        content: '<!-- wp:cf/block /-->',
      },
    });

    setState({
      error: false,
      isCreating: false,
    });

    return response;
  };

  return {
    ...state,
    create,
  };
};

export default useProductCreation;
