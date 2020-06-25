<?php
/**
 * Paga Business Library.
 *
 * PHP version >=5
 *
 * @category  PHP
 * @package   PagaMerchant
 * @author    PagaDevComm <devcomm@paga.com>
 * @copyright 2020 Pagatech Financials
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      https://packagist.org/packages/paga/paga-merchant
 */
namespace PagaBusiness;
/**
 * PagaBusinessClient  class
 * 
 * @category  PHP
 * @package   PagaMerchant
 * @author    PagaDevComm <devcomm@paga.com>
 * @copyright 2020 Pagatech Financials
 * @license   http://www.php.net/license/3_01.txt  PHP License 3.01
 * @link      https://packagist.org/packages/paga/paga-merchant
 */
class PagaBusinessClient
{

    var $test_server = "https://beta.mypaga.com"; //"http://localhost:8080"
    var $live_server = "https://www.mypaga.com";



    /**
     * __construct function
     *
     * @param mixed[] $builder Builder Object
     */
    function __construct($builder)
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
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array("content-type: application/json", "Accept: application/json","hash:$hash","principal:$this->principal", "credentials: $this->credential"),

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

    // /**
    //  * Undocumented function
    //  *
    //  * @param string $url                       Authorization code url
    //  * @param string $hash                      sha512 encoding of the required 
    //  *                                          parameters and the clientAPI key
    //  * @param JSON   $data                      request body data
    //  * 
    //  * @param string $idPhoto_path              path to idPhoto
    //  * 
    //  * @return $cmd
    //  */
    /**
     * Builder Request Multipart Form Function
     *
     * @param string $url                       Authorization code url
     * @param string $hash                      sha512 encoding of the required
     *                                          parameters and the clientAPI key
     * @param string $customerAccountPhoto_path path to customerAccountPhoto
     * @param string $idPhoto_path              path to idPhoto
     * @param JSON   $data                      request body data
     * 
     * @return $cmd
     */
    public function buildRequestMultpartForm($url, $hash, $customerAccountPhoto_path,
        $idPhoto_path, $data = null
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
            $cmd .= "--form 'customerAccountPhoto=@$customerAccountPhoto_path;type=image/jpeg' \\";
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
     * @param string $data parameters for hashing
     * 
     * @return $hash
     */
    public function createHash($data)
    {

        $hash ="";
        foreach ($data as $key => $value) {
            $hash = $value.$hash;
        }
        $hash=$hash.$this->apiKey;
        $hash = hash('sha512', $hash);

        return $hash;
    }



    /**
     * Get Banks function
     *
     * @param string $reference_number A unique reference number provided by the client to uniquely identify the transaction
     * @param string $locale           The language/locale to be used in messaging.
     * 
     * @return JSON Object with List of Banks integrated with paga
     */
    function getBanks($reference_number, $locale = null)
    {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/getBanks";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number
        );

        $hash = hash('sha512', $reference_number.$this->apiKey);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;

    }

    /**
     * Undocumented function
     *
     * @param string $reference_number A unique reference number provided by the client to uniquely identify the transaction
     * @param string $locale           The language/locale to be used in messaging.
     * 
     * @return JSON Object with List of merchants integrated with paga
     */
    function getMerchants($reference_number, $locale = null)
    {


        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/getMerchants";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number
        );

        $hash = hash('sha512', $reference_number.$this->apiKey);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);

        return $response;

    }


    /**
     * Undocumented function
     *
     * @param string $reference_number A unique reference number provided by the
     *                                 client to uniquely identify the transaction
     * @param string $merchantPublicId The identifier which uniquely identifies  
     *                                 the merchant on the Paga platform.
     * @param string $locale           The language/locale to be used in messaging.
     * 
     * @return JSON Object with List of services of the merchant
     */
    function getMerchantServices($reference_number, $merchantPublicId,
        $locale = null
    ) {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/getMerchantServices";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'merchantPublicId'=>$merchantPublicId
        );

        $hash_string = $reference_number.$merchantPublicId.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;

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
    function getOperationStatus($reference_number,$locale = null)
    {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/getOperationStatus";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number
        );

        $hash_string = $reference_number.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;

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
    function getMobileOperators($reference_number,$locale = null)
    {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/getMobileOperators";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number
        );

        $hash_string = $reference_number.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;

    }


    /**
     * Register Customer function
     *
     * @param string      $reference_number                 
     *                                                      A unique reference number
     *                                                      provided by the business
     *                                                      identifying the transaction.                                              
     * @param string      $customerPhoneNumber              The identifying credential (principal)
     *                                                      for the customer
     *                                                      (eg.phone number)      
     * @param string      $firstName                        The first name of the customer
     * @param string      $lastName                         The last name of the customer
     * @param string      $customerIdType                   The Type of Customer Id: ex.DRIVERS_LICENCE, NATIONAL_ID,PASSPORT, RESIDENT_PERMIT, STUDENT_ID, EMPLOYER_ID, TAX_CARD, NYSC_ID_CARD, SWORN_AFFIDAVIT,VOTERS_CARD, UNION_ID,BVN, CORPORATE_AFFAIRS_COMMISSION  
     * @param string      $customerIdNumber                 The customer Id number of length 10 or 11 characters long.
     * @param string      $customerIdExpirationDate         The expiration date of the CustomerId         
     * @param string      $customerEmail                    Email of the customer
     * @param date:string $customerDateOfBirth              Birth date of the customer           
     * @param string      $customerGender                   gender of the customer(MALE or FEMALE) 
     * @param array       $customerAddress                  Address of the customer consists of
     *                                                      {country, region, county, city, localGovernmentArea, 
     *                                                      streetAddress, postalCode, landmark, freeformAddress}
     * @param string      $customerMaritalStatus            MaritalStatus (Married, Single)
     * @param string      $customerPreferredLanguageISOCode Language en us
     * @param string      $customerReferredByFirstName      First name of the referrer
     * @param string      $customerReferredByLastName       Last name of the referrer
     * @param string      $customerReferredByPhoneNumber    PhoneNumber of the referrer
     * @param boolean     $optionForWalletSavings           flag to option for wallet saving
     * @param string      $passportPhoto_path               The path to the customers Passport photo
     * @param string      $idPhoto_path                     The path to the customers Id photo
     * 
     * @return void
     */
    function registerCustomer($reference_number, $customerPhoneNumber, $firstName, 
        $lastName, $customerIdType, $customerIdNumber, $customerIdExpirationDate,    
        $customerEmail, $customerDateOfBirth, $customerGender, $customerAddress,
        $customerMaritalStatus, $customerPreferredLanguageISOCode,
        $customerReferredByFirstName, $customerReferredByLastName,
        $customerReferredByPhoneNumber, $optionForWalletSavings,
        $passportPhoto_path, $idPhoto_path
    ) {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/registerCustomer";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'customerPhoneNumber'=>$customerPhoneNumber,
            'customerFirstName'=>$firstName,
            'customerLastName'=>$lastName,
            'customerIdType'=>$customerIdType,
            'customerIdNumber'=>$customerIdNumber,
            'customerIdExpirationDate'=>$customerIdExpirationDate,
            'customerEmail'=>$customerEmail,
            'customerDateOfBirth'=>$customerDateOfBirth,
            'customerGender'=>$customerGender,
            'customerAddress'=>$customerAddress,
            'customerMaritalStatus'=>$customerMaritalStatus,
            'customerPreferredLanguageISOCode'=>$customerPreferredLanguageISOCode,
            'customerReferredByFirstName'=>$customerReferredByFirstName,
            'customerReferredByLastName'=>$customerReferredByLastName,
            'customerReferredByPhoneNumber'=>$customerReferredByPhoneNumber,
            'optinForWalletSavings'=>$optionForWalletSavings

        );

        $hash_string = $reference_number.$customerPhoneNumber.$firstName.$lastName.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl_cmd = $this->buildRequestMultpartForm($url, $hash, $data, $passportPhoto_path, $idPhoto_path);

        $response = shell_exec($curl_cmd);

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
    function registerCustomerIdentification($reference_number, $customerPhoneNumber, $customerIdType, 
        $customerIdNumber, $customerIdExpirationDate, $idPhoto_path
    ) {



        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/registerCustomerIdentification";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'customerPhoneNumber'=>$customerPhoneNumber,
            'customerIdType'=>$customerIdType,
            'customerIdNumber'=>$customerIdNumber,
            'customerIdExpirationDate'=>$customerIdExpirationDate

        );


         $hash_string = $reference_number.$customerPhoneNumber.$customerIdType.$customerIdNumber.$customerIdExpirationDate.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl_cmd = $this->buildRequestMultpartForm($url, $hash, $data, null, $idPhoto_path);

        $response = shell_exec($curl_cmd);

        return $response;

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
    function registerCustomerAccountPhoto($reference_number, $customerPhoneNumber,
        $passportPhoto_path
    ) {
        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/registerCustomerAccountPhoto";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'customerPhoneNumber'=>$customerPhoneNumber
        );

        $hash_string = $reference_number.$customerPhoneNumber.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl_cmd = $this->buildRequestMultpartForm($url, $hash, $data, $passportPhoto_path, null);

        $response = shell_exec($curl_cmd);

        return $response;

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
    public function moneyTransfer($referenceNumber,$amount, $destinationAccount,
        $senderPrincipal, $senderCredentials, $currency, $alternateSenderName=null,
        $destinationBank=null, $holdingPeriod=null, $minRecipientKYCLevel=null, 
        $locale=null, $sourceOfFunds=null, $suppressRecipientMessage=null, 
        $transferReference=null, $sendWithdrawalCode=null
    ) {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/moneyTransfer";
        $credential = null;
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

        $hash_string = $referenceNumber.$amount.$destinationAccount.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
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
    public function airtimePurchase($reference_number,$amount, 
        $destinationPhoneNumber
    ) {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/airtimePurchase";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'amount'=>$amount,
            'destinationPhoneNumber'=>$destinationPhoneNumber

        );

        $hash_string = $reference_number.$amount.$destinationPhoneNumber.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;


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

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/accountBalance";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number
        );

        $hash_string = $reference_number.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;


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
    public function depositToBank($reference_number, $amount, $destinationBankUUID, 
        $destinationBankAccountNumber,$recipientPhoneNumber,$currency
    ) {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/depositToBank";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'amount'=>$amount,
            'destinationBankUUID'=>$destinationBankUUID,
            'destinationBankAccountNumber'=>$destinationBankAccountNumber,
            "recipientPhoneNumber"=>$recipientPhoneNumber,
            "currency"=>$currency,
        );

        $hash_string = $reference_number.$amount.$destinationBankUUID.$destinationBankAccountNumber.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
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
    public function validateDepositToBank($reference_number, $amount, $destinationBankUUID, $destinationBankAccountNumber){

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/validateDepositToBank";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'amount'=>$amount,
            'destinationBankUUID'=>$destinationBankUUID,
            'destinationBankAccountNumber'=>$destinationBankAccountNumber
        );

        $hash_string = $reference_number.$amount.$destinationBankUUID.$destinationBankAccountNumber.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
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
    public function moneyTransferBulk($bulkReferenceNumber,$items_arr)
    {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/moneyTransferBulk";
        $credential = null;

        $data = array(
            "bulkReferenceNumber"=>$bulkReferenceNumber,
            "items"=>$items_arr
        );

        $hash_string = $items_arr[0]["referenceNumber"].$items_arr[0]["amount"].$items_arr[0]["destinationAccount"].sizeof($items_arr). $this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
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
    public function merchantPayment($reference_number, $amount, $merchantAccount, $merchantReferenceNumber, $currency, $merchantService){

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/merchantPayment";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'amount'=>$amount,
            'merchantAccount'=>$merchantAccount,
            'merchantReferenceNumber'=>$merchantReferenceNumber,
            "currency" =>$currency,
            "merchantService"=>$merchantService
        );

        $hash_string = $reference_number.$amount.$merchantAccount.$merchantReferenceNumber.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
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

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/transactionHistory";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number
        );

        $hash_string = $reference_number.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
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

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/transactionHistory";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number
        );

        $hash_string = $reference_number.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
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
    public function onboardMerchant($reference,$merchantExternalId,$merchantInfo,
        $integration
    ) {

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/onboardMerchant";
        $credential = null;
        $data = array(
            'reference'=>$reference,
            'merchantExternalId'=>$merchantExternalId,
            'merchantInfo' =>$merchantInfo,
            'integration' =>$integration
        );

        $hash_string = $reference.$merchantExternalId.$merchantInfo["legalEntity"]["name"].$merchantInfo["legalEntityRepresentative"]["phone"].$merchantInfo["legalEntityRepresentative"]["email"].$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;

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

        $server = ($this->test) ? $this->test_server : $this->live_server;
        $url = $server."/paga-webservices/business-rest/secured/validateCustomer";
        $credential = null;
        $data = array(
            'referenceNumber'=>$reference_number,
            'customerIdentifier'=>$customerIdentifier,

        );

        $hash_string = $reference_number.$customerIdentifier.$this->apiKey;

        $hash = hash('sha512', $hash_string);

        $curl = $this->buildRequest($url, $hash, $data);
        $response = curl_exec($curl);
        $this->checkCURL($curl);
        return $response;
    }

    /**
     * Cherck  CURL
     * 
     * @param string $curl CURL
     * 
     * @return void
     */
    public function checkCURL($curl)
    {
        if (curl_errno($curl)) {
            echo 'Curl error: ' . curl_error($curl);
        }

        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        printf("<br/>HTTP Code: " . $httpcode);

        if ($httpcode == 200) {
            printf("SUCCESSFUL");
        }
        //var_dump($response);

        curl_close($curl);
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
    function __construct()
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


?>


