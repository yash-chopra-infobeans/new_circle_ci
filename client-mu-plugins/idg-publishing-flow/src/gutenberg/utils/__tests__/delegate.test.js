import { delegate } from '../delegate';

const ele = {
  selector: '.main',
  matches(selector) {
    return this.selector === selector;
  },
  parentElement: {
    selector: '.parent',
    matches(selector) {
      return this.selector === selector;
    },
    parentElement: false,
  },
};

describe('delegate()', () => {
  it('should return selector.main', () => {
    expect(delegate(ele, '.main')).toEqual(ele);
  });

  it('should return selector.parent', () => {
    expect(delegate(ele, '.parent')).toEqual(ele.parentElement);
  });

  it('should return false', () => {
    expect(delegate(ele, '.doesnt-exist')).toEqual(false);
  });
});
