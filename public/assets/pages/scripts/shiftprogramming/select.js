 $(document).on('change', '.sel', function(e) {
     //var matches = document.querySelectorAll(".sel");
     //matches.forEach(element => console.log(element)){
     selectCheck();

 });

 function selectCheck() {
     var valorQuitar = 0;
     var nombre = "";
     $('.sel').each(function() {
         $(this).find('option').removeAttr('disabled');
     });
     $('.sel').each(function() {
         valorQuitar = $(this).val();
         nombre = this.name;
         if (valorQuitar != 0) {
             $('.sel').each(function() {
                 if (this.name == nombre) {
                     $(this).find('option[value="' + valorQuitar + '"]').attr('selected', true);
                 } else {
                     $(this).find('option[value="' + valorQuitar + '"]').attr('disabled', true);
                 }
             });
         }
     });
 }