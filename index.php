<?php

error_reporting(E_ALL & ~E_NOTICE);

/*
 * Całość jest jeszcze przed testami.
 * Komuinikaty błędów należy obsłużyć
 */
class login {
    private function autolad(){
       
                        
        spl_autoload_register(function ($class) {
            $file = 'system/'.$class.'.php';
            if(file_exists($file)){
                require_once $file;
            } else {
                require_once $class.'.php';
        }
        });  
   
    }

    public function setLogin() {
           //config nie jest klasą
         require_once 'system/config.php';
            $this->autolad();           
            $username = htmlspecialchars($_POST['username']);
            $key = htmlspecialchars($_POST['apiaccesskey']);
            $id = $_POST['produktid'];
            $imei = $_POST['imei'];
            $modelid = $_POST['modelid'];
            $files = $_FILES;
            $mail = new mail();
            if(htmlspecialchars($_POST['action'])=='accountinfo') {
                $action = 'account';
            }  else {
                $action = htmlspecialchars($_POST['action']);
            }
            $requestformat = strtolower($_POST['requestformat']); //obsługa formatu xml
            if($requestformat == 'xml') {
                $xml = new xmlapi();
            }elseif ($requestformat == 'json') {
                  $valid = new valid();
                  if($valid->email($username) && ($valid->key($key))) {
                       if($action){
                            switch ($action) {  
                                case 'account':
                                      $this->account($config,$key,$action);
                                    break;
                                case 'imeiservicelist':
                                     $this->imeiservicelist($config,$key);
                                    break;
                                case 'product':
                                   $this->product($config,$key,$action,$id=1);
                                    break;
                                case 'modellist':
                                    $this->modellist($config,$key,$action,$id);
                                    break;
                                case 'placeimeiorder':
                                    $this->placeimeiorder($config,$key,$id,$imei,$modelid);
                                    break;
                                case 'getimeiorder':
                                    $this->getimeiorder($config,$imei,$id,$key,$files);
                                    break;
                                case 'meplist':
                                    $this->meplist($config,$key,$action,$id);
                                    break;
                                case 'providerlist':
                                    $this->providerlist($config,$key,$action,$id);
                                    break;
                                default :
                                    echo json_encode(array('ERROR'=>array(array('MESSAGE'=>'Action not found'))));
                                }
                        }
                  }  else {
                      
                      echo json_encode(array('ERROR'=>array(array('MESSAGE'=>'Incorrect username or key.'))));
                  }  
              }

        }         
        
        private function account($config,$key,$action) {
          
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$config[0]['adress'].'/api/'.$action.'.json?key='.$key.'&pretty=true');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            $result = curl_exec($ch);
            $array=json_decode($result);
            if($array->status == 'ok') {
                $resultaccount = array(
                    'ID'=>$array->account->id,
                    'SUCCESS' => array (array(
                        'message'=>'Your Accout Info',
                        'AccoutInfo'=>array(
                            'credit'=>$array->account->credits,
                            'mail'=>$array->account->email,
                            'currency'=>'PLN'
                            )
                          )
                       )
                     );
            }  else {
                $resultaccount =array(
                    'ERROR' =>array(array(
                     'MESSAGE'=>$array->errors[0]->message,
                        )
                    )          
            );
                    $mail = new mail();
                     $mail->send($array->errors[0]->message.'/n'.$_SERVER);
                     exit();
        }
        echo json_encode($resultaccount);
         }
        
        private function imeiservicelist($config,$key) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$config[0]['adress'].'/api/products.json?type=imei&key='.$key.'&pretty=true');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);      
            $result = curl_exec($ch);
            $resultimei = json_decode($result);
            if($resultimei->status == 'ok') {
                     //$imieresult['SUCCESS'][0]['LIST']['SERVICES'];
              for($j=0;$j<$resultimei->count;++$j){ 
                  $imieresult['SUCCESS'][$j]['LIST']
                               = array(
                                   'Nazwa grupy'=>array(
                                   'GROUPNAME'=>$resultimei->products[$j]->name,
                                   'SERVICES'=>array(    
                                    $resultimei->products[$j]->id, 
                                       array(
                                            'SERVICEID'=>$resultimei->products[$j]->id,
                                            'SERVICENAME'=>$resultimei->products[$j]->name,
                                            'CREDIT'=>$resultimei->products[$j]->discount_price,
                                            'TIME'=>$resultimei->products[$j]->delivery_time,
                                            'INFO'=>$resultimei->products[$j]->description,
                                           // Wymagane pola - możliwe wartości: None|Optional|Required
                            'Requires.Network'=>'None', // "Required" jeżeli w odpowiedzi API GSMKody, w polu required pojawia się "network_id"
                            'Requires.Mobile'=>'None', // "Required" jeżeli w odpowiedzi API GSMKody, w polu required pojawia się "model"
                            'Requires.Provider'=>'None', // "Required" jeżeli w odpowiedzi API GSMKody, w polu required pojawia się "provider_id"
                            'Requires.PIN'=>'None', 
                            'Requires.KBH'=>'None', // "Required" jeżeli w odpowiedzi API GSMKody, w polu required pojawia się "sn"
                            'Requires.MEP'=>'None', // "Required" jeżeli w odpowiedzi API GSMKody, w polu required pojawia się "mep"
                            'Requires.PRD'=>'None', // "Required" jeżeli w odpowiedzi API GSMKody, w polu required pojawia się "prd"
                            'Requires.Type'=>'None',
                            'Requires.Locks'=>'None',
                            'Requires.Reference'=>'None',
                            'Requires.SN'=>'None',
                            'Requires.SecRO'=>'None'
                                   
                               )        
                             )
                           )
                        );
                }
                   echo json_encode($imieresult);                  
                }
                        else {                           
                           echo json_encode($imieresult = array(
                                        'ERROR' =>array(array(
                                         'MESSAGE'=>$array->errors[0]->message,
                                        )
                                    )
                               )
                            );
                                $mail = new mail();
                                $mail->send($imieresult->errors[0]->message.'/n'.$_SERVER);
                                exit();
                    }

                   }
                
        function product($config,$key,$action,$id) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$config[0]['adress'].'/api/'.$action.'?id='.$id.'&key='.$key.'&pretty=true');
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            $result = curl_exec($ch);
            $arrayproduct = json_decode($result);
            if($arrayproduct->status == 'ok') {
                $resultarray = array(
                    'SUCCESS'=>'Your Accout Info',
                    'product'=> array(
                     'ID'=>$arrayproduct->product->id,
                     'name'=>$arrayproduct->product->name,
                     'description'=>$arrayproduct->product->description,
                     'standard_price'=>$arrayproduct->product->standard_price,
                     'discount'=>$arrayproduct->product->discount,
                     'discount_price'=>$arrayproduct->product->discount_price,
                     'guarantee_return'=>$arrayproduct->product->guarantee_return,
                     'guarantee_return_price'=>$arrayproduct->product->guarantee_return_price,
                     'delivery_time'=>$arrayproduct->product->delivery_time,
                     'status'=>$arrayproduct->product->status,
                     'required'=> array(
                         $arrayproduct->product->required[0],
                         $arrayproduct->product->required[1],
                     ),
                    )
                );
            }         else {
                        $resultarray = array(
                            array(
                    'ERROR' =>array(
                     'MESSAGE'=>$arrayproduct->errors[0]->message,
                        )
                    )          
            );
                 $mail = new mail();
                 $mail->send($arrayproduct->errors[0]->message.'/n'.$_SERVER);
                 exit();

         }
            echo json_encode($resultarray);
        }
        
        private function modellist($config,$key,$action,$id) {
            /*
             *x`x`  Paweł Kozioł: modellist, meplist i providerlist narazie nie zrobisz
             *  Paweł Kozioł: nie masz jak
             */
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$config[0]['adress'].'/api/products.json?type=imei&key='.$key.'&pretty=true');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);      
            $result = curl_exec($ch);
            $resultimei = json_decode($result);
            if($resultimei->status === 'ok') {
                $result= array(
              'SUCCESS'=>'Your Accout Info',
              'LIST'=>$resultimei->products[0]->required[1],
                    array(array(
                           'ID'=>$resultimei->products[0]->id,
                           'name'=>$resultimei->products[0]->name,
                           'description'=>$resultimei->products[0]->description,
                           'standard_price'=>$resultimei->products[0]->standard_price,
                           'discount'=>$resultimei->products[0]->discount,
                           'discount_price'=>$resultimei->products[0]->discount_price,
                           'guarantee_return'=>$resultimei->products[0]->guarantee_return,
                           'guarantee_return_price'=>$resultimei->products[0]->guarantee_return_price,
                           'delivery_time'=>$resultimei->products[0]->delivery_time,
                    ))
                        );             
            }
            echo json_encode($resultimei);
        }
        
        private function getimeiorder($config,$imei,$id,$key,$files) {         
            if(empty($files['parameters']['name'])){
                echo '123123123123123313';
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL,$config[0]['adress'].'/api/order.json?id='.$id.'&imei='.$imei.'&key='.$key.'&pretty=true');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                    $result = curl_exec($ch);
                    $resultpla = json_decode($result);
                    var_dump($resultpla);
                    if($resultpla->status == 'ok') {
                                $resultarray = $result= array(array(
                              'SUCCESS'=>'Your Accout Info',
                              'IMEI'=>$resultpla->order->imei ,
                                   array(array(
                                        'MESSAGE'=>$resultpla->order->status,
                                        'REFERENCEID'=>$resultpla->order->id,
                                        )
                                        ))
                                );

                            } else {
                              $resultarray = array('STATUS'=>'ERROR',
                                    array(array(
                                        'MESSAGE'=>$resultpla->errors[0]->message,
                                    )));
                                 $mail = new mail();
                                 $mail->send($resultarray->errors[0]->message.'/n'.$_SERVER.'/n'.__METHOD__.'/n'.__LINE__);
            
                         }
                    echo json_encode($resultarray);
            }  else {
                
                $this->getimeiorderfiles($config,$files,$key);
            }
          
        }
      private function placeimeiorder($config,$key,$id,$imei,$modelid) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$config[0]['adress'].'/api/new_order.json?');//&product_id=1000&imei=796969246&pretty=true');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,urlencode('&key='.$key.'&product_id='.$id.'&imei'.$imei.'&pretty=true'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            $result = curl_exec($ch);      
            $array = json_decode($result);

            if($array->status === 'error') {
               $result = array('STATUS'=>'ERROR',
                   'ERROR'=>array(array(
                        'MESSAGE'=>$array->errors[0]->message,
                    )));
            $mail = new mail();
            $mail->send($array->errors[0]->message.'/n'.$_SERVER.'/n'.__METHOD__.'/n'.__LINE__);

            }
               // echo json_encode($result);
                  return $result;
        }
        private function getimeiorderfiles($config,$files,$key) {

            $xml = new xml();
            $result = $xml->getXml($files);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $config[0]['adress'].'/api/order.json?&id='.$result->ID.'&imei='.$result->IMEI.'&key='.$key.'&pretty=true');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            $result = curl_exec($ch);
            $resultpla = json_decode($result);
            if($resultpla->status == 'ok') {
                $resultarray = $result= array(array(
              'SUCCESS'=>'Your Accout Info',
              'IMEI'=>$resultpla->order->imei ,
                    array(
              'ID'=>$resultpla->order->id,
                   array(array(
                        'MESSAGE'=>$resultpla->order->status,
                        'REFERENCEID'=>$resultpla->order->id,
                        ))
                        ))
                );
                exit();
            } else {
              $resultarray = array('STATUS'=>'ERROR',
                    array(array(
                        'MESSAGE'=>$resultpla->errors[0]->message,
                    )));
                        $mail = new mail();
                        $mail->send($resultpla->errors[0]->message.'/n'.$_SERVER.'/n'.__METHOD__.'/'.__LINE__);
                        
         }
            echo json_encode($resultarray);
        }
        
        private function meplist($config,$key,$action,$id) {
            echo 'meplist ';
        }
        
        private function providerlist($config,$key,$action,$id) {
            echo 'providerlist ';
        }
}

try {
$login = new login();
$login->setLogin();
} catch (Exception $ex) {
     echo(json_encode(array('error'=>$ex->getMessage().'wielkosc liter ma znaczenie')));
}
?>