# PostCSS Ms Unit [![Build Status][ci-img]][ci]

[PostCSS] plugin to add modular scale unit.

[PostCSS]: https://github.com/postcss/postcss
[ci-img]:  https://travis-ci.org/jrmd/postcss-ms-unit.svg
[ci]:      https://travis-ci.org/jrmd/postcss-ms-unit

```css
.foo {
    font-size: 1ms;
}
```

```css
.foo {
    font-size: 19.2px;
}
```

## Usage

```js
postcss([ require('postcss-ms-unit') ])
```

You can also declare the ratios in root like so

```css
:root {
    --ms-base: 16;
    --ms-base: 1.2;
}
```

See [PostCSS] docs for examples for your environment.
