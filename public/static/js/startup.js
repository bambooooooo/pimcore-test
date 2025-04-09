document.addEventListener(pimcore.events.postOpenObject, function(e){

    if(e.detail.object.data.general.className === "Product" || e.detail.object.data.general.className === "ProductSet")
    {
        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/download-cloud.svg',
            scale: 'medium',
            menu: [
                {
                    text: t('Images'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/gallery.svg',
                    scale: 'medium',
                    handler: function () {
                        const path = "/export/images/" + e.detail.object.id;
                        window.open(path);
                    }
                }
            ]
        });

        e.detail.object.toolbar.add({
            icon: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjQgMjQ7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0yMS43LDE0LjdMMTkuNCwxM2wwLjEtMWwtMC4xLTFsMi4xLTEuNmMwLjItMC4xLDAuMy0wLjQsMC4xLTAuNmwtMi0zLjVDMTkuNSw1LDE5LjMsNSwxOSw1bC0yLjUsMQ0KCWMtMC41LTAuNC0xLjEtMC43LTEuNy0xbC0wLjQtMi43QzE0LjUsMi4yLDE0LjMsMiwxNCwyaC00QzkuOCwyLDkuNSwyLjIsOS41LDIuNEw5LjEsNS4xQzguNSw1LjMsOCw1LjcsNy40LDZMNSw1DQoJQzQuNyw1LDQuNSw1LDQuMyw1LjNsLTIsMy41QzIuMiw5LDIuMyw5LjIsMi41LDkuNEw0LjYsMTFsLTAuMSwxbDAuMSwxbC0yLjEsMS43Yy0wLjIsMC4yLTAuMywwLjQtMC4xLDAuNmwyLDMuNQ0KCUM0LjUsMTksNC43LDE5LDUsMTlsMi41LTFjMC41LDAuNCwxLjEsMC43LDEuNywxbDAuNCwyLjdjMCwwLjIsMC4zLDAuNCwwLjUsMC40aDRjMC4zLDAsMC41LTAuMiwwLjUtMC40TDE1LDE5DQoJYzAuNi0wLjMsMS4yLTAuNiwxLjctMWwyLjUsMWMwLjIsMC4xLDAuNSwwLDAuNi0wLjJsMi0zLjVDMjEuOSwxNS4xLDIxLjksMTQuOCwyMS43LDE0Ljd6IE0xMiwxOXYtMmMtMi44LDAtNS0yLjItNS01DQoJYzAtMC45LDAuMi0xLjcsMC42LTIuNGwxLjUsMS41QzkuMSwxMS40LDksMTEuNyw5LDEyYzAsMS43LDEuMywzLDMsM3YtMmwzLDNMMTIsMTl6IE0xNi40LDE0LjRsLTEuNS0xLjVDMTUsMTIuNiwxNSwxMi4zLDE1LDEyDQoJYzAtMS43LTEuMy0zLTMtM3YyTDksOGwzLTN2MmMyLjgsMCw1LDIuMiw1LDVDMTcsMTIuOSwxNi44LDEzLjcsMTYuNCwxNC40eiIvPg0KPC9zdmc+DQo=',
            scale: 'medium',
            menu: [
                {
                    text: t('Translate name'),
                    icon: '/bundles/pimcoreadmin/img/flat-white-icons/translation.svg',
                    scale: 'medium',
                    handler: function () {
                        console.log("Enqueue translation for " + e.detail.object.id);
                    }
                },
                {
                    text: t('ERP Export'),
                    icon: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHBhdGggZmlsbD0iI0EzQTVBNyIgZD0iTTIwLDZoLTRWNGMwLTEuMS0wLjktMi0yLTJoLTRDOC45LDIsOCwyLjksOCw0djJINEMyLjksNiwyLDYuOSwyLDh2MTFjMCwxLjEsMC45LDIsMiwyaDE2YzEuMSwwLDItMC45LDItMg0KCVY4QzIyLDYuOSwyMS4xLDYsMjAsNnogTTE0LDZoLTRWNC40QzEwLDQuMiwxMC4yLDQsMTAuNCw0aDMuMkMxMy44LDQsMTQsNC4yLDE0LDQuNFY2eiIvPg0KPHJlY3QgeD0iMiIgeT0iMTMiIGZpbGw9IiM0ODRCNEUiIHdpZHRoPSIyMCIgaGVpZ2h0PSIxIi8+DQo8cmVjdCB4PSI5IiB5PSIxMiIgZmlsbD0iIzQ4NEI0RSIgd2lkdGg9IjYiIGhlaWdodD0iNCIvPg0KPHJlY3QgeD0iMTAiIHk9IjEzIiBmaWxsPSIjQTNBNUE3IiB3aWR0aD0iNCIgaGVpZ2h0PSIyIi8+DQo8L3N2Zz4NCg==',
                    scale: 'medium',
                    handler: function () {
                        console.log("Enqueue to erp export...");
                    }
                }
            ]
        });
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
            if(btn == 'ok'){
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
})