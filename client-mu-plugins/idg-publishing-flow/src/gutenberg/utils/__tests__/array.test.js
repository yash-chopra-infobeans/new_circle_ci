import { isEmpty, deleteNullValues } from '../array';

describe('isEmpty()', () => {
  it('should return true if an array is empty', () => {
    expect(isEmpty([])).toBeTruthy();
  });

  it('should return false if an array is not empty', () => {
    expect(isEmpty([1, 2, 3, 4])).toBeFalsy();
  });

  it('should return true if an array contains only falsy values', () => {
    expect(isEmpty([false, null])).toBeTruthy();
  });
});

describe('deleteNullValues()', () => {
  it('should delete any null values from an array', () => {
    const arrayWithDeletedNullValues = deleteNullValues([1, 2, 3, null]);
    expect(arrayWithDeletedNullValues[3]).toBeUndefined();
  });
});
