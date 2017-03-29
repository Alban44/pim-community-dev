'use strict';

/**
 * Save extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/save',
        'pim/saver/category'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        CategorySaver
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.product.info.update_successful'),
            updateFailureMessage: __('pim_enrich.entity.product.info.update_failed'),

            /**
             * {@inheritdoc}
             */
            save: function (options) {
                var category = $.extend(true, {}, this.getFormData());
                var code = null;
                var isUpdate = false;
                var method = 'POST';

                if (_.has(category.meta, 'id')) {
                    code = category.code;
                    isUpdate = true;
                    method = 'PUT';
                }

                delete category.id;
                delete category.meta;

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return CategorySaver
                    .save(code, category, method)
                    .then(function (data) {
                        this.postSave();

                        this.setData(data, options);

                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            }
        });
    }
);
