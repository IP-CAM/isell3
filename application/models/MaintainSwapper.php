<?php
if( $_POST['swap'] ){
    echo rename("isell3", "isell3_backup") && rename("isell3_new", "isell3");    
}