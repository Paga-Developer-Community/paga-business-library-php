<?php
/**
 * Paga Business Library.
 *
 * PHP version >=5
 *
 * @category  PHP
 * @package   PagaBusiness
 * @author    PagaDevComm <devcomm@paga.com>
 * @copyright 2020 Pagatech Financials
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      https://packagist.org/packages/paga/paga-business
 */

namespace PagaBusiness;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


$logger = new Logger('stderr');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));


/**
 * PagaBusinessClient  class
 *
 * @category  PHP
 * @package   PagaBusiness
 * @author    PagaDevComm <devcomm@paga.com>
 * @copyright 2020 Pagatech Financials
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      https://packagist.org/packages/paga/paga-business
 */
class PagaBusinessClient
{
    public $test_server = "https://beta.mypaga.com"; 
    public $live_server = "https://www.mypaga.com";



    /**
     * __construct function
     *
     * @param object $builder Builder Object
     */
    public function __construct($builder)
    {
        $this->apiKey =$builder->apiKey;
        $this->principal = $builder->principal;
        $this->credential = $builder->credential;
        $this->test = $builder->test;
    }

    /**
     * Builder function
     *
     * @return new Builder()
     */
    public static function builder()
    {
        return new Builder();
    }


    /**
     * BuildRequest function
     *
     * @param string  $url  Authorization code url
     * @param string  $hash sha512 encoding of the required parameters
     *                      and the clientAPI key
     * @param mixed[] $data request body data
     *
     * @return $curl
     */
    public function buildRequest($url, $hash, $data = null)
    {
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array("content-type: application/json",
            "Accept: application/json","hash:$hash","principal:$this->principal",
            "credentials: $this->credential"),

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT => 120
            )
        );

        if ($data != null) {
            $data_string = json_encode($data);

            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        }

        return $curl;
    }

    /**
     * Builder Request Multipart Form Function
     *
     * @param string $url                       Authorization code url
     * @param string $hash                      sha512 encoding of the required
     *                                          parameters and the clientAPI key
     * @param JSON   $data                      request body data
     * @param string $customerAccountPhoto_path path to customerAccountPhoto
     * @param string $idPhoto_path              path to idPhoto
     * 
     * @return $cmd
     */
    public function buildRequestMultpartForm(
        $url,
        $hash,
        $data = null,
        $customerAccountPhoto_path = null,
        $idPhoto_path = null
    ) {
        if ($data != null) {
            $data_string = json_encode($data);
        }

        $cmd = "curl --request POST --url $url \
                    --header 'content-type: multipart/form-data' \
                    --header  'hash:$hash' \
                    --header 'credentials: $this->credential' \
                    --header 'principal: $this->principal' \
                    --form 'customer=$data_string;type=application/json' \\";


        if ($customerAccountPhoto_path!=null) {
            $cmd .= "--form 'customerAccountPhoto=@$customerAccountPhoto_path;
            type=image/jpeg' \\";
        }

        if ($idPhoto_path!=null) {
            $cmd .=   "--form 'customerIdPhoto=@$idPhoto_path;type=image/jpeg' \\";
        }
            

        if ($customerAccountPhoto_path!=null && $idPhoto_path!=null) {
            $cmd .=   "--form 'isSubsidiary=true;type=application/json'";
        }
           
        return $cmd;
    }

  
    /**
     * Create Hash  function
     *
     * @param array $data parameters for hashing
     * 
     * @return $hash
     */
    public function createHash($data)
    {
        $hash ="";
        foreach ($data as $key => $value) {
            $hash .= $value;
        }
        $hash=$hash.$this->apiKey;
        $hash = hash('sha512', $hash);

        return $hash;
    }



    /**
     * Get Banks function
     *
     * @param string $reference_number A unique reference number provided by
     *                                 the clientto uniquely identify the transaction
     * @param string $locale           The language/locale to be used in messaging.
     *
     * @return JSON Object with List of Banks integrated with paga
     */
    public function getBanks($reference_number, $locale = null)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured/getBanks";
            $data = array(
                'referenceNumber'=>$reference_number
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Merchants function
     *
     * @param string $reference_number A unique reference number provided by the
     *                                 client to uniquely identify the transaction
     * @param string $locale           The language/locale to be used in messaging.
     *
     * @return JSON Object with List of merchants integrated with paga
     */
    public function getMerchants($reference_number, $locale = null)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured/getMerchants";
            $data = array(
            'referenceNumber'=>$reference_number
            );
            $hash =$this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Get Merchant Services function
     *
     * @param string $reference_number A unique reference number provided by the
     *                                 client to uniquely identify the transaction
     * @param string $merchantPublicId The identifier which uniquely identifies
     *                                 the merchant on the Paga platform.
     * @param string $locale           The language/locale to be used in messaging.
     *
     * @return JSON Object with List of services of the merchant
     */
    public function getMerchantServices(
        $reference_number,
        $merchantPublicId,
        $locale = null
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/getMerchantServices";
            $data = array(
                'referenceNumber'=>$reference_number,
                'merchantPublicId'=>$merchantPublicId
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
            
        }
    }



    /**
     * Get Operation Status function
     *
     * @param string $reference_number A unique reference number provided by the
     *                                 client to uniquely identify the transaction
     * @param string $locale           The language/locale to be used in messaging.
     *
     * @return JSON Object with the details of the transaction.
     */
    public function getOperationStatus($reference_number, $locale = null)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/getOperationStatus";
            $data = array(
                'referenceNumber'=>$reference_number
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Mobile Operators function
     *
     * @param string $reference_number A unique reference number provided by the
     *                                 client to uniquely identify the transaction
     * @param string $locale           The language/locale to be used in messaging.
     *
     * @return JSON Object with List of mobile operators integrated with paga.
     */
    public function getMobileOperators($reference_number, $locale = null)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/getMobileOperators";
            $data = array(
                'referenceNumber'=>$reference_number
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Register Customer function
     *
     * @param string      $reference_number    A unique reference number
     *                                         provided by the business identifying
     *                                         the transaction.                                              
     * @param string      $customerPhoneNumber The identifying credential (principal)
     *                                         for the customer
     *                                         (e.g.phone number)      
     * @param string      $customerFirstName   The first name of the customer
     * @param string      $customerLastName    The last name of the customer
     * @param string      $customerEmail       Email of the customer
     * @param date:string $customerDateOfBirth Birth date of the customer         
     * 
     * @return void
     */
    function registerCustomer($reference_number, $customerPhoneNumber, 
        $customerFirstName,$customerLastName, $customerEmail, $customerDateOfBirth
    ) {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/registerCustomer";
        $data = array(
            'referenceNumber'=>$reference_number,
            'customerPhoneNumber'=>$customerPhoneNumber,
            'customerFirstName'=>$customerFirstName,
            'customerLastName'=>$customerLastName,
            'customerEmail'=>$customerEmail,
            'customerDateOfBirth'=>$customerDateOfBirth
        );

        $hash_string = $reference_number.
            $customerPhoneNumber.$customerFirstName.$customerLastName.$this->apiKey;

        $hash = hash('sha512', $hash_string);
        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl, json_decode($response, true));
        return $response;

    }

      

    /**
     * Register customer Identification
     *
     * @param string      $reference_number         A unique reference number provided by the business,
     *                                              identifying the transaction. This reference number will be
     *                                              preserved on the Paga platform to reconcile the operation
     *                                              across systems and will be returned in the response
     * @param string      $customerPhoneNumber      The identifying credential (principal) for the customer (eg.
     *                                              phone number). This will be checked against the Paga
     *                                              system to determine if the account belongs to an existing user
     * @param string      $customerIdType           The IdentificationType of customer(eg. EMPLOYER_ID, STUDENT_ID)
     * @param string      $customerIdNumber         The Id Number of the customer
     * @param date:string $customerIdExpirationDate Expiration date of the customer iD
     * @param String      $idPhoto_path             path of the customer id photo
     *
     * @return JSONObject
     */
    public function registerCustomerIdentification(
        $reference_number,
        $customerPhoneNumber,
        $customerIdType,
        $customerIdNumber,
        $customerIdExpirationDate,
        $idPhoto_path
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/registerCustomerIdentification";
            $data = array(
                'referenceNumber'=>$reference_number,
                'customerPhoneNumber'=>$customerPhoneNumber,
                'customerIdType'=>$customerIdType,
                'customerIdNumber'=>$customerIdNumber,
                'customerIdExpirationDate'=>$customerIdExpirationDate
            );

            $hash_string= array(
                $reference_number,
                $customerPhoneNumber,
                $customerIdType,
                $customerIdNumber,
                $customerIdExpirationDate
            );

            $hash = $this->createHash($hash_string);

            $curl_cmd = $this->buildRequestMultpartForm(
                $url, $hash, $data, null, $idPhoto_path
            );

            $response = shell_exec($curl_cmd);

            $logger = new Logger('stderr');
            $logger->pushHandler(new StreamHandler('php://stderr'));
            $logger->info('response:', [json_decode($response, true)]);
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Register customer Account Photo
     *
     * @param string $reference_number    A unique reference number provided by the business,
     *                                    identifying the transaction. This reference number will be
     *                                    preserved on the Paga platform to reconcile the operation
     *                                    across systems and will be returned in the response.
     * @param string $customerPhoneNumber The identifying credential (principal) for the customer (eg.
     *                                    phone number). This will be checked against the Paga
     *                                    system to determine if the account belongs to an existing user
     * @param string $passportPhoto_path  The path to the customers account photo
     *
     * @return JSONObject
     */
    public function registerCustomerAccountPhoto(
        $reference_number,
        $customerPhoneNumber,
        $passportPhoto_path
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/registerCustomerAccountPhoto";
            $data = array(
                'referenceNumber'=>$reference_number,
                'customerPhoneNumber'=>$customerPhoneNumber
            );
            $hash = $this->createHash($data);
            $curl_cmd = $this->buildRequestMultpartForm(
                $url, $hash, $data, $passportPhoto_path, null
            );
            $response = shell_exec($curl_cmd);
            $logger = new Logger('stderr');
            $logger->pushHandler(new StreamHandler('php://stderr'));
            $logger->info('response:', [json_decode($response, true)]);
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Money Transfer
     *
     * @param string  $referenceNumber          A unique reference number provided by the client to uniquely identify the transaction
     * @param string  $amount                   The amount to be sent
     * @param string  $destinationAccount       The recipient identifier(ex.Phone number)
     * @param string  $senderPrincipal          The username of the sender user
     * @param string  $senderCredentials        The password of the send user
     * @param string  $currency                 The currency to be used(e.g,NGN)
     * @param string  $alternateSenderName      alternative name-of-sender
     * @param string  $destinationBank          For money transfers to a bank account, this is the destination bank code
     * @param string  $holdingPeriod            The number of days with which the recipient's KYC must have before it is reverted back to the sender.
     * @param string  $minRecipientKYCLevel     The minimum target KYC level the money transfer transaction recipient's paga account must have
     * @param string  $locale                   The language/locale to be used in messaging.
     * @param string  $sourceOfFunds            The name of a source account for funds.
     * @param boolean $suppressRecipientMessage Whether to prevent sending an SMS to the recipient of the money transfer.
     * @param string  $transferReference        The name of a source account for funds
     * @param boolean $sendWithdrawalCode       Defaults to true  this indicates whether confirmation messages for funds sent to non Paga customers will include the withdrawal cod
     *
     * @return JSON Object
     */
    public function moneyTransfer(
        $referenceNumber,
        $amount,
        $destinationAccount,
        $senderPrincipal,
        $senderCredentials,
        $currency,
        $alternateSenderName=null,
        $destinationBank=null,
        $holdingPeriod=null,
        $minRecipientKYCLevel=null,
        $locale=null,
        $sourceOfFunds=null,
        $suppressRecipientMessage=null,
        $transferReference=null,
        $sendWithdrawalCode=null
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured/moneyTransfer";
            $data = array(
            'referenceNumber'=>$referenceNumber,
            'amount'=>$amount,
            'destinationAccount'=>$destinationAccount,
            'senderPrincipal'=>$senderPrincipal,
            'senderCredentials'=>$senderCredentials,
            'currency'=>$currency,
            'destinationBank'=>$destinationBank,
            'sendWithdrawalCode'=>$sendWithdrawalCode,
            'transferReference'=>$transferReference,
            'sourceOfFunds'=>$sourceOfFunds,
            'suppressRecipientMessage'=>$suppressRecipientMessage,
            'locale'=>$locale,
            'alternateSenderName'=>$alternateSenderName,
            'minRecipientKYCLevel'=>$minRecipientKYCLevel,
            'holdingPeriod'=>$holdingPeriod

            );
            $hash_string = array($referenceNumber,$amount,$destinationAccount);
            $hash = $this->createHash($hash_string);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Airtime Purchase
     *
     * @param string $reference_number       A unique reference number provided by the client to uniquely identify the transaction.
     * @param string $amount                 The amount airtime to be purchased.
     * @param string $destinationPhoneNumber The phone number to which the airtime is purchased
     *
     * @return JSON Object
     */
    public function airtimePurchase(
        $reference_number,
        $amount,
        $destinationPhoneNumber
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/airtimePurchase";
            $data = array(
                    'referenceNumber'=>$reference_number,
                    'amount'=>$amount,
                    'destinationPhoneNumber'=>$destinationPhoneNumber
                );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * Account Balance
     *
     * @param string $reference_number A unique reference number provided by the client to uniquely identify the transaction
     *
     * @return JSON Object with the account balance details
     */
    public function accountBalance($reference_number)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured/accountBalance";
            $data = array(
                    'referenceNumber'=>$reference_number
                );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    /**
     * Deposit to Bank
     *
     * @param string $reference_number             A unique reference number provided by the business, identifying the transaction.
     * @param string $amount                       The amount
     * @param string $destinationBankUUID          The bank UUID.
     * @param string $destinationBankAccountNumber The bank account number to which the money is deposited.
     * @param string $recipientPhoneNumber         Phone number of recipient user
     * @param string $currency                     The currency to be used(eg,NGN)
     *
     * @return JSON Object
     */
    public function depositToBank(
        $reference_number,
        $amount,
        $destinationBankUUID,
        $destinationBankAccountNumber,
        $recipientPhoneNumber,
        $currency
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured/depositToBank";
            $data = array(
                'referenceNumber'=>$reference_number,
                'amount'=>$amount,
                'destinationBankUUID'=>$destinationBankUUID,
                'destinationBankAccountNumber'=>$destinationBankAccountNumber,
                "recipientPhoneNumber"=>$recipientPhoneNumber,
                "currency"=>$currency,
            );
            $hash_string = array($reference_number,$amount,$destinationBankUUID,
            $destinationBankAccountNumber);
            $hash = $this->createHash($hash_string);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return  $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Validate Deposit to Bank
     *
     * @param string $reference_number             A unique reference number provided by the business, identifying the transaction.
     * @param string $amount                       The amount
     * @param string $destinationBankUUID          The bank UUID.
     * @param string $destinationBankAccountNumber The bank account number to which the money is deposited.
     *
     * @return JSON Object
     */
    public function validateDepositToBank(
        $reference_number,
        $amount,
        $destinationBankUUID,
        $destinationBankAccountNumber
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/validateDepositToBank";
            $data = array(
                'referenceNumber'=>$reference_number,
                'amount'=>$amount,
                'destinationBankUUID'=>$destinationBankUUID,
                'destinationBankAccountNumber'=>$destinationBankAccountNumber
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

  

    /**
     * Money Transfer Bulk function
     *
     * @param string  $bulkReferenceNumber A unique bulk reference number provided by the business, identifying the transaction.
     * @param mixed[] $items_arr           Parameters of items_arr
     *
     * @property string $reference_number   unique number identifies the transaction
     * @property string $amount             the amount to be transferred
     * @property string $destinationAccount account number of the receiver(e.g.receiver phone number)
     * @property string $senderPrincipal    sender user name
     * @property string $currency           the currency used in the transaction (e.g.NGN)
     *
     * @return JSON Object
     */
    public function moneyTransferBulk($bulkReferenceNumber, $items_arr)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/moneyTransferBulk";

            $data = array(
                "bulkReferenceNumber"=>$bulkReferenceNumber,
                "items"=>$items_arr
            );

            $hash_string = array($items_arr[0]["referenceNumber"],
            $items_arr[0]["amount"],
            $items_arr[0]["destinationAccount"], sizeof($items_arr));
            $hash = $this->createHash($hash_string);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Merchant Payment
     *
     * @param string $reference_number        A unique reference number provided by the business, identifying the transaction.
     * @param double $amount                  The amount of the merchant payment
     * @param string $merchantAccount         The account identifying the merchant (eg. merchant Id, UUID, name).
     * @param string $merchantReferenceNumber The account/reference number identifying the customer on the merchant's system.
     * @param string $currency                The currency to be used(ex.NGN)
     * @param string $merchantService         Array of the services provided by the merchant
     *
     * @return JSON Object
     */
    public function merchantPayment(
        $reference_number,
        $amount,
        $merchantAccount,
        $merchantReferenceNumber,
        $currency,
        $merchantService
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured/merchantPayment";
            $data = array(
                'referenceNumber'=>$reference_number,
                'amount'=>$amount,
                'merchantAccount'=>$merchantAccount,
                'merchantReferenceNumber'=>$merchantReferenceNumber,
                "currency" =>$currency,
                "merchantService"=>$merchantService
            );
    
            $hash_string = array($reference_number,$amount,$merchantAccount,
            $merchantReferenceNumber);
            $hash = $this->createHash($hash_string);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Transaction History
     *
     * @param string $reference_number A unique reference number identifying the transaction.
     *
     * @return JSON Object
     */
    public function transactionHistory($reference_number)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/transactionHistory";
            $data = array(
                'referenceNumber'=>$reference_number
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Recent Transaction History
     *
     * @param string $reference_number A unique reference number identifying the transaction.
     *
     * @return JSON Object
     */
    public function recentTransactionHistory($reference_number)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/transactionHistory";
            $data = array(
                'referenceNumber'=>$reference_number
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Onboard Merchant
     *
     * @param string  $reference          A unique reference number provided by the business, identifying the transaction. This reference number will be preserved on the Paga platform to reconcile the operation across systems and will be returned in the response
     * @param string  $merchantExternalId A unique reference number provided by the business, identifying the specific Organization account to be created.
     * @param mixed[] $merchantInfo       Containing information about the Organization to be created.
     * @param mixed[] $integration        Contains information about the type of notification to be used for notification of received payments.
     *
     * @return Json
     */
    public function onboardMerchant(
        $reference,
        $merchantExternalId,
        $merchantInfo,
        $integration
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured/onboardMerchant";
            $data = array(
                'reference'=>$reference,
                'merchantExternalId'=>$merchantExternalId,
                'merchantInfo' =>$merchantInfo,
                'integration' =>$integration
            );

            $hash_string = array($reference.$merchantExternalId,
            $merchantInfo["legalEntity"]["name"],
            $merchantInfo["legalEntityRepresentative"]["phone"],
            $merchantInfo["legalEntityRepresentative"]["email"]);

            $hash = $this->createHash($hash_string);

            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Validate Customer
     *
     * @param String $reference_number   A unique reference number provided by the business, identifying the transaction.
     *                                   This reference number will be preserved on the Paga platform to reconcile the operation across systems and will
     *                                   be returned in the response
     * @param String $customerIdentifier The value that identifies the user(ex. Phonenumber, email)
     *
     * @return string JSON Object identifies the user
     */
    public function validateCustomer($reference_number, $customerIdentifier)
    {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/validateCustomer";
            $data = array(
                'referenceNumber'=>$reference_number,
                'customerIdentifier'=>$customerIdentifier,
            );
            $hash = $this->createHash($data);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

        /**
         * Register Persistent Payment Account function
         *
         * @param string $reference_number A unique reference number for this request
         *                                 .This same reference number will be returned 
         *                                 in the response.                        
         * @param string $phone_number     The phone number of the customer.
         * @param string $account_name     The acount name of your customer.
         * @param string $first_name       The first name of the your customer.
         * @param string $last_name        The last name of the your customer.
         * @param string $financial_identification_number
         *                                 The customer's Bank verification Number (BVN).
         * @param string $email            The acount name of your customer.
         * @param string $account_Reference     This is a unique reference number provided by the Organization which identifies the persistent account Number. It should have a minimum length of 12 characters and a maximum length of 30 characters
         *
         * @return JSON Object with List of Banks integrated with paga
         */
    public function registerPersistentPaymentAccount($reference_number, 
        $phone_number, $account_name,$first_name, $last_name, 
        $financial_identification_number, $email, $account_Reference
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server.
            "/paga-webservices/business-rest/secured/registerPersistentPaymentAccount";
            $data = array(
                'referenceNumber'=>$reference_number,
                'phoneNumber'=>$phone_number,
                'accountName'=>$account_name,
                'firstName'=>$first_name, 
                'lastName'=>$last_name, 
                'financialIdentificationNumber'=>$financial_identification_number,
                'email'=>$email,
                'accountReference'=>$account_Reference
            );
            $hash_string= array(
                $reference_number,
                $phone_number,
                        
            );
            $hash = $this->createHash($hash_string);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Persistent Payment Account Activity function
     *
     * @param string $reference_number             A unique reference number for this request
     *                                             .This same reference number will be returned 
     *                                             in the response.                        
     * @param string $account_number               A valid Persistent Payment Account Number..
     * @param string $get_last_single_activity     A flag if set to true would return only the last activity on the Persistent Payment Account
     * @param string $start_date                   The start date for which records are to be returned.
     * @param string $end_date                     The end of the time frame for the records to be returned.
     * @param string $account_Reference     This is a unique reference number provided by the Organization which identifies the persistent account Number. It should have a minimum length of 12 characters and a maximum length of 30 characters
     *
     * @return JSON Object with List of Banks integrated with paga
     */
    public function getPersistentPaymentAccountActivity($reference_number, $account_number, 
        $get_last_single_activity, $start_date, $end_date, $account_reference
    ) {
        try {
            $server = ($this->test) ? $this->test_server : $this->live_server;
            $url = $server."/paga-webservices/business-rest/secured
                            /getPersistentPaymentAccountActivity";
            $data = array(
            'referenceNumber'=>$reference_number,
            'accountNumber' =>$account_number,
            'getLatestSingleActivity'=>$get_last_single_activity,
            'startDate' => $start_date,
            'endDate'=> $end_date,
            'accountReference' => $account_reference

            );

            $hash_string= array(
                $reference_number
            
            );
            $hash =$this->createHash($hash_string);
            $curl = $this->buildRequest($url, $hash, $data);
            $response = curl_exec($curl);
            $this->checkCURL($curl, json_decode($response, true));
            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Cherck  CURL
     *
     * @param string $curl     CURL
     * @param object $response API response
     *
     * @return void
     */
    public function checkCURL($curl, $response)
    {
        $logger = new Logger('stderr');
        $logger->pushHandler(new StreamHandler('php://stderr'));
        if (curl_errno($curl)) {
            return $logger->error('response: '.curl_error($response));
        }

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($httpcode == 200) {
            return $logger->info('response:', [$response]);
        }

        return curl_close($curl);
    }
}

/**
 * Builder Class
 *
 * @category  PHP
 * @package   PagaMerchant
 * @author    PagaDevComm <devcomm@paga.com>
 * @copyright 2020 Pagatech Financials
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      https://packagist.org/packages/paga/paga-merchant
 */
class Builder
{

    /**
     * __construct
     */
    public function __construct()
    {
    }

    /**
     * Set API Key function
     *
     * @param string $apiKey Merchant api key
     *
     * @return void
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Set Principal function
     *
     * @param string $principal Merchant public ID from paga
     *
     * @return void
     */
    public function setPrincipal($principal)
    {
        $this->principal = $principal;
        return $this;
    }


    /**
     * Set Credential function
     *
     * @param string $credential Merchant password from paga
     *
     * @return void
     */
    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }


    /**
     * Set Test function
     *
     * @param string $test test to set testing or live(true for test,false for live)
     *
     * @return void
     */
    public function setTest($test)
    {
        $this->test = $test;
        return $this;
    }

    /**
     * Build function
     *
     * @return void
     */
    public function build()
    {
        return new PagaBusinessClient($this);
    }
}



