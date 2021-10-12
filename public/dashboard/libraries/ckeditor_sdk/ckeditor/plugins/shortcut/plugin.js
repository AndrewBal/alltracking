(function () {
    CKEDITOR.plugins.add("shortcut", {
        requires: ['dialog', 'fakeobjects'],
        icons: "shortcut",
        init: function (editor) {
            editor.addCommand("shortcut", new CKEDITOR.dialogCommand("shortcutDialog"));
            editor.ui.addButton("Shortcut", {
                label: "Всавить элемент",
                command: "shortcut"
            });
            CKEDITOR.dialog.add("shortcutDialog", this.path + "dialogs/shortcut.js")
        }
    });
})();