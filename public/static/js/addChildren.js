document.addEventListener(pimcore.events.pimcoreReady, (e) => {
    const ADD_CHILDREN_KEY = 'm';

    const addChildren = function(){

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

        if(activeTab.object == null)
        {
            Ext.MessageBox.show({
                title: t("Error"),
                msg: t("Can not add children with kind other than object"),
                buttons: Ext.Msg.OK
            });

            return;
        }

        if(activeTab.object.data.type == 'folder')
        {
            Ext.MessageBox.show({
                title: t("Error"),
                msg: t("Can not add folder children. Objects only."),
                buttons: Ext.Msg.OK,
            });

            return;
        }

        let parentId = activeTab.object.data.general.id;

        var options = {
            url: Routing.generate('pimcore_admin_dataobject_dataobject_add'),
            elementType: "object",
            sourceTree: null,
            parentId: parentId,
            className: activeTab.object.data.general.className,
            classId: activeTab.object.data.general.classId,
        };

        var ret = Ext.MessageBox.prompt(t("Enter new object(s) key or pattern"), t("Enter new object(s) key or pattern"), function(btn, text){
            if(btn === 'ok'){
                var be = expansions(text);

                var total = be.length;
                var completed = 0;
                var added = 0;
                var failed = false;

                function done(res)
                {
                    if(JSON.parse(res.responseText).success === true)
                    {
                        added++;
                    }

                    completed++;
                    if(completed == total)
                    {
                        pimcore.helpers.showNotification("Ok", "Dodano: " + added + " / " + completed + " obiektów", "ok", "Ok");
                        pimcore.treenodelocator.showInTree(parentId, "object");
                        pimcore.elementservice.refreshNodeAllTrees('object', parentId);
                    }
                }

                for(var i=0; i<total; i++)
                {
                    options['key'] = pimcore.helpers.getValidFilename(be[i], "object");

                    Ext.Ajax.request({
                        url: options.url,
                        method: 'POST',
                        params: options,
                        success: function(res){
                            done(res);
                        }
                    });
                }
            }
        })
    }

    new Ext.util.KeyMap({
        target: document,
        key: ADD_CHILDREN_KEY,
        fn: addChildren,
        ctrl: true,
        alt: false,
        shift: false,
        stopEvent: true
    })

})