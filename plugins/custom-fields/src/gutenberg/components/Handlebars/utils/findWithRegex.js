const findWithRegex = (regex, contentBlock, callback) => {
  const text = contentBlock.getText();

  const matches = [...text.matchAll(regex)];

  if (!matches) {
    return;
  }

  matches.forEach(match => {
    callback(match.index, match.index + match[0].length);
  });
};

export default findWithRegex;
