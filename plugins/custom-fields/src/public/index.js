/* eslint-disable no-console */
import { isFunction } from 'lodash-es';
import { WINDOW_NAMESPACE, NAMESPACE } from '../settings';

import useFieldValues from '../gutenberg/hooks/useFieldValues';
import useFieldErrors from '../gutenberg/hooks/useFieldErrors';

const { applyFilters, doAction } = wp.hooks;

/**
 * Register an item to a registry.
 *
 * @param
 * @param  {object} args
 * @param  {string} args.name- The field type.
 * @param  {function} args.render - A component rendering the field.
 * @return {boolean} - Boolean to determine of the registration was successful.
 */
window[WINDOW_NAMESPACE].register = (registry, { name, render }) => {
  if (typeof name !== 'string') {
    console.error('Name must be a string.');
    return false;
  }

  if (!/^[a-z][a-z0-9-]*$/.test(name)) {
    console.error(
      'Name must include only lowercase alphanumeric characters or dashes, and start with a letter.',
    );

    return false;
  }

  if (!window[WINDOW_NAMESPACE][registry]) {
    console.error(`"${registry}" is not a valid registry.`);
    return false;
  }

  if (window[WINDOW_NAMESPACE][registry][name]) {
    console.error(`"${name}" is already registered.`);
    return false;
  }

  if (!isFunction(render)) {
    console.error(`The "render" property must be specified and must be a valid function.`);
    return false;
  }

  window[WINDOW_NAMESPACE][registry][name] = applyFilters(
    `${NAMESPACE}.register`,
    render,
    registry,
    name,
  );

  doAction(`${NAMESPACE}.registered`, registry, name, render);

  return true;
};

/**
 * Unegister an item from a registry.
 *
 * @param  {string} - The field type.
 * @return {boolean} - Boolean to determine of the unregistration was successful.
 */
window[WINDOW_NAMESPACE].unregister = (registry, name) => {
  if (!window[WINDOW_NAMESPACE][registry]) {
    console.error(`"${registry}" is not a valid registry.`);
    return false;
  }

  if (!window[WINDOW_NAMESPACE][registry][name]) {
    console.error(`${name} is not registered.`);
    return false;
  }

  delete window[WINDOW_NAMESPACE][registry][name];

  doAction(`${NAMESPACE}.unregistered`, registry, name);

  return true;
};

/**
 * Make useFieldValues hook public.
 */
window[WINDOW_NAMESPACE].useFieldValues = useFieldValues;

/**
 * Make useFieldErrors hook public.
 */
window[WINDOW_NAMESPACE].useFieldErrors = useFieldErrors;
