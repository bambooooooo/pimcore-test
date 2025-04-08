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
        })

        e.detail.object.toolbar.add({
            icon: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMi4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iRWJlbmVfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHdpZHRoPSIyNHB4IiBoZWlnaHQ9IjI0cHgiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwIDAgMjQgMjQiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHBhdGggZmlsbD0iI0EzQTVBNyIgZD0iTTE5LjQsMTNsMC4xLTFsLTAuMS0xbDIuMS0xLjZjMC4yLTAuMSwwLjMtMC40LDAuMS0wLjZsLTItMy41QzE5LjUsNSwxOS4zLDUsMTksNWwtMi41LDENCgljLTAuNS0wLjQtMS4xLTAuNy0xLjctMWwtMC40LTIuN0MxNC41LDIuMiwxNC4zLDIsMTQsMmgtNEM5LjgsMiw5LjUsMi4yLDkuNSwyLjRMOS4xLDUuMUM4LjUsNS4zLDgsNS43LDcuNCw2TDUsNQ0KCUM0LjcsNSw0LjUsNSw0LjMsNS4zbC0yLDMuNUMyLjIsOSwyLjMsOS4yLDIuNSw5LjRMNC42LDExbC0wLjEsMWwwLjEsMWwtMi4xLDEuN2MtMC4yLDAuMi0wLjMsMC40LTAuMSwwLjZsMiwzLjUNCglDNC41LDE5LDQuNywxOSw1LDE5bDIuNS0xYzAuNSwwLjQsMS4xLDAuNywxLjcsMWwwLjQsMi43YzAsMC4yLDAuMywwLjQsMC41LDAuNGg0YzAuMywwLDAuNS0wLjIsMC41LTAuNEwxNSwxOQ0KCWMwLjYtMC4zLDEuMi0wLjYsMS43LTFsMi41LDFjMC4yLDAuMSwwLjUsMCwwLjYtMC4ybDItMy41YzAuMS0wLjIsMC4xLTAuNS0wLjEtMC42TDE5LjQsMTN6Ii8+DQo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMTIsMTl2LTJjLTIuOCwwLTUtMi4yLTUtNWMwLTAuOSwwLjItMS43LDAuNi0yLjRsMS41LDEuNUM5LjEsMTEuNCw5LDExLjcsOSwxMmMwLDEuNywxLjMsMywzLDN2LTJsMywzDQoJTDEyLDE5eiIvPg0KPHBhdGggZmlsbD0iI0ZGRkZGRiIgZD0iTTE2LjQsMTQuNGwtMS41LTEuNUMxNSwxMi42LDE1LDEyLjMsMTUsMTJjMC0xLjctMS4zLTMtMy0zdjJMOSw4bDMtM3YyYzIuOCwwLDUsMi4yLDUsNQ0KCUMxNywxMi45LDE2LjgsMTMuNywxNi40LDE0LjR6Ii8+DQo8L3N2Zz4NCg==',
            scale: 'medium',
            menu: [
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