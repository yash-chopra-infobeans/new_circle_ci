import LinkPopover from './LinkPopover';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { Button } = wp.components;

class DisplayComponent extends Component {
  constructor(...args) {
    super(...args);

    const { value, activeIndex = null } = this.props;

    this.state = {
      value,
      activeIndex,
    };

    this.addItem = this.addItem.bind(this);
  }

  addItem() {
    const {
      attributes: { items = [] },
      setAttributes,
    } = this.props;

    setAttributes({
      items: [
        ...items,
        {
          title: __('New Tab Item', 'idg-base-theme'),
          url: '#',
          opensInNewTab: false,
          makeButton: false,
          id: items.length,
        },
      ],
    });
  }

  updateItem(index, value) {
    const { attributes, setAttributes } = this.props;

    setAttributes({
      items: attributes.items.map((item, i) => (i === index ? { ...item, ...value } : item)),
    });
  }

  setActiveIndex(index = null) {
    this.setState({
      activeIndex: index,
    });
  }

  deleteItem(index) {
    const { attributes, setAttributes } = this.props;

    const reducedArr = [...attributes.items];
    reducedArr.splice(index, 1);
    this.setActiveIndex(null);
    setAttributes({ items: reducedArr });
  }

  moveLinks = (index, dir = 1) => () => {
    const { attributes, setAttributes } = this.props;
    const { items } = attributes;

    const item = items[index];
    const temp = items.filter((value, i) => i !== index);
    const newArray = [...temp.slice(0, index + dir), item, ...temp.slice(index + dir)];
    setAttributes({
      items: newArray,
    });
  };

  render() {
    const { attributes, className, isSelected } = this.props;
    const { activeIndex } = this.state;
    const { items } = attributes;

    const listItems = (items || []).map((item, i) => (
      <div className="item-links-wrapper">
        {isSelected && (
          <div className="move-links-wrapper">
            <button
              onClick={i === 0 ? null : this.moveLinks(i, -1)}
              className={
                i === 0
                  ? 'move-links-disabled  dashicons dashicons-arrow-left-alt2'
                  : 'dashicons dashicons-arrow-left-alt2 move-link-btn move-btn-left-arrow'
              }
            ></button>
            <button
              onClick={this.moveLinks(i, 1)}
              className={
                i === items.length - 1
                  ? 'move-links-disabled dashicons dashicons-arrow-right-alt2'
                  : 'dashicons dashicons-arrow-right-alt2 move-link-btn move-btn-right-arrow'
              }
            ></button>
          </div>
        )}

        <li key={i} className={item.makeButton ? 'item-link make-button' : 'item-link text-link'}>
          <Button className="item-link-btn" onClick={this.setActiveIndex.bind(this, i)}>
            {item.title !== '' ? item.title : <span className="default-link">Add a title</span>}
          </Button>
          {i === activeIndex && (
            <LinkPopover
              value={item}
              onChange={this.updateItem.bind(this, i)}
              onFocusOutside={this.setActiveIndex.bind(this, null)}
              onDelete={() => this.deleteItem(i)}
            />
          )}
        </li>
      </div>
    ));

    return (
      <>
        <div className={className}>
          <ul className="nav-link">{listItems}</ul>
          <Button className="add-link-btn" onClick={this.addItem}>
            <span className="dashicons dashicons-plus-alt2" aria-label="Add link"></span>
          </Button>
        </div>
      </>
    );
  }
}

export default DisplayComponent;
