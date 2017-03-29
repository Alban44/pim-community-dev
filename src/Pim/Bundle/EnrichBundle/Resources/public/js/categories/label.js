'use strict';
/**
 * Title extension for jobs
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'oro/translator',
        'pim/form/common/label',
        'pim/user-context'
    ],
    function (
        __,
        BaseLabel,
        UserContext
    ) {
        return BaseLabel.extend({

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                return BaseLabel.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);

                return BaseLabel.prototype.configure.apply(this, arguments);
            },

            /**
             * Provide the object label
             *
             * @return {String}
             */
            getLabel: function () {
                var labels = this.getFormData().labels || {};
                var prefix = __(this.config.title);
                var currentLocale = UserContext.get('uiLocale');

                return prefix + ' - ' + labels[currentLocale];
            }
        });
    }
);
