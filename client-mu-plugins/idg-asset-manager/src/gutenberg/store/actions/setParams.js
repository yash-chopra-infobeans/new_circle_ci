const setParams = (state, { value = {} }) => ({
  ...state,
  searchParams: value,
});

export default setParams;
