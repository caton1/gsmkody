<?php
class valid {
    public function mep(){
        $subject='MEP04598005';
$pattern = '/^[A-Z0-9]{10,15}+$/';
if(preg_match_all( $pattern, $subject , $matches )) {
        return TRUE;
}  else {
        return FALSE;
}

    }
    
    public function providerid(){
        $subject = 'OT40132ARGPL3';
     $provider = '/^[A-Z0-9]1{0,15}/';
   if(preg_match_all( $provider, $subject , $matches )) {
        return TRUE;
    }  else {
        return FALSE;
        }

    }
}
$v=new valid();
$v->providerid();
?>