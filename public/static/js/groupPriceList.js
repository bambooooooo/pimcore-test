document.addEventListener(pimcore.events.postOpenObject, function(e){
    if(e.detail.object.data.general.className === "Group")
    {
        const prods = e.detail.object.data.data.Products.length + e.detail.object.data.data.Sets.length;

        var priceLevels = Ext.create('Ext.data.Store', {
            fields: ['name', 'title'],
            loading: true,
            proxy: {
                type: 'ajax',
                url: '/price-levels',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                }
            },
        })

        var unpublished = Ext.create('Ext.form.Checkbox', {name: 'show_unpublished', fieldLabel: t('Show unpublished')});
        var showGroupProducts = Ext.create('Ext.form.Checkbox', {name: 'show_products', fieldLabel: t('Show products'), checked: true});
        var showGroupSets = Ext.create('Ext.form.Checkbox', {name: 'show_sets', fieldLabel: t('Show sets'), checked: true});
        var showGroupRelatedProducts = Ext.create('Ext.form.Checkbox', {name: 'show_relatedproducts', fieldLabel: t('Show related products'), checked: true});
        var showProductTypeSku = Ext.create('Ext.form.Checkbox', {name: 'show_products_sku', fieldLabel: t('Show products with type SKU'), checked: false});
        var showPrices = Ext.create('Ext.form.Checkbox', {name: 'show_prices', fieldLabel: t('Show prices'), checked: true});
        var showSummaryGrid = Ext.create('Ext.form.Checkbox', {name: 'show_summary_grid', fieldLabel: t('Show summary grid'), checked: true});
        var showProductStocks = Ext.create('Ext.form.Checkbox', {name: 'show_products_stocks', fieldLabel: t('Show products stocks'), checked: false});

        var priceLevelSelector = Ext.create('Ext.form.ComboBox', {
            xtype: 'combo',
            fieldLabel: t('Select price level'),
            store: priceLevels,
            displayField: 'title',
            valueField: 'name'
        });

        function getBasePath()
        {
            return "/object/" + pimcore.settings.language + "/" + e.detail.object.id + "/price-list?" +
                "show_unpublished=" + unpublished.value +
                "&show_products=" + showGroupProducts.value +
                "&show_sets=" + showGroupSets.value +
                "&show_related_products=" + showGroupRelatedProducts.value +
                "&show_products_type_sku=" + showProductTypeSku.value +
                "&show_summary_grid=" + showSummaryGrid.value +
                "&show_product_stocks=" + showProductStocks.value +
                "&show_prices=" + showPrices.value;
        }

        var btnPdf = Ext.create('Ext.Button', {
            xtype: 'button',
            text: t('Price list PDF'),
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            handler: function(){
                if(!priceLevelSelector.value)
                {
                    alert("Please specify price level");
                    return;
                }

                const path = getBasePath() + "&price_level=" + priceLevelSelector.value;
                window.open(path);
            }
        });

        var btnPdfSpec = Ext.create('Ext.Button', {
            xtype: 'button',
            text: t('Datasheets PDF'),
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            handler: function(){
                if(!priceLevelSelector.value)
                {
                    const path = getBasePath() + "&type=pdf-spec";
                    window.open(path);
                }
                else
                {
                    const path = getBasePath() +  "&type=pdf-spec&price_level=" + priceLevelSelector.value;
                    window.open(path);
                }
            }
        });

        var btnXlsx = Ext.create('Ext.Button', {
            xtype: 'button',
            text: t('Price list XLSX'),
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            handler: function(){
                if(!priceLevelSelector.value)
                {
                    alert(t("Please specify offer first"));
                    return;
                }

                const path = getBasePath() + "&price_level=" + priceLevelSelector.value + "&type=xlsx";

                window.open(path);
            }
        });

        var panel = Ext.create('Ext.form.Panel', {
            layout: {
                type: 'vbox',
                align: 'stretch',
            },
            defaults: {
                labelWidth: 200
            },
            bodyPadding: 16,
            items: [
                unpublished,
                showGroupProducts,
                showGroupSets,
                showGroupRelatedProducts,
                showProductTypeSku,
                showSummaryGrid,
                showProductStocks,
                showPrices,
                priceLevelSelector,
                {
                    xtype: 'splitter'
                },
                Ext.create('Ext.form.Panel', {
                    layout: {
                        type: 'hbox',
                        align: 'right',
                        buttonAlign: 'right'
                    },
                    items:
                        [
                            btnPdf,
                            { xtype: 'splitter'},
                            btnXlsx,
                            { xtype: 'splitter'},
                            btnPdfSpec
                        ]
                })
            ]
        })

        var win = Ext.create('Ext.window.Window', {
            title: t('Price list for') + ' ' + e.detail.object.data.general.key,
            items: [
                panel,
            ],
            closeAction: 'hide',
            width: 600,
            layout: 'fit',
            closeable: true,
            modal: true
        })

        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            scale: 'medium',
            tooltip: t('Download'),
            menu: [
                {
                    text: t('Product(Set) images'),
                    tooltip: t('Download all images from assigned Products and ProductSets as zip archive'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
                    scale: 'medium',
                    handler: function () {

                        if(prods > 0)
                        {
                            const path = "/export/images/" + e.detail.object.id;
                            window.open(path);
                        }
                        else
                        {
                            Ext.Msg.alert(t('Warning'), t('Group has no Products and Sets!'));
                        }
                    }
                },
                {
                    text: t('Pricelist'),
                    tooltip: t('Download pricelist in PDF'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/percent.svg',
                    scale: 'medium',
                    handler: function () {
                        win.show();
                    }
                }
            ]
        })
    }
})