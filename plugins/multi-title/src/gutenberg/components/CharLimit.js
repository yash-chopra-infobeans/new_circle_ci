export const CharLimit = ({ showCharLimitAt = 0, charLimit, charLimitValue = '' }) => {
  // if no character limit just return
  if (!charLimit) {
    return null;
  }

  /**
   * calculate width the coloured line should be based on value length
   * as a percentage of character limit.
   */
  let width = `${(charLimitValue.length / charLimit) * 100}%`;

  let showChar = showCharLimitAt;
  if (showChar < 0 && charLimit) {
    showChar = charLimit / 2;
  }

  let backgroundColor = '#6c7781';
  // to highlight remaining characters if less than half of the limit
  if (charLimitValue.length >= charLimit / 2) {
    backgroundColor = '#FFB900';
  }

  // warning char limit hit
  if (charLimitValue.length === charLimit) {
    backgroundColor = '#F56E28';
  }

  // potential error beyond char limit
  if (charLimitValue.length > charLimit) {
    backgroundColor = '#DC3232';
    // setting with to 100% so the bar doesn't go beyond the div
    width = '100%';
  }

  return (
    <div className="wrappedInput-charLimit charLimit">
      <div className="charLimit-count" style={{ backgroundColor }}>
        {`${charLimit - charLimitValue.length}`}
      </div>
      <div className="charLimit-bar">
        <div className="charLimit-bar-progress" style={{ backgroundColor, width }} />
      </div>
    </div>
  );
};

export default CharLimit;
