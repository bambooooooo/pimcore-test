document.addEventListener(pimcore.events.pimcoreReady, (e) => {

    var original = pimcore.object.helpers.gridTabAbstract.prototype.getExportButtons;

    Ext.override(pimcore.object.helpers.gridTabAbstract, {

        getExportButtons: function () {

            var buttons = original ? original.apply(this, arguments) : [];

            buttons.push({
                text: t('XLSX Export (Images)'),
                iconCls: 'pimcore_icon_export',
                handler: function () {
                    var exportType = pimcore.object.gridexport.csv;
                    this.exportPrepareCustom([], exportType);
                }.bind(this)
            });

            return buttons;
        },

        exportPrepareCustom: function (settings, exportType) {
            let params = this.getGridParams();

            var fields = this.getGridConfig().columns;
            var fieldKeys = Object.entries(fields).map(([key, value]) => ({ key: key, label: value.fieldConfig?.label || key }));
            fieldKeys = Ext.encode(fieldKeys);
            params["fields[]"] = fieldKeys;
            if (this.context) {
                params["context"] = Ext.encode(this.context);
            }

            settings = Ext.encode(settings);
            params["settings"] = settings;
            Ext.Ajax.request({
                method: 'POST',
                url: this.exportPrepareUrl,
                params: params,
                success: function (response) {
                    var rdata = Ext.decode(response.responseText);

                    if (rdata.success && rdata.jobs) {
                        const exportSize = rdata.jobs.reduce((a, b) => a + b.length, 0)
                        if (exportSize > 25) {
                            Ext.Msg.confirm("Confirmation", sprintf(t('batch_export_confirmation'), `<b>${new Intl.NumberFormat(navigator.language).format(exportSize)}</b>`),
                                (btn) => {
                                    if (btn === "yes") {
                                        this.exportProcessCustom(rdata.jobs, rdata.fileHandle, fieldKeys, true, settings, exportType);
                                    } else {
                                        return;
                                    }
                                });
                        } else {
                            this.exportProcessCustom(rdata.jobs, rdata.fileHandle, fieldKeys, true, settings, exportType);
                        }
                    }
                }.bind(this)
            });
        },

        exportProcessCustom: function (jobs, fileHandle, fields, initial, settings, exportType) {
            if (initial) {
                this.exportErrors = [];
                this.exportJobCurrent = 0;

                this.exportParameters = {
                    fileHandle: fileHandle,
                    language: this.gridLanguage,
                    settings: settings
                };
                this.exportProgressBar = new Ext.ProgressBar({
                    text: t('initializing'),
                    style: "margin-top: 0px;",
                    width: 500
                });

                this.cancelBtn = Ext.create('Ext.Button', {
                    scale: 'small',
                    text: t('cancel'),
                    tooltip: t('cancel'),
                    icon: '/bundles/pimcoreadmin/img/flat-color-icons/cancel.svg',
                    style: 'margin-left:5px;height:30px',
                    handler: () => {
                        // Stop the batch processing
                        this.exportJobCurrent = Infinity;
                    }
                });

                this.progressPanel = Ext.create('Ext.panel.Panel', {
                    layout: {
                        type: 'hbox',
                    },
                    items: [
                        this.exportProgressBar,
                        this.cancelBtn
                    ],
                });

                this.exportProgressWin = new Ext.Window({
                    title: t("export"),
                    items: [this.progressPanel],
                    layout: 'fit',
                    width: 650,
                    bodyStyle: "padding: 10px;",
                    closable: false,
                    plain: true,
                    listeners: pimcore.helpers.getProgressWindowListeners()
                });
                this.exportProgressWin.show();
            }

            if (this.exportJobCurrent >= jobs.length) {

                this.exportProgressWin.close();
                // error handling
                if (this.exportErrors.length > 0) {

                    var jobErrors = [];
                    for (var i = 0; i < this.exportErrors.length; i++) {
                        jobErrors.push(this.exportErrors[i].job);
                    }
                    Ext.Msg.alert(t("error"), t("error_jobs") + ": " + jobErrors.join(","));
                } else {
                    pimcore.helpers.download(this.getDownloadUrlCustom(fileHandle));
                }

                return;
            }

            var status = (this.exportJobCurrent / jobs.length);
            var percent = Math.ceil(status * 100);
            this.exportProgressBar.updateProgress(status, percent + "%");

            this.context['image'] = true; // custom - for trigger GridExportListener event handling

            this.exportParameters['ids[]'] = jobs[this.exportJobCurrent];
            this.exportParameters["fields[]"] = fields;
            this.exportParameters.classId = this.classId;
            this.exportParameters.initial = initial ? 1 : 0;
            this.exportParameters.language = this.gridLanguage;
            this.exportParameters.context = Ext.encode(this.context);
            this.exportParameters.userTimezone = getUserTimezone();

            Ext.Ajax.request({
                url: Routing.generate("pimcore_admin_dataobject_dataobjecthelper_doexport"),
                method: 'POST',
                params: this.exportParameters,
                success: function (jobs, currentJob, response) {

                    try {
                        var rdata = Ext.decode(response.responseText);
                        if (rdata) {
                            if (!rdata.success) {
                                throw "not successful";
                            }
                        }
                    } catch (e) {
                        this.exportErrors.push({
                            job: currentJob
                        });
                    }

                    window.setTimeout(function () {
                        this.exportJobCurrent++;
                        this.exportProcessCustom(jobs, fileHandle, fields, false, settings, exportType);
                    }.bind(this), this.batchJobDelay);
                }.bind(this, jobs, jobs[this.exportJobCurrent])
            });
        },

        getDownloadUrlCustom: function (fileHandle) {
            return Routing.generate("bnix_download_xlsx_with_images", {fileHandle: fileHandle});
        }
    });
})