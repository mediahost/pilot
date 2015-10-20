/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function(config) {
    // Define changes to default configuration here.
    // For the complete reference:
    // http://docs.ckeditor.com/#!/api/CKEDITOR.config

    // The toolbar groups arrangement, optimized for two toolbar rows.
    config.toolbarGroups = [
        {name: 'clipboard', groups: ['clipboard', 'undo']},
        {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
        {name: 'links'},
        {name: 'insertion', groups: ['insert', 'mediaembed']},
        {name: 'forms'},
        {name: 'tools'},
        {name: 'document', groups: ['mode', 'document', 'doctools']},
        {name: 'others'},
        '/',
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align']},
        {name: 'styles'},
        {name: 'colors'},
//        {name: 'about'},
    ];

    // Remove some buttons, provided by the standard plugins, which we don't
    // need to have in the Standard(s) toolbar.
    config.removeButtons = 'Underline,Subscript,Superscript';

    // Se the most common block elements.
    config.format_tags = 'p;h1;h2;h3;pre';

    // Make dialogs simpler.
    config.removeDialogTabs = 'image:advanced;link:advanced';

    config.language = lang;
    config.extraPlugins += ',imagebrowser,mediaembed';
    config.imageBrowser_listUrl = "";

    config.contentsCss = basePath + "/css/front.css";
    
    config.extraAllowedContent = 'iframe[*]';
    
    config.autosave_delay = 1;


};

(function() {
    CKEDITOR.plugins.add('imagebrowser', {
        "init": function(editor) {
            if (typeof(editor.config.imageBrowser_listUrl) === 'undefined' || editor.config.imageBrowser_listUrl === null) {
                return;
            }

            editor.plugins.imagebrowser.path = basePath;
            editor.config.filebrowserImageBrowseUrl = editor.plugins.imagebrowser.path + "/kcfinder/browse.php?listUrl=" + encodeURIComponent(editor.config.imageBrowser_listUrl);
        }
    });
})();


(function() {
    CKEDITOR.plugins.add('mediaembed',
            {
                init: function(editor)
                {
                    var me = this;
                    CKEDITOR.dialog.add('MediaEmbedDialog', function(instance)
                    {
                        return {
                            title: 'Embed Media',
                            minWidth: 550,
                            minHeight: 200,
                            contents:
                                    [
                                        {
                                            id: 'iframe',
                                            expand: true,
                                            elements: [{
                                                    id: 'embedArea',
                                                    type: 'textarea',
                                                    label: 'Paste Embed Code Here',
                                                    'autofocus': 'autofocus',
                                                    setup: function(element) {
                                                    },
                                                    commit: function(element) {
                                                    }
                                                }]
                                        }
                                    ],
                            onOk: function() {
                                for (var i = 0; i < window.frames.length; i++) {
                                    if (window.frames[i].name == 'iframeMediaEmbed') {
                                        var content = window.frames[i].document.getElementById("embed").value;
                                    }
                                }
                                // console.log(this.getContentElement( 'iframe', 'embedArea' ).getValue());
                                div = instance.document.createElement('div');
                                div.setHtml(this.getContentElement('iframe', 'embedArea').getValue());
                                instance.insertElement(div);
                            }
                        };
                    });

                    editor.addCommand('MediaEmbed', new CKEDITOR.dialogCommand('MediaEmbedDialog'));

                    editor.ui.addButton('MediaEmbed',
                            {
                                label: 'Embed Media',
                                command: 'MediaEmbed',
                                icon: this.path + 'images/icon_bw.png',
                                toolbar: 'mediaembed'
                            });
                }
            });
})();
