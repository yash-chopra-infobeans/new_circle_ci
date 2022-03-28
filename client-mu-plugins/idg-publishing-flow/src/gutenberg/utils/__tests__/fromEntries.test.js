import fromEntries from '../fromEntries';

describe('fromEntries()', () => {
  it('if available it should use the native solution', () => {
    const fromEntriesSpy = jest.fn();
    const spy = jest.spyOn(Object, 'fromEntries').mockImplementation(fromEntriesSpy);
    fromEntries([]);
    expect(fromEntriesSpy).toHaveBeenCalled();
    spy.mockRestore();
  });

  it('it should return the correct result if the native version is not available', () => {
    const savedFromEntries = Object.fromEntries;
    Object.fromEntries = false;
    const entries = new Map([
      ['foo', 'bar'],
      ['baz', 42],
    ]);
    expect(fromEntries(entries)).toEqual({ foo: 'bar', baz: 42 });
    Object.fromEntries = savedFromEntries;
  });
});
