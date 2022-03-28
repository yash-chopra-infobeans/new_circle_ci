import { WINDOW_NAMESPACE, NAMESPACE } from '../../settings';
import CustomFields from '../components/CustomFields';
import Plugins from '../components/Plugins';
import useFieldErrors from '../hooks/useFieldErrors';
import useFieldValues from '../hooks/useFieldValues';

const { useEffect } = wp.element;
const { SlotFillProvider, Slot, Spinner, Flex } = wp.components;
const { config, plugins } = window[WINDOW_NAMESPACE];
const { sections, field_groups: fieldGroups } = config;

export default props => {
  const values = useFieldValues();
  const { data: errors } = useFieldErrors();

  useEffect(() => {
    document.body.classList[props.isSelected ? 'add' : 'remove']('cf-block-selected');
  }, [props.isSelected]);

  if (!values.values) {
    return (
      <Flex justify="center" align="center">
        <Spinner />
      </Flex>
    );
  }

  return (
    <SlotFillProvider>
      <Slot name={`${NAMESPACE}/before-fields`} />
      <CustomFields sections={sections} fieldGroups={fieldGroups} values={values} errors={errors} />
      <Slot name={`${NAMESPACE}/after-fields`} />
      <Plugins plugins={plugins} />
    </SlotFillProvider>
  );
};
