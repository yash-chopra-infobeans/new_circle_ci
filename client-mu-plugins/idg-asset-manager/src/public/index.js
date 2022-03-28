import { WINDOW_NAMESPACE } from '../settings';

import TextWithDate from '../gutenberg/components/TextWithDate';
import JWPlayer from '../gutenberg/components/JWPlayer';
import dataToFile from '../gutenberg/utils/dataToFile';

window[WINDOW_NAMESPACE] = {};
window[WINDOW_NAMESPACE].components = {};
window[WINDOW_NAMESPACE].components.TextWithDate = TextWithDate;
window[WINDOW_NAMESPACE].components.JWPlayer = JWPlayer;

window[WINDOW_NAMESPACE].utils = {};
window[WINDOW_NAMESPACE].utils.dataToFile = dataToFile;
