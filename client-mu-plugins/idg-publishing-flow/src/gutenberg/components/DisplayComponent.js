import SaveButton from './SaveButton';
import StatusSelect from './StatusSelect';
import SiteSelect from './SiteSelect';
import HeaderSettings from './HeaderSettings';
import PublishButton from './PublishButton';
import Category from './Category';
import StoryTypeComponent from './StoryType';
import Embargo from './Embargo';
import FeaturedImage from './FeaturedImage';
import PrePublishHeader from './prepublish/PrePublishHeader';
import PreUpdateHeaderComponent from './prepublish/PreUpdateHeader';

const DisplayComponent = () => (
  <>
    <HeaderSettings.Slot />
    <PreUpdateHeaderComponent />
    <PrePublishHeader />
    <SaveButton />
    <Category />
    <StoryTypeComponent />
    <FeaturedImage />
    <Embargo />
    <StatusSelect />
    <SiteSelect />
    <PublishButton />
  </>
);

export default DisplayComponent;
