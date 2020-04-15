// var appF = new Vue({
//     el: '#areas_depts_filter',
//     data: {
//       options: [
//                     {val: 0, name: 'Todos'},
//                     {val: 1, name: 'Área'},
//                     {val: 2, name: 'Departamento'}
//                 ],
//       iOption: 1,
//       lAreas: oData.aData[0],
//       lDepts: oData.aData[1],
//       elements: this.lAreas
//     },
//     methods: {
//       onChangeFilter() {
//           this.elements = this.iOption == 1 ? this.lAreas : this.lDepts;
//           return;
//       }  
//     },
//   })

function onChangeFilter() {
    let v = $('#i_filter').val();
    $('#elems').empty();
    let lCollection = [];

    switch (v) {
        case "1":
            lCollection = oData.lAreas;
            break;

        case "2":
            lCollection = oData.lDepts;
            break;
    
        default:
            break;
    }

    $.each(lCollection, function (i, item) {
        $('#elems').append($('<option>', { 
            value: item.id,
            text : item.name 
        }));
    });
}
