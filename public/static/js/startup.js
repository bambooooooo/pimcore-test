Date.prototype.ddmmyyyy = function() {
    var mm = this.getMonth() + 1;
    var dd = this.getDate();

    return [
        (dd > 9 ? '' : '0') + dd,
        (mm > 9 ? '': '0') + mm,
        this.getFullYear(),
    ].join('.');
}

document.addEventListener(pimcore.events.postOpenAsset, function(e) {

    if(e.detail.asset.data.mimetype === 'application/pdf')
    {
        e.detail.asset.toolbar.add({
            text: t('Rotate pages'),
            tooltip: t('Rotate pages'),
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/rotate_camera.svg',
            scale: 'medium',
            menu: [
                {
                    text: t('Rotate left (90)'),
                    scale: 'medium',
                    tooltip: t('Rotate left (90)'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/rotate_camera.svg',
                    handler: function () {
                        Ext.Ajax.request({
                            url: "/assets/rotate/" + e.detail.asset.id + "/270",
                            success: function (data) {
                                console.log(data.responseText);
                                e.detail.asset.reload();
                            },
                            failure: function (error) {
                                console.log("[Error] " + error.responseText);
                            },
                        });
                    }
                },
                {
                    text: t('Rotate right (90)'),
                    scale: 'medium',
                    tooltip: t('Rotate right (90)'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/rotate_camera.svg',
                    handler: function () {
                        Ext.Ajax.request({
                            url: "/assets/rotate/" + e.detail.asset.id + "/90",
                            success: function (data) {
                                console.log(data.responseText);
                                e.detail.asset.reload();
                            },
                            failure: function (error) {
                                console.log("[Error] " + error.responseText);
                            },
                        });
                    }
                },
                {
                    text: t('Rotate (180)'),
                    scale: 'medium',
                    tooltip: t('Rotate (180)'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/rotate_camera.svg',
                    handler: function () {
                        Ext.Ajax.request({
                            url: "/assets/rotate/" + e.detail.asset.id + "/180",
                            success: function (data) {
                                console.log(data.responseText);
                                e.detail.asset.reload();
                            },
                            failure: function (error) {
                                console.log("[Error] " + error.responseText);
                            },
                        });
                    }
                }
            ]
        })
    }
})

document.addEventListener(pimcore.events.postOpenObject, function(e){

    const translate = function (field = "name") {

        var current = 0;
        var total = pimcore.settings.websiteLanguages.length;

        var progressbar = new Ext.ProgressBar({
            text: t('Progress'),
            style: "margin-top: 0px;",
            width: 500
        })

        var cancelBtn = Ext.create('Ext.Button', {
            scale: 'small',
            text: t('Cancel'),
            tooltip: t('Cancel'),
            icon: '/bundles/pimcoreadmin/img/flat-color-icons/cancel.svg',
            style: 'margin-left: 5px; height: 30px',
            handler: function () {
                current = Infinity;
            }
        })

        var panel = Ext.create('Ext.panel.Panel', {
            layout: {
                type: 'hbox'
            },
            items: [
                progressbar,
                cancelBtn
            ]
        });

        var pbWin = new Ext.Window({
            title: t('Translating'),
            items: [
                panel
            ],
            layout: 'fit',
            width: 650,
            bodyStyle: "padding: 10px",
            closable: false,
            plain: true,
            modal: false
        });

        var sourceText = null;

        switch(field)
        {
            case "name":
            {
                sourceText = e.detail.object.data.data.localizedfields.data[pimcore.settings.language].Name;
                break;
            }
            case "description":
            {
                sourceText = e.detail.object.data.data.localizedfields.data[pimcore.settings.language].Description;
                break;
            }
            default:
            {
                console.log("Error: Invalid field name [" + field + "]");
                return;
            }
        }

        var translate = function ()
        {
            console.log("Translate into " + pimcore.settings.websiteLanguages[current] + "...");

            let params =  {
                'id': e.detail.object.id,
                'origin': pimcore.settings.language,
                'loc': pimcore.settings.websiteLanguages[current],
                'field': field
            };

            switch(field)
            {
                case "name":
                {
                    params['name'] = e.detail.object.data.data.localizedfields.data[pimcore.settings.language].Name;
                    sourceText = e.detail.object.data.data.localizedfields.data[pimcore.settings.language].Name;
                    break;
                }
                case "description":
                {
                    params['name'] = e.detail.object.data.data.localizedfields.data[pimcore.settings.language].Description;
                    sourceText = e.detail.object.data.data.localizedfields.data[pimcore.settings.language].Description;
                    break;
                }
            }

            Ext.Ajax.request({
                url: "/object/translate-name",
                params: params,
                success: function (data) {
                    console.log(data.responseText);
                },
                failure: function (error) {
                    console.log("[Error] " + error.responseText);
                },
                callback: function (response) {

                    current = current + 1;

                    if(current >= total)
                    {
                        pbWin.close();
                        e.detail.object.reload();
                    }
                    else
                    {
                        var progress = current / total;
                        var percent = Math.ceil(progress * 100);
                        var status = t("Translating into ") + pimcore.settings.websiteLanguages[current] + "... (" + percent + "%)";

                        progressbar.updateProgress(progress, status);
                        setTimeout(translate, 500);
                    }
                }
            });
        }

        Ext.Msg.confirm("Translate", "Translate [" + sourceText + "] into " + total + " languages?", function(btn){
            if(btn === "yes")
            {
                pbWin.show();
                console.log("[TX] " + sourceText);
                translate();
            }
        })
    }

    const translateNameSubItem = {
        text: t('Translate name'),
        iconCls: 'pimcore_material_icon_translation',
        scale: 'medium',
        tooltip: t('Translate full name to all system languages'),
        handler: function()
        {
            translate("name")
        }
    };

    const translateDescriptionSubItem = {
        text: t('Translate description'),
        iconCls: 'pimcore_material_icon_translation',
        scale: 'medium',
        tooltip: t('Translate description to all system languages'),
        handler: function()
        {
            translate("description")
        }
    }

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
            },
            translateNameSubItem
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
                            Ext.Msg.alert('Warning', 'Group has no Products and Sets!');
                        }
                    }
                },
                {
                    text: t('Bulk datasheet'),
                    tooltip: t('Download Bulk datasheet'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
                    scale: 'medium',
                    handler: function () {
                        const path = "/factory/en/" + e.detail.object.id + "/datasheet";
                        window.open(path);
                    }
                }
            ]
        })

        e.detail.object.toolbar.add({
            icon: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjQgMjQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yMS43LDE0LjdMMTkuNCwxM2wwLjEtMWwtMC4xLTFsMi4xLTEuNmMwLjItMC4xLDAuMy0wLjQsMC4xLTAuNmwtMi0zLjVDMTkuNSw1LDE5LjMsNSwxOSw1bC0yLjUsMQ0KCWMtMC41LTAuNC0xLjEtMC43LTEuNy0xbC0wLjQtMi43QzE0LjUsMi4yLDE0LjMsMiwxNCwyaC00QzkuOCwyLDkuNSwyLjIsOS41LDIuNEw5LjEsNS4xQzguNSw1LjMsOCw1LjcsNy40LDZMNSw1DQoJQzQuNyw1LDQuNSw1LDQuMyw1LjNsLTIsMy41QzIuMiw5LDIuMyw5LjIsMi41LDkuNEw0LjYsMTFsLTAuMSwxbDAuMSwxbC0yLjEsMS43Yy0wLjIsMC4yLTAuMywwLjQtMC4xLDAuNmwyLDMuNQ0KCUM0LjUsMTksNC43LDE5LDUsMTlsMi41LTFjMC41LDAuNCwxLjEsMC43LDEuNywxbDAuNCwyLjdjMCwwLjIsMC4zLDAuNCwwLjUsMC40aDRjMC4zLDAsMC41LTAuMiwwLjUtMC40TDE1LDE5DQoJYzAuNi0wLjMsMS4yLTAuNiwxLjctMWwyLjUsMWMwLjIsMC4xLDAuNSwwLDAuNi0wLjJsMi0zLjVDMjEuOSwxNS4xLDIxLjksMTQuOCwyMS43LDE0Ljd6IE0xMiwxOXYtMmMtMi44LDAtNS0yLjItNS01DQoJYzAtMC45LDAuMi0xLjcsMC42LTIuNGwxLjUsMS41QzkuMSwxMS40LDksMTEuNyw5LDEyYzAsMS43LDEuMywzLDMsM3YtMmwzLDNMMTIsMTl6IE0xNi40LDE0LjRsLTEuNS0xLjVDMTUsMTIuNiwxNSwxMi4zLDE1LDEyDQoJYzAtMS43LTEuMy0zLTMtM3YyTDksOGwzLTN2MmMyLjgsMCw1LDIuMiw1LDVDMTcsMTIuOSwxNi44LDEzLjcsMTYuNCwxNC40eiIvPg0KPC9zdmc+DQo=',
            scale: 'medium',
            tooltip: 'Modify',
            menu: [
                translateNameSubItem,
                translateDescriptionSubItem
            ]
        });
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
                    tooltip: t('Preview price list'),
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
                },
                {
                    text: t('Datasheet (PDF)'),
                    tooltip: t('Download PDF datasheets'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
                    scale: 'medium',
                    handler: function () {
                        const path = "/factory/" + pimcore.settings.language + "/" + e.detail.object.id + "/datasheet?show_prices=" + e.detail.object.id;
                        window.open(path);
                    }
                }
            ]
        })
    }

    if(e.detail.object.data.general.className === "User")
    {
        var path = "/prices/";
        if(e.detail.object.data.data.Offers.length > 0)
        {
            console.log(e.detail.object.data.general.key);

            let first = true;
            for(let offer of e.detail.object.data.data.Offers)
            {
                if(first)
                {
                    first = false;
                    path = path + offer.id + "?kind={kind}&filename=" + e.detail.object.data.general.key + "-" + (new Date().ddmmyyyy());
                }
                else
                {
                    path = path + "&references[]=" + offer.id;
                }
            }
        }

        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            scale: 'medium',
            tooltip: 'Download',
            menu: [
                {
                    text: t('Offers (preview)'),
                    tooltip: t('Download all offers (preview)'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
                    scale: 'medium',
                    handler: function () {
                        if(e.detail.object.data.data.Offers.length > 0)
                        {
                            window.open(path.replace("{kind}", "preview"));
                        }
                        else
                        {
                            Ext.Msg.alert("No offer assigned to the user");
                        }
                    }
                },
                {
                    text: t('Price list (xlsx)'),
                    tooltip: t('Download all offers (xlsx)'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
                    scale: 'medium',
                    handler: function () {
                        if(e.detail.object.data.data.Offers.length > 0)
                        {
                            window.open(path.replace("{kind}", "xlsx"));
                        }
                        else
                        {
                            Ext.Msg.alert("No offer assigned to the user");
                        }
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