<?php
class valid {
    public function text($text){
        
            if(preg_match('/[a-z]$/',$text)) {     
            return TRUE;
            }  else {
                return FALSE;
            }
    }
    
    public function key($key) {

            if(preg_match('/[";",":","%","?",".",","]$/',$key)) {  
                return FALSE;
              }  else {
                return TRUE;
            }    
    }
    
    public function email($email) {
            if(preg_match('/^[a-zA-Z0-9.\-_]+@[a-zA-Z0-9\-.]+\.[a-zA-Z]{2,4}$/', $email)){
                return TRUE;
            }  else {
                return FALSE;
            }
    }
    public function mep($subject){
    //    $subject='MEP04598005';
        $pattern = '/^[A-Z0-9]{10,13}+$/';
        if(preg_match_all( $pattern, $subject , $matches )) {
                return TRUE;
        }  else {
                return FALSE;
        }

    }
    
    public function providerid($subject){
//        $subject = 'OT40132ARGPL3';
        $provider = '/^[A-Z0-9]1{0,15}/';
           if(preg_match_all( $provider, $subject , $matches )) {
                 return TRUE;
           }  else {
           return FALSE;
           }

    }
}

?>