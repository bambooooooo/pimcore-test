document.addEventListener(pimcore.events.postOpenObject, (e) => {

    if(e.detail.object.data.general.className != "Product")
    {
        return;
    }

    console.log(e.detail.object.toolbar);

    e.detail.object.toolbar.add({
            tooltip: t('Download XLSX of product fillment from all its packages'),
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/template.svg',
            scale: 'medium',
            handler: function () {
                const path = "/products/fillments/" + e.detail.object.id + "?kind=xlsx";
                window.open(path);
            }
        }
    )


});