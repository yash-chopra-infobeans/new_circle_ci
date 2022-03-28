import '../styles/gutenberg.scss';
import '../gutenberg';
import Input from '../gutenberg/components/WrappedInput';

if (!window.MultiTitle) {
  window.MultiTitle = {};
}

window.MultiTitle.WrappedInput = Input;
