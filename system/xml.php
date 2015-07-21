<?php
class xml {
  
    public function getXml($files){
     
        $type = $files['parameters']['type'];
        $name = $files['parameters']['name'];
        $tmp_name = $files['parameters']['tmp_name'];
        $size = $files['parameters']['size'];
        $typefile = substr($type,(strpos($type, '/')+1));


        if($typefile ==='xml') {
             $result = simplexml_load_file('http://swatka.16mb.com/curl/temp/xml.xml');
//             var_dump($result);
             return $result;
            
            }  else {
                echo json_encode(array('ERROR'=>array(array('MESSAGE'=>'WRONG TYPE OF FILE'))));
                exit();
            }     

    }
}
?>