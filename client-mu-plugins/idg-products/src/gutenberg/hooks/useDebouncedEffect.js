const { useEffect, useRef } = wp.element;

const useDebouncedEffect = (callback, delay, deps = []) => {
  const firstUpdate = useRef(true);

  useEffect(() => {
    if (firstUpdate.current) {
      callback();
      firstUpdate.current = false;
      return;
    }

    const handler = setTimeout(() => {
      callback();
    }, delay);

    // eslint-disable-next-line consistent-return
    return () => {
      clearTimeout(handler);
    };
  }, [delay, ...deps]);
};

export default useDebouncedEffect;
