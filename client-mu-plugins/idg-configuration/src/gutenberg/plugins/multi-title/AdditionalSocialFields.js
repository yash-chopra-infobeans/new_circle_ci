const { Component } = wp.element;
const { TextareaControl } = wp.components;

// eslint-disable-next-line react/prefer-stateless-function
class AdditionalSocialFields extends Component {
  render() {
    const { tab, getValue = '', handleChange } = this.props;

    return (
      <div className="additional-fields__container">
        <TextareaControl
          label="Description"
          value={getValue(tab.name, 'social_desc')}
          onChange={handleChange(tab, 'social_desc', true)}
        />
      </div>
    );
  }
}

export default AdditionalSocialFields;
