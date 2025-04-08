document.addEventListener(pimcore.events.postOpenObject, function(e){
    if(e.detail.object.data.general.className === "Product" || e.detail.object.data.general.className === "ProductSet")
    {
        e.detail.object.toolbar.add({
            icon: '/bundles/pimcoreadmin/img/flat-white-icons/gallery.svg',
            scale: 'medium',
            handler: function () {
                const path = "/export/images/" + e.detail.object.id;
                window.open(path);
            }
        })
    }
})