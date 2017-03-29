'use strict';

/**
 * Delete product extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form/common/delete',
        'pim/remover/product'
    ],
    function (
        DeleteForm,
        ProductRemover
    ) {
        return DeleteForm.extend({
            remover: ProductRemover,

            /**
             * {@inheritdoc}
             */
            getIdentifier: function () {
                //TODO : to be tested!
                return this.getFormData().meta.id;
            }
        });
    });
