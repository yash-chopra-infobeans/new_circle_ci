const loadScript = async (url, isAsync = false) =>
  new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = url;

    if (isAsync) {
      script.async = true;
    }

    script.onload = resolve;
    script.onerror = () => reject(new Error(`${url} failed to load`));
    document.head.appendChild(script);
  });

export default loadScript;
