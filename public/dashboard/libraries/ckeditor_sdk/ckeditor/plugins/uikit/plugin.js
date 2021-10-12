(function () {
    CKEDITOR.plugins.add("uikit", {
        requires: "uikit",
        icons: "",
        init: function (editor) {
            editor.addContentsCss(CKEDITOR.getUrl("plugins/uikit/css/uikit.min.css"));
        }
    });
})();
