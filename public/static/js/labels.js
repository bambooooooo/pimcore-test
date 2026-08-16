document.addEventListener(pimcore.events.postOpenObject, function(e) {

    var labelTemplates = Ext.create('Ext.data.Store', {
        fields: ['id', 'name'],
        autoLoad: false,
        proxy: {
            type: 'ajax',
            url: '/labelsize',
            reader: {
                type: 'json',
                rootProperty: 'data',
            }
        }
    })

    var usersWithTemplate = Ext.create('Ext.data.Store', {
        fields: ['id', 'name'],
        autoLoad: false,
        proxy: {
            type: 'ajax',
            url: '/userlabel',
            reader: {
                type: 'json',
                rootProperty: 'data',
            }
        }
    })

    var packageProducts = Ext.create('Ext.data.Store', {
        fields: ['id', 'name'],
        autoLoad: true,
        proxy: {
            type: 'ajax',
            url: '/packageproducts/' + e.detail.object.id,
            reader: {
                type: 'json',
                rootProperty: 'data',
            }
        }
    })

    var productPackages = Ext.create('Ext.data.Store', {
        fields: ['id', 'name'],
        autoLoad: true,
        proxy: {
            type: 'ajax',
            url: '/productpackages/' + e.detail.object.id,
            reader: {
                type: 'json',
                rootProperty: 'data',
            }
        }
    })

    var orderStore = Ext.create('Ext.data.Store', {
        fields: ['id', 'name'],
        autoLoad: true,
        proxy: {
            type: 'ajax',
            url: '/common-orders',
            reader: {
                type: 'json',
                rootProperty: 'data',
            }
        }
    })

    var labelSize = Ext.create('Ext.form.ComboBox', {
        xtype: 'combo',
        fieldLabel: t('Template'),
        store: labelTemplates,
        displayField: 'name',
        valueField: 'name',
    })

    var userTemplateCombo = Ext.create('Ext.form.ComboBox', {
        xtype: 'combo',
        fieldLabel: t('Recipient'),
        store: usersWithTemplate,
        displayField: 'name',
        valueField: 'id',
    })

    var packageCombo = Ext.create('Ext.form.ComboBox', {
        xtype: 'combo',
        fieldLabel: t('Package'),
        store: productPackages,
        displayField: 'name',
        valueField: 'id',
    })

    var productCombo = Ext.create('Ext.form.ComboBox', {
        xtype: 'combo',
        fieldLabel: t('Product'),
        store: packageProducts,
        displayField: 'name',
        valueField: 'id',
    })

    var commonOrderCombo = Ext.create('Ext.form.ComboBox', {
        xtype: 'combo',
        fieldLabel: t('Order'),
        store: orderStore,
        displayField: 'name',
        valueField: 'id',
    })

    var btn = Ext.create('Ext.Button', {
        xtype: 'button',
        text: t('Show'),
        icon: '/bundles/pimcoreadmin/img/flat-white-icons/seemode.svg',
        handler: function () {

            var packageId = null;
            var productId = null;

            if(!labelSize.value)
            {
                alert(t("Select label size first"))
                return;
            }

            if(e.detail.object.data.general.className === "Package")
            {
                packageId = e.detail.object.id;

                if(!productCombo.value)
                {
                    alert(t("Select product first"));
                    return;
                }

                productId = productCombo.value;
            }

            if(e.detail.object.data.general.className === "Product")
            {
                productId = e.detail.object.id;

                if(!packageCombo.value)
                {
                    alert(t("Select package first"));
                    return;
                }

                packageId = packageCombo.value;
            }

            var url = "/factory/pl/labels/" + packageId
                + "?product_id=" + productId
                + "&size=" + labelSize.value
                + "&copies=1";

            if(userTemplateCombo.value)
            {
                url = url + "&customer_id=" + userTemplateCombo.value
            }

            if(commonOrderCombo.value)
            {
                url = url + "&serie_id=" + commonOrderCombo.value;
            }

            window.open(url);
        }
    });

    var items = [];
    if(e.detail.object.data.general.className === "Package")
    {
        items = [
            productCombo,
            labelSize,
            userTemplateCombo,
            commonOrderCombo,
            btn
        ]
    }
    else if(e.detail.object.data.general.className === "Product")
    {
        items = [
            packageCombo,
            labelSize,
            userTemplateCombo,
            commonOrderCombo,
            btn
        ]
    }

    var panel = Ext.create('Ext.form.Panel', {
        layout: {
            type: 'vbox',
            align: 'stretch',
        },
        defaults: {
            labelWidth: 200
        },
        bodyPadding: 16,
        items: items
    })

    var packageWin = Ext.create('Ext.window.Window', {
            title: t('Package label'),
            items: [
                panel,
            ],
            closeAction: 'hide',
            width: 600,
            layout: 'fit',
            closeable: true,
            modal: true
        })

    if(e.detail.object.data.general.className === "Package" || e.detail.object.data.general.className === "Product" )
    {
        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-color-icons/print.svg',
            scale: 'medium',
            tooltip: t('Package label'),
            handler: function () {
                packageWin.show();
            }
        })
    }
})