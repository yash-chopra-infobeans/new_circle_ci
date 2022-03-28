export function flatToTree(array) {
  const roots = [];
  const map = {};
  let node;
  let i;

  for (i = 0; i < array.length; i += 1) {
    map[array[i].id] = i;
    array[i].children = []; // eslint-disable-line no-param-reassign
  }

  for (i = 0; i < array.length; i += 1) {
    node = array[i];

    if (node.parent !== 0) {
      array[map[node.parent]].children.push(node);
    } else {
      roots.push(node);
    }
  }

  return roots;
}

export default flatToTree;
