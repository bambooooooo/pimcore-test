
document.addEventListener(pimcore.events.postOpenObject, function(e){

    if(e.detail.object.data.general.className === "Product" || e.detail.object.data.general.className === "ProductSet")
    {
        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            scale: 'medium',
            tooltip: 'Download',
            menu: [
                {
                    text: t('Images'),
                    tooltip: t('Download all images as zip archive'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/gallery.svg',
                    scale: 'medium',
                    handler: function () {
                        const path = "/export/images/" + e.detail.object.id;
                        window.open(path);
                    }
                }
            ]
        });

        let menu = [
            {
                text: t('EAN13 (GTIN)'),
                icon: '/UI/barcode_white.svg',
                scale: 'medium',
                tooltip: t('Assign EAN from first available EAN Pool'),
                handler: function () {

                    if(e.detail.object.data.data.Ean != null && e.detail.object.data.data.Ean.length > 12)
                    {
                        Ext.Msg.alert('Warning', 'EAN already exists!');
                        return;
                    }

                    if(e.detail.object.data.data.Name != null && e.detail.object.data.Name.length > 1)
                    {
                        Ext.Msg.alert('Warning', 'Product has no PL name');
                        return;
                    }

                    console.log("Add EAN to #" + e.detail.object.id);

                    Ext.Ajax.request({
                        url: "/object/add-ean",
                        method: "POST",
                        params: {
                            'id': e.detail.object.id
                        },
                        success: function (data) {
                            console.log("Success!");

                            const obj = JSON.parse(data.responseText);

                            const remaining_threshold = 200;

                            if(obj.remaining < remaining_threshold)
                            {
                                pimcore.helpers.showNotification(t('Warning'), t('Less than ' + remaining_threshold + ' GTINs left.') + ": " + obj.remaining, "warn");
                            }

                            e.detail.object.reload();
                        },
                        failure: function (error) {
                            console.log("Error!");
                            console.log(error.responseText);
                        },
                    })
                }
            }
        ]

        if(e.detail.object.data.general.className === "Product")
        {
            menu.push({
                text: t('Base price from package'),
                icon: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjQgMjQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yMS43LDE0LjdMMTkuNCwxM2wwLjEtMWwtMC4xLTFsMi4xLTEuNmMwLjItMC4xLDAuMy0wLjQsMC4xLTAuNmwtMi0zLjVDMTkuNSw1LDE5LjMsNSwxOSw1bC0yLjUsMQ0KCWMtMC41LTAuNC0xLjEtMC43LTEuNy0xbC0wLjQtMi43QzE0LjUsMi4yLDE0LjMsMiwxNCwyaC00QzkuOCwyLDkuNSwyLjIsOS41LDIuNEw5LjEsNS4xQzguNSw1LjMsOCw1LjcsNy40LDZMNSw1DQoJQzQuNyw1LDQuNSw1LDQuMyw1LjNsLTIsMy41QzIuMiw5LDIuMyw5LjIsMi41LDkuNEw0LjYsMTFsLTAuMSwxbDAuMSwxbC0yLjEsMS43Yy0wLjIsMC4yLTAuMywwLjQtMC4xLDAuNmwyLDMuNQ0KCUM0LjUsMTksNC43LDE5LDUsMTlsMi41LTFjMC41LDAuNCwxLjEsMC43LDEuNywxbDAuNCwyLjdjMCwwLjIsMC4zLDAuNCwwLjUsMC40aDRjMC4zLDAsMC41LTAuMiwwLjUtMC40TDE1LDE5DQoJYzAuNi0wLjMsMS4yLTAuNiwxLjctMWwyLjUsMWMwLjIsMC4xLDAuNSwwLDAuNi0wLjJsMi0zLjVDMjEuOSwxNS4xLDIxLjksMTQuOCwyMS43LDE0Ljd6IE0xMiwxOXYtMmMtMi44LDAtNS0yLjItNS01DQoJYzAtMC45LDAuMi0xLjcsMC42LTIuNGwxLjUsMS41QzkuMSwxMS40LDksMTEuNyw5LDEyYzAsMS43LDEuMywzLDMsM3YtMmwzLDNMMTIsMTl6IE0xNi40LDE0LjRsLTEuNS0xLjVDMTUsMTIuNiwxNSwxMi4zLDE1LDEyDQoJYzAtMS43LTEuMy0zLTMtM3YyTDksOGwzLTN2MmMyLjgsMCw1LDIuMiw1LDVDMTcsMTIuOSwxNi44LDEzLjcsMTYuNCwxNC40eiIvPg0KPC9zdmc+DQo=',
                scale: 'medium',
                tooltip: t('Move base price as sum of packages prices'),
                handler: function () {

                    Ext.Ajax.request({
                        url: "/object/base-price",
                        method: "POST",
                        params: {
                            'id': e.detail.object.id
                        },
                        success: function (data) {
                            console.log("Success!");
                            console.log(data.responseText);

                            const obj = JSON.parse(data.responseText);

                            e.detail.object.reload();
                        },
                        failure: function (error) {
                            console.log("Error!");
                            console.log(error.responseText);
                        },
                    })
                }
            })
        }

        e.detail.object.toolbar.add({
            icon: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjQgMjQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yMS43LDE0LjdMMTkuNCwxM2wwLjEtMWwtMC4xLTFsMi4xLTEuNmMwLjItMC4xLDAuMy0wLjQsMC4xLTAuNmwtMi0zLjVDMTkuNSw1LDE5LjMsNSwxOSw1bC0yLjUsMQ0KCWMtMC41LTAuNC0xLjEtMC43LTEuNy0xbC0wLjQtMi43QzE0LjUsMi4yLDE0LjMsMiwxNCwyaC00QzkuOCwyLDkuNSwyLjIsOS41LDIuNEw5LjEsNS4xQzguNSw1LjMsOCw1LjcsNy40LDZMNSw1DQoJQzQuNyw1LDQuNSw1LDQuMyw1LjNsLTIsMy41QzIuMiw5LDIuMyw5LjIsMi41LDkuNEw0LjYsMTFsLTAuMSwxbDAuMSwxbC0yLjEsMS43Yy0wLjIsMC4yLTAuMywwLjQtMC4xLDAuNmwyLDMuNQ0KCUM0LjUsMTksNC43LDE5LDUsMTlsMi41LTFjMC41LDAuNCwxLjEsMC43LDEuNywxbDAuNCwyLjdjMCwwLjIsMC4zLDAuNCwwLjUsMC40aDRjMC4zLDAsMC41LTAuMiwwLjUtMC40TDE1LDE5DQoJYzAuNi0wLjMsMS4yLTAuNiwxLjctMWwyLjUsMWMwLjIsMC4xLDAuNSwwLDAuNi0wLjJsMi0zLjVDMjEuOSwxNS4xLDIxLjksMTQuOCwyMS43LDE0Ljd6IE0xMiwxOXYtMmMtMi44LDAtNS0yLjItNS01DQoJYzAtMC45LDAuMi0xLjcsMC42LTIuNGwxLjUsMS41QzkuMSwxMS40LDksMTEuNyw5LDEyYzAsMS43LDEuMywzLDMsM3YtMmwzLDNMMTIsMTl6IE0xNi40LDE0LjRsLTEuNS0xLjVDMTUsMTIuNiwxNSwxMi4zLDE1LDEyDQoJYzAtMS43LTEuMy0zLTMtM3YyTDksOGwzLTN2MmMyLjgsMCw1LDIuMiw1LDVDMTcsMTIuOSwxNi44LDEzLjcsMTYuNCwxNC40eiIvPg0KPC9zdmc+DQo=',
            scale: 'medium',
            tooltip: 'Modify',
            menu: menu
        });
    }

    if(e.detail.object.data.general.className === "Group")
    {
        const prods = e.detail.object.data.data.Products.length + e.detail.object.data.data.Sets.length;

        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            scale: 'medium',
            tooltip: 'Download',
            menu: [
                {
                    text: t('Product(Set) images'),
                    tooltip: t('Download all images from assigned Products and ProductSets as zip archive'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/gallery.svg',
                    scale: 'medium',
                    handler: function () {

                        if(prods > 0)
                        {
                            const path = "/export/images/" + e.detail.object.id;
                            window.open(path);
                        }
                        else
                        {
                            Ext.Msg.alert('Warning', 'Group has no Products and Sets!');
                        }
                    }
                }
            ]
        })
    }

    if(e.detail.object.data.general.className === "Offer")
    {
        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            scale: 'medium',
            tooltip: 'Download',
            menu: [
                {
                    text: t('Price list (preview)'),
                    tooltip: t('Download PDF price list'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
                    scale: 'medium',
                    handler: function () {
                        const path = "/prices/" + e.detail.object.id;
                        window.open(path);
                    }
                },
                {
                    text: t('Price list (xlsx)'),
                    tooltip: t('Download XLSX price list'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
                    scale: 'medium',
                    handler: function () {
                        const path = "/prices/" + e.detail.object.id + "?kind=xlsx";
                        window.open(path);
                    }
                }
            ]
        })
    }
})

document.addEventListener(pimcore.events.pimcoreReady, (e) => {
    const ADD_SIBLING_KEY = 'm';

    const openNewObject = function(){

        let tabPanel = Ext.getCmp("pimcore_panel_tabs");
        let activeTab = tabPanel.getActiveTab();

        if(!activeTab)
        {
            Ext.MessageBox.show({
                title: t("Error"),
                msg: t("Open object first that need to have sibling first."),
                buttons: Ext.Msg.OK,
            });

            return;
        }

        let parentId = activeTab.object.data.general.parentId;

        if(parentId == null)
        {
            Ext.MessageBox.show({
                title: t("Error"),
                msg: t("Object has no parent"),
                buttons: Ext.Msg.OK,
            });

            return;
        }

        Ext.MessageBox.prompt(t("Enter sibling key"), t("Enter sibling key"), function(btn, text){
            if(btn === 'ok'){
                var options = {
                    url: Routing.generate('pimcore_admin_dataobject_dataobject_add'),
                    elementType: "object",
                    sourceTree: null,
                    parentId: parentId,
                    className: activeTab.object.data.general.className,
                    classId: activeTab.object.data.general.classId,
                    key: pimcore.helpers.getValidFilename(text, "object")
                };

                pimcore.elementservice.addObject(options);
            }
        })
    }

    new Ext.util.KeyMap({
        target: document,
        key: ADD_SIBLING_KEY,
        fn: openNewObject,
        ctrl: true,
        alt: false,
        shift: false,
        stopEvent: true
    })

    const openDocs = function(){
        window.open(document.location.origin +  ":8005", "_blank");
    }

    const openFactory = function(){
        window.open(document.location.origin + "/factory", "_blank");
    }

    const toolbar = pimcore.globalmanager.get("layout_toolbar");
    const fileMenu = toolbar.fileMenu;

    if(fileMenu)
    {
        fileMenu.insert({
            text: "Megstyl Docs",
            iconCls: "pimcore_nav_icon_documentation",
            handler: openDocs,
        })

        fileMenu.insert({
            text: "Megstyl Factory",
            iconCls: "pimcore_nav_icon_documentation",
            handler: openFactory,
        })
    }
})