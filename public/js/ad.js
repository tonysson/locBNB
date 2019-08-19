
    // language=JQuery-CSS
        $('#add-image').on("click", function (){
            // je récupère le numéro des futurs champs que je vais créer
            const index = +$('#widgets-counter').val();

            //console.log(index);
 
            // je récupère le prototype des entrées
            const tmpl = $('#annonce_images').data('prototype'). replace(/__name__/g, index);
 
            //j'injecte ce code au sein de la div
            $('#annonce_images').append(tmpl);
            $('#widgets-counter').val(index + 1);
            

            // j'injecte ma fction de suppression de bouton

            deleteBtn();

        });

        function updateCounter() {
            const count = +$('#annonce_images div.form-group').length;

            $('#widgets-counter').val(count);
        }

        

        function deleteBtn(){
            $('button[data-action="delete"]').on("click", function(){

                // this represente le bouton cliqué(this dans une fction javascript qui est lié a un evenement represente l'element html qui a declenché l'evenement), dataset represente tous les attributs data-quelquechose,.target parce-que je veux pointer les data-target

                const target = this.dataset.target;

                $(target).remove();


            });

        }
        
        updateCounter();
        deleteBtn();