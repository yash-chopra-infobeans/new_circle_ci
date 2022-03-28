/**
 * Import all Gutenberg blocks in this file.
 * Make sure the entry point in `webpack.config.js` for Gutenberg bundle is enabled.
 */

// Import the Gutenberg specific stylesheet.
import '../styles/gutenberg.scss';

// Custom Blocks
import './blocks/card-block';
import './blocks/hero';
import './blocks/article-feed';
import './blocks/tab-navigation';
import './blocks/layout';
import './blocks/jw-player';
import './blocks/review';
import './blocks/price-comparison';
import './blocks/image-pull-quotes';
import './blocks/image';
import './blocks/product-chart';
import './blocks/product-chart/product-chart-item';
import './blocks/product-widget';
import './blocks/toc-heading-filter';
import './blocks/sponsored-embed';

// Custom Dcoument settings
import './document-settings/FeaturedVideo';
import './document-settings/FloatingVideo';
import './document-settings/ContentType';
import './document-settings/SuppressMeta';
import './document-settings/PreventIndex';
import './document-settings/ExternalLink';
