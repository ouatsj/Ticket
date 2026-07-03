document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.upversement').forEach(function (e) {
        
        e.onclick = function () {
            let mtaForm = document.querySelector('#verForm');
            mtaForm.setAttribute('action', `${APP_ROOT}/Caisses/upautreversment/${e.dataset.cle_compagnie}/${e.dataset.ID}`);
            document.querySelector('h3#Titleverse').innerHTML = `MODIFICATION SUR LE VERSEMENT DE : ${e.dataset.nombeneficiaire}`;
            $('#vesreinterneid').val(`${e.dataset.type_versement}`);
            $('#caissegenreversid').val(`${e.dataset.id_genre_versement}`);
            $('#prenomidentif').val(`${e.dataset.nombeneficiaire}`);
            $('#personnelsid').val(`${e.dataset.typpersonnel}`);
            $('#autremontantversemid').val(`${e.dataset.montant_verser}`);
            $('#autrebordereauid').val(`${e.dataset.bordereau_verser}`);
            $('#autrecommentverseid').val(`${e.dataset.commentaire}`);
            $('#autredateversementsid').val(`${e.dataset.dat}`);

        }
    })
});