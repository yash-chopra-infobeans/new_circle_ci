import Button from '../Button';
import insertHandlebarVariable from '../modifiers/insertHandlebarVariable';

const Toolbar = ({ editorState, onChange, handlebars = [] }) => {
  return (
    <div className="cf-handlebars-toolbar">
      {handlebars.map(variable => (
        <Button onPress={() => onChange(insertHandlebarVariable(editorState, variable))}>
          {variable}
        </Button>
      ))}
    </div>
  );
};

export default Toolbar;
