CKEDITOR.dialog.add('setfieldDialog', function (editor) {
    // let data = '<div class="row"> <ul class="collapsible"><li><div class="collapsible-header"><i class="material-icons"></i>State</div><div class="collapsible-body"> <table><tr><td><a href="">SELECT</td><td>MP</td></tr></table> </div></li></ul></div>';

    let baseUrl = document.getElementById('path_data').value;
    let entity_data = "";
    let field_data = "";
    let child_entity = "";
    let child_entity_field = "";
    $.ajaxSetup({
        async: false
        });
    $.getJSON(baseUrl+'/ckeditor/plugins/setfield/dialogs/entity_data.json', function(data){
        entity_data = data;
        field_data = data.fields;
        child_entity =  data.children;
    });
    $.ajaxSetup({
        async: true
        });
    let data = '';

    data += '<div class="row"> <ul class="collapsible"><li><div class="collapsible-header"><i class="material-icons"></i>'+entity_data.name+'</div>';
    data += '<div class="collapsible-body">';
    data += '<table>';
    for(item in field_data)
    {
        data += '<tr>';
        data += '<td style="display: table-cell;padding: 15px 5px;text-align: left; vertical-align: middle;border-radius: 2px;border: 1px solid;"><a class="select_field" data-is_parent="yes" data-parent_id="'+entity_data.parent_entity_id+'" data-is_multiple="'+entity_data.is_multiple+'" data-entity_id="'+entity_data.id+'"  data-field_id="'+field_data[item].id+'"  data-field_name="'+field_data[item].label+'" style="margin: 10px; color: purple;">SELECT</td>';
        data += '<td style="display: table-cell;padding: 15px 5px;text-align: left; vertical-align: middle;border-radius: 2px;border: 1px solid;">'+field_data[item].label+'</td>';
        data += '</tr>';
        
    }
    data += '</table>';
    data += '</div></li></ul></div>';

    
    let childData = "";
    for(child in child_entity)
    {
        childData += '<div class="row"> <ul class="collapsible"><li><div class="collapsible-header"><i class="material-icons"></i>'+child_entity[child].name+'</div>';
        childData += '<div class="collapsible-body">';
        childData += '<table>';
        let child_fields = child_entity[child].fields;
        for(childFieldId in child_fields)
        {
            childData += '<tr>';
            childData += '<td style="display: table-cell;padding: 15px 5px;text-align: left; vertical-align: middle;border-radius: 2px;border: 1px solid;">';
            childData += '<a class="select_field" data-parent_id="'+child_entity[child].parent_entity_id+'" data-entity_id="'+child_entity[child].id+'" data-is_parent="no"  data-is_multiple="'+child_entity[child].is_multiple+'"  data-field_id="'+child_fields[childFieldId].id+'"  data-field_name="'+child_fields[childFieldId].label+'" style="margin: 10px; color: purple;">SELECT</td>';
            childData += '<td style="display: table-cell;padding: 15px 5px;text-align: left; vertical-align: middle;border-radius: 2px;border: 1px solid;">'+child_fields[childFieldId].label+'</td>';
            childData += '</tr>';
            
        }
        childData += '</table>';
        childData += '</div></li></ul></div>';
    }

    

    return {
        title: 'Set Fields',
        minWidth: 700,
        minHeight: 400,
        contents: [
            {
                id: 'tab-basic',
                label: 'Select Field',
                elements: [
                    // {
                    //     type: 'select',
                    //     id: 'Field',
                    //     label: 'Select Field',
                    //     style: 'display:block;',
                    //     items: [['---------Select Field-------','']],
                    //     onLoad: function(element){
                    //         // let baseUrl = window.location.origin + '/' + window.location.pathname.split ('/') [1] + '/';
                    //         let baseUrl = document.getElementById('path_data').value;
                    //         let entity_data = "";
                    //         let field_fata = "";
                    //         $.getJSON(baseUrl+'/ckeditor/plugins/setfield/dialogs/entity_data.json', function(data){
                    //             entity_data = data;
                    //             field_fata = data.field
                    //             console.log(data);
                    //             console.log(data.fields);
                    //         });

                    //     },
                    //     onChange: function (api) {
                    //         // this = CKEDITOR.ui.dialog.select
                    //         alert('Current value: ' + this.getValue());
                    //     }
                    // },
                    {
                        type: 'html',
                        html: data,
                        onLoad: function(element){
                            $('.collapsible').collapsible({
                                accordion:true
                              });
                        }
                    },
                    {
                        type: 'html',
                        html: childData,
                        onLoad: function(element){
                            $('.collapsible').collapsible({
                                accordion:true
                              });
                        }
                    },
                    // {
                    //     type: 'html',
                    //     html: '<div class="accordion" id="accordionExample"><div class="card"><div class="card-header" id="headingOne"><h2 class="mb-0"><button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Collapsible Group Item #1</button></h2></div><div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample"><div class="card-body">Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.</div></div></div>',
                    //     onLoad: function(){
                            
                    //     }
                    // }
                ]
            },
        ],
        buttons : [CKEDITOR.dialog.cancelButton ],
        onOk: function () {

        }
    };
});