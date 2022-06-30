<?=
"<?php 
use backend\\components\\helpers\\Html; 
    
/*
 * Personalizzare a piacimento la vista
 * \$model è il model legato alla tabella del db
 * \$buttons sono i tasti del template standard {view}{update}{delete}
 * tutto quello che si inserirà qui comparirà dopo il calendario per inserire
 * del codice HTML prima del calendario usare il campo intestazione della
 * configurazione della vista nella pagina index.php
 */
?>
<div class=\"listview-container legenda-calendario\">       
 ############ PERSONALIZZARE IL CALENDARIO CON L&#39;HTML A PIACIMENTO - qui un esempio di legenda, le funzioni non sono implementate di default ##############  
    <div class=\"legenda-calendario-simbolo\" style=\"background-color:<?= \$model->getColoreCategoria() ?>\"></div>
    <div class=\"legenda-calendario-testo\"><?= \$model->getNomeLegenda() ?></div>

</div>"
?>
