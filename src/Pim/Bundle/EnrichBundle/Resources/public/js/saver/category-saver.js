/**
 * Category saver
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
'use strict';

define([
        'underscore',
        'pim/saver/base',
        'module',
        'routing'
    ], function (
        _,
        BaseSaver,
        module,
        Routing
    ) {
        return _.extend({}, BaseSaver, {
            /**
             * {@inheritdoc}
             */
            getUrl: function (code) {
                if (!code) {
                    return Routing.generate(module.config().postUrl);
                }

                return Routing.generate(module.config().putUrl, {code: code});
            }
        });
    }
);
