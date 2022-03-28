var postcss = require('postcss');

module.exports = postcss.plugin('postcss-ms-unit', function (opts) {
    opts = opts || {
        base: 16,
        ratio: 1.2
    };

    var ms = function (desiredScale) {
        var x = opts.base;
        var shouldDivide = desiredScale < 0 ? true : false;
        var scale = shouldDivide ? desiredScale * -1 : desiredScale;

        for (var i = 0; i < scale; i++) {
            if (shouldDivide) {
                x = x / opts.ratio;
            } else {
                x = x * opts.ratio;
            }
        }

        return parseFloat(x.toFixed(3)) + "px";
    }

    return function (root, result) {

        root.walkRules(':root', (rule) => {
            rule.walkDecls('--ms-base', function (decl, i) {
                opts.base = parseInt(decl.value);
            });

            rule.walkDecls('--ms-ratio', function (decl, i) {
                opts.ratio = parseFloat(decl.value);
            });
        });

        root.walkRules(function (rule) {

            rule.walkDecls('font-size', function (decl, i) {

                if (decl.value.match(/^\-?\d+ms$/)) {
                    var val = parseInt(decl.value);
                    decl.cloneBefore({ prop: 'font-size', value: ms(val) });
                    decl.remove();
                }
            });

        });
    };
});
