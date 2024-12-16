CKEDITOR.plugins.add('setfield', {
    requires: 'widget',
    icons: 'fields',
    init: function (editor) {
        //Plugin logic goes here.
        // let baseUrl = window.location.origin + '/' + window.location.pathname.split ('/') [1] + '/' + window.location.pathname.split ('/') [2] + '/';
        editor.addContentsCss(this.path + 'dialogs/style/style.css');

        editor.addCommand('showPopup', new CKEDITOR.dialogCommand('setfieldDialog'));


        // 
        //     exec: function( editor ) {
        //        alert('Botton Clicked');
        //     }

        //Add Button
        editor.ui.addButton('Fields', {
            label: 'Set Fields',
            command: 'showPopup',
            // toolbar: 'insert'
        });

        CKEDITOR.dialog.add('setfieldDialog', this.path + 'dialogs/setfield.js');
        $(document).on('click', '.select_field', function (element) {
            let entity_id = $(this).attr('data-entity_id');
            let field_id = $(this).attr('data-field_id');

            let field_label = $(this).attr('data-field_name');
            let is_parent = $(this).attr('data-is_parent'); // yes or no
            let is_multiple = $(this).attr('data-is_multiple'); //1 or 0
            let parent_entity_id = $(this).attr('data-parent_id');

            let field = '_field_';
            let dataId = '{{{'+entity_id+field+field_id+'}}}';
            
                let myValue = '<input class="entityFieldId" data-parent_entity_id="'+parent_entity_id+'" data-is_parent="'+is_parent+'" data-is_multiple="'+is_multiple+'" type="hidden" value="'+dataId+'">';
                myValue += '<span class="fieldLabel">'+field_label+'</span>';
                editor.insertHtml(myValue);
           
                CKEDITOR.dialog.getCurrent().hide();
                
        });


    },
    onLoad: function () {
        CKEDITOR.addCss(
            'td,' +
            ' th {' +
            ' display: table-cell; !important' +
            ' padding: 15px 5px; !important' +
            'text-align: left;' +
            'vertical-align: middle;' +
            'border-radius: 2px;' +
            'border: 1px solid; !important' +
            '}'
        );
    }
});