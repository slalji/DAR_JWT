<?php
require_once ("config.php");
require_once ("Transactions.php");
/**
 *
 * PHP version 5
 *
 * @modal DB
 * @author   Salma Lalji
 **/
 
class DB extends PDO {

    private $conn =null;
   

    public function __construct() {
        date_default_timezone_set('Africa/Dar_es_Salaam');
        $dsn = 'mysql:dbname=' . DB_NAME . ';host=' . DB_HOST;
        $user = DB_USER;
        $pw = DB_PASS;
        try {
            $this->conn = new PDO( DB_DSN, DB_USER, DB_PASS ); 
            $this->conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            return $this->conn;
        }
        catch(PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();;
        }


    }
    //mysql_query("INSERT INTO incoming(sender,keyword,message) VALUES('$sender','$prefix','$sms')");
    private function getKeyValuePair($obj){
    
        $str="{";
        foreach ($obj as $key => $value){
        $str .= ( ' ,"'.$key .'":"'.$value.'"' ) ;
        }
        return $str."}";
        
    }
    protected function toString($data){
        $params="{";
            foreach($data as $k => $d){
                if(is_object($d)){
                $params .=$k.":";
                    $params .= $this->getKeyValuePair($d);  
                }
                else{
                    $params .= '"'.$k .'":"'.$d.'", ';
                }
            }
            return $params .="}";
    }
    public function incoming($data) {
       
        try{
            $params = $this->toString($data);

            $paramdatetime = date('Y-m-d H:i:s',$data->timestamp);
            $sql= 'INSERT INTO incoming (fulltimestamp, paramtimestamp, method, transId,payload) 
                VALUES (now(), :paramtimestamp, :method, :transId, :payload)';
            $stmt = $this->conn->prepare( $sql );
            $stmt->bindValue( "paramtimestamp", $paramdatetime, PDO::PARAM_STR );            
            $stmt->bindValue( "method", $data->method, PDO::PARAM_STR );
            $stmt->bindValue( "transId", $data->requestParams->transId, PDO::PARAM_STR );
            $stmt->bindValue( "payload", $params , PDO::PARAM_STR);
            $stmt->execute();
            //return $stmt->fetch(PDO::FETCH_ASSOC);
            //$stmt->commit(); 
            //return $stmt->lastInsertId(); 
        }
        catch(PDOExecption $e) { 
            //$stmt->rollback(); 
            print "Error!: " . $e->getMessage() . $sql."</br>".$data;            
        } 
        catch( PDOExecption $e ) { 
            print "Error!: " . $e->getMessage() . $sql. "</br>".$data; 
        } 
        return false;
    }
    public function transaction($data, $method){
        $transaction = new Transactions();
        
        switch($method){
            case 'openAccount': print_r( $transaction->OpenAccount($data)); break;
            case 'updateAccount': print_r($transaction->UpdateAccount($data)); break;
            case 'cashIn': $transaction->CashIn($data); break;
            case 'cashOut': $transaction->CashOut($data); break;
            case 'sendMoney': $transaction->SendMoney($data); break;
            case 'nameLookup': print_r($transaction->NameLookup($data)); break;
            case 'transactionLookup': $transaction->TransactionLookup($data); break;
            case 'biller': $transaction->Biller($data); break;
            case 'requestVCN': $transaction->RequestVCN($data); break;
            case 'linkVCNAccount': $transaction->LinkVCNAccount($data); break;
            case 'disburseLoan': $transaction->DisburseLoan($data); break;
            case 'suspenseAccount': $transaction->SuspenseAccount($data); break;
            case 'closeAccount': $transaction->CloseAccount($data); break;
            case 'activateAccount': $transaction->ActivateAccount($data); break;
            case 'reversal': $transaction->Reversal($data); break;
            case 'exGratiaPayments': $transaction->ExGratiaPayments($data); break;
            case 'BE': $transaction->BE($data); break;
            case 'MS': $transaction->MS($data); break;
            default: print_r('no method found: '.$method);
            
        }

    }
}

?>