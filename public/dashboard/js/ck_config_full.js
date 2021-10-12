CKEDITOR.editorConfig = function (e) {
    e.extraPlugins = "filebrowser,uikit,shortcut,youtube,iframe,stylescombo", e.toolbar = [{
        name: "tools",
        items: ["Maximize", "ShowBlocks", "Source", "-", "CopyFormatting", "RemoveFormat", "NewPage"]
    }, {
        name: "basicstyles",
        groups: ["basicstyles", "cleanup"],
        items: ["Bold", "Italic", "Underline", "Strike", "Subscript", "Superscript"]
    }, {name: "colors", items: ["TextColor", "BGColor"]}, {
        name: "paragraph",
        groups: ["list", "indent", "blocks", "align", "bidi"],
        items: ["NumberedList", "BulletedList", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock", "-", "BidiLtr", "BidiRtl", "Blockquote"]
    }, {name: "links", items: ["Link", "Unlink", "Anchor"]}, {
        name: "insert",
        items: ["Image", "Youtube", "Iframe", "Table", "-", "CreateDiv", "Shortcut"]
    }, {
        name: "styles",
        items: ["Styles", "Format", "Font", "FontSize"]
    }], e.toolbarCanCollapse = !0, e.removeButtons = "about", e.format_tags = "p;h1;h2;h3;pre;div", e.removeDialogTabs = "image:advanced", e.filebrowserBrowseUrl = "/libraries/ckeditor_sdk/ckfinder/ckfinder.html", e.filebrowserUploadUrl = "/libraries/ckeditor_sdk/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files", e.authentication = !0, e.allowedContent = !0
};
