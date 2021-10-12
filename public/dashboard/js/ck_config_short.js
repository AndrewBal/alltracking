CKEDITOR.editorConfig = function (e) {
    e.toolbar = [{name: "tools", items: ["Maximize", "ShowBlocks", "Source"]}, {
        name: "basicstyles",
        groups: ["basicstyles"],
        items: ["Bold", "Italic", "Underline", "Strike"]
    }, {
        name: "paragraph",
        groups: ["list", "indent", "blocks", "bidi"],
        items: ["NumberedList", "BulletedList", "-", "CreateDiv", "-", "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"]
    }, {name: "insert", items: ["CreateDiv"]}, {name: "colors", items: ["TextColor", "BGColor"]}, {
        name: "styles",
        items: ["Format", "Font", "FontSize"]
    }], e.toolbarCanCollapse = !0, e.removeButtons = "about", e.format_tags = "p;h1;h2;h3;pre;div", e.allowedContent = !0, e.authentication = !0
};
