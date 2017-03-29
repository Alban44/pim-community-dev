'use strict';
/**
 * Categories edit form
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
        'backbone',
        'pim/form',
        'text!pim/template/categories/edit-form',
        'pim/tree/manage',
        'routing',
        'jquery.sidebarize'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template,
        TreeManage,
        Routing
    ) {
        return BaseForm.extend({
            template: _.template(template),
            treeManage: null,

            /**
             * Callback called by the treeManager
             * @param data
             * @param type
             */
            callbackCurrentCategoryUpdated: function (data) {
                this.setData(data);
                this.extensions['pim-categories-edit-form-right-panel'].render();
            },

            /**
             * Render the tree of categories using TreeManage
             * @param route
             */
            renderCategoriesTree: function (route) {
                //TODO ALBAN: manage ACL and route and _categorytree_create and data-editable in html template!
                var buttons = [];
                buttons.push($('<a>', {
                    'class': 'no-hash',
                    'data-toggle': 'tooltip',
                    'data-placement': 'right',
                    'data-original-title': __('pim_enrich.form.categories.btn.create.tree')
                }).html(
                    $('<i>', {'class': 'icon-plus-sign'})
                ).on('click', function () {
                    var url = Routing.generate(route + '_categorytree_create');
                    Backbone.history.navigate('url=' + url, {trigger: true});
                }).tooltip());

                // Instantiate sidebar
                $('#category-tree-container').sidebarize({buttons: buttons});
                this.treeManage = new TreeManage('#tree', route, this.callbackCurrentCategoryUpdated.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));
                this.renderCategoriesTree('pim_enrich');

                return BaseForm.prototype.render.apply(this, arguments);
            }
        });
    }
);
