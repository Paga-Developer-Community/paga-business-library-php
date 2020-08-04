# PagaBusiness PHP API Library v1.0.3

## Business Services exposed by the library

- registerCustomer
- registerCustomerAccountPhoto
- registerCustomerIdentification
- moneyTransfer
- airtimePurchase
- merchantPayment
- validateDepositToBank
- depositToBank
- accountBalance
- transactionHistory
- recentTransactionHistory
- getMerchants
- getMerchantServices
- getBanks
- getOperationStatus
- getMobileOperators
- onboardMerchant
- moneyTransferBulk

For more information on the services listed above, visit the [Paga DEV website](https://developer-docs.paga.com/docs/php-library-1)

## How to use

`composer require paga/paga-business`

 
```
require_once __DIR__ .'/vendor/autoload.php'


$businessClient = PagaBusinessClient::builder()
                ->setApiKey("<apiKey>")
                ->setPrincipal("<publicId>")
                ->setCredential("<password>")
                ->setTest(true)
                ->build();
```

As shown above, you set the principal and credential given to you by Paga, If you pass true as the value for setIsTest(), the library will use the test url as the base for all calls. Otherwise setting it to false will use the live url value you **pass** as the base. 

### Business Service Functions

**Register Customer**

The Register Customer operation allows 3rd Parties to register customers on Paga. New customers will be contacted to setup their authentication credentials. To make use of this function, call the **registerCustomer** inside the BusinessClient which will return a JSONObject for the registered customer.

```
$response = $businessClient ->registerCustomer(
    "referenceNumber",
    "customerPhoneNumber",
    "customerEmail",
    "customerFirstName",
    "customerLastName",
    "customerDateOfBirth",
    "customerGender",
    $customerAddress,
    "customerMaritalStatus",
    "customerPreferredLanguageISOCode",
    "customerReferredByPhoneNumber",
    "customerReferredByFirstName",
    "customerReferredByLastName",
    "customerIdType",
    "customerIdNumber",
    "customerIdExpirationDate",
    "optinForWalletSavings",
    "customerSupplementaryDetailsNextOfKinLastName",
    "customerSupplementaryDetailsNextOfKinFirstName",
    "customerSupplementaryDetailsNextOfKinType",
    "customerSupplementaryDetailsNextOfKinPhoneNumber"
)

$customerAddress = array(
  "country"=>"Nigeria", 
  "region"=>"Abia", 
  "county"=>"", 
  "city"=>"", 
  "localGovernmentArea"=>"", 
  "streetAddress"=>"test_address", 
  "postalCode"=>"", 
  "landmark"=>"", 
  "freeformAddress"=>""
);

```
**Register Customer Account Photo**

An operation for a business to upload an account photo for a customer account that it has registered. This is useful in case the account photo is not available or the upload fails or is rejected during the initial registration attempt.
To make use of this function, call the **registerCustomerAccountPhoto** function inside the BusinessClient Library which will return a JSONObject.


```
$response = $businessClient ->registerCustomerAccountPhot(
  $reference_number,
  $customerPhoneNumber,
  $passportPhoto_path
);
```
**Register Customer Identification**

An operation for a business to upload an identification photo for a customer account that it has registered.
To make use of this function, call the **registerCustomerIdentification** function inside the BusinessClient Library which will return a JSONObject.


```
$response = $businessClient -> registerCustomerIdentification(
  $reference_number,
  $customerPhoneNumber, 
  $customerIdType, 
  $customerIdNumber,
  $customerIdExpirationDate, 
  $idPhoto_path
);
```
**Money Transfer**

The Money Transfer operation enables an integrated 3rd party to utilize the Paga platform to transfer funds from a variety of sources to another party. The funds transfer may be executed from the accounts of the integrated 3rd party themselves, or on behalf of another customer with the appropriate authentication. The source of funds may be the sender's Paga account or another source that the sender has pre-registered on the Paga platform. 
To make use of this function, call the **moneyTransfer** inside the BusinessClient Library which will return a JSONObject for the money transferred.


```  
$response = $businessClient ->moneyTransfer(
  $referenceNumber,
  $amount, 
  $destinationAccount,
  $senderPrincipal, 
  $senderCredentials,
  $currency
  );

```
**Airtime Purchase**

The Airtime Purchase operation enables an integrated 3rd party to utilize the Paga platform to purchase airtime for any phone number on any of the major networks. The purchase can be funded by the integrated 3rd party themselves, or on behalf of another customer with the appropriate authentication. The source of funds may be the purchaser's Paga account or another source that the sender has pre-registered on the Paga platform. 
To make use of this function, call the **airtimePurchase** function inside the BusinessClient Library which will return a JSONObject for the airtime purchased.


```
$response = $businessClient ->airtimePurchase(
  $referenceNumber,
  $amount,
  $phoneNumber
);
```
**Merchant Payment**

The Merchant Payment operation enables an integrated 3rd party to utilize the Paga platform to make payments to registered merchants. The purchase can be funded by the integrated 3rd party themselves, or on behalf of another customer with the appropriate authentication. The source of funds may be the purchaser's Paga account or another source that the sender has pre-registered on the Paga platform. To make use of this function, call the **merchantPayment** function inside the BusinessClient Library which will return a JSONObject for the merchant payment.


```
$response = $businessClient-> merchantPayment($reference_number, 
  $amount, 
  $merchantAccount, 
  $merchantReferenceNumber, 
  $currency, 
  $merchantService
);
```
**Validate Deposit to Bank**

The Validate Deposit To Bank operation enables an integrated 3rd party to pre-validate a potential deposit to bank operation using similar parameters that would be provided for the actual deposit to bank operation. This will return a result indicating whether the actual deposit to bank operation using the same parameters is likely to be successful or not, and if not, why not. This will also validate the bank account number for the bank provided and return the account holder name for that account as stored at the bank. This will also return any fees that would be charged as part of the actual deposit to bank operation.To make use of this function, call the **validateDepositToBank** function inside the BusinessClient Library which will return a JSONObject for the validate deposit to bank.


```
$response = $businessClient-> validateDepositToBank(
  $reference_number, 
  $amount, 
  $destinationBankUUID, 
  $destinationBankAccountNumber
  );
```
**Deposit To Bank**

The Deposit To Bank operation enables an integrated 3rd party to utilize the Paga platform to deposit funds to any bank account. The funds will be deposited from the businesses Paga account to the bank via the  bank account specified in the operation parameters. To make use of this function, call the **depositToBank** function inside the BusinessClient Library which will return a JSONObject for the deposit to bank.


```
$response = $businessClient-> depositToBank(
  $reference_number, 
  $amount,
  $destinationBankUUID, 
  $destinationBankAccountNumber, 
  $recipientPhoneNumber, 
  $currency
  )
```
**Account Balance**

The Account Balance operation enables an integrated 3rd party to utilize the Paga platform to check the balance of a Paga account or any other account type pre-registered on the Paga platform, which support balance inquiries. The account balance check may be executed for the account(s) of the integrated 3rd party themselves, or on behalf of another customer with the appropriate authentication. To make use of this function, call the **accountBalance** function inside the BusinessClient Library which will return a JSONObject.


```
$response = $businessClient-> accountBalance($reference_number);
```
**Transaction History**

The Transaction History operation enables an integrated 3rd party to utilize the Paga platform to check the transaction history of their Paga account between selected date ranges. The transaction history may be executed on the account(s) of the integrated 3rd party, or on behalf of another customer with the appropriate authentication. Transactions results are limited to the most recent 10,000 results. To make use of this function, call the **transactionHistory** function inside the BusinessClient Library which will return a JSONObject for the transaction history.


```
$response = $businessClient-> transactionHistory($reference_number);
```
**Recent Transaction History**

The Recent Transaction History operation enables an integrated 3rd party to utilize the Paga platform to check the last 5 transactions on their Paga account. The  recent transaction history may be executed for the account(s) of the integrated 3rd party or on behalf of another customer with the appropriate authentication. To make use of this function, call the **recentTransactionHistory** function inside the BusinessClient Library which will return a JSONObject for the recent transaction history.


```
$response = $businessClient-> recentTransactionHistory($reference_number);
```
**Get Merchants**

The Get Merchants operation enables an integrated 3rd party to utilize the Paga platform to obtain a list of registered merchants on the Paga platform, typically for use in parameterizing the merchant payment operation. To make use of this function, call the **getMerchants** function inside the BusinessClient Library which will return a JSONObject.


```
$response = $businessClient-> getMerchants($reference_number);
```
**Get Merchant Services**

The Get Merchants Services operation enables an integrated 3rd party to utilize the Paga platform to obtain a list of registered services and service details for a given registered merchant on the Paga platform, typically for use in parameterizing the merchant payment operation. To make use of this function, call the **getMerchantServices** function inside the BusinessClient Library which will return a JSONObject.


```
$response = $businessClient-> getMerchantServices(
  $reference_number, 
  $merchantPublicId
  );
```
**Get Banks**

The Get Banks operation enables an integrated 3rd party to utilize the Paga platform to obtain a list of available banks on the Paga platform, typically for use in parameterizing the deposit to bank operation. To make use of this function, call the **getBanks** function inside the BusinessClient Library which will return a JSONObject.


```
$response = $businessClient-> getBanks($reference_number);
```
**Get Operation Status**

The Get Operation Status operation allows an integrated 3rd party to check on the status of a previous operation using the operation's reference number. To make use of this function, call the **getOperationStatus** function inside the BusinessClient Library which will return a JSONObject.


```
$response = $businessClient -> getOperationStatus($reference_number);
  
```
**Get Mobile Operators**

The Get Mobile Operators operation enables an integrated 3rd party to utilize the Paga platform to obtain a list of available mobile operators on the Paga platform, typically for use in parameterizing the various operations of the business api. To make use of this function, call the **getMobileOperators** function inside the BusinessClient Library which will return a JSON Object .


```
$response = $businessClient -> getMobileOperators($reference_number);
```
**Bulk Money Transfer**

The Bulk Money Transfer operation enables an integrated 3rd party to utilize the Paga platform to execute the money transfer operation described above to multiple recipients simultaneously. This is limited to 300 payment items per bulk operation. To make use of this function, call the **moneyTransferBulk** function inside the BusinessClient Library which will return a JSON Object.


```
$bulkReferenceNumber = "ref-123";
$items_arr = array(
                   array('referenceNumber'=>$referenceNumber_1,
                        'amount'=>$amount_1,
                        'destinationAccount'=>$phonenumber_1,
                        'senderPrincipal'=>$senderPrincipal_1,
                        'senderCredentials'=>$senderCredentials_1,
                        'currency'=>$currency_1
                   ),

                   array('referenceNumber'=>$referenceNumber_2,
                         'amount'=>$amount_2,
                         'destinationAccount'=>$phonenumber_2,
                         'senderPrincipal'=>$senderPrincipal_2,
                         'senderCredentials'=>$senderCredentials_2,
                         'currency'=>$currency_2
                   )
						);
$response = $businessClient -> moneyTransferBulk($bulkReferenceNumber, $items_arr);
```
Click  [here](https://mypaga.readme.io/docs/business-rest-api-operations#section-18-bulk-money-transfer) for more info on **MoneyTransferItem** 

**Onboard Merchant**

The Onboard Merchant operation, allows Aggregator Organisations to create sub organisations on the paga platform.

**This operation is only available to Merchant aggregators on the Paga platform.**

```
$legalEntity = array (
    "name"=> "",
    "description"=>"",
    "addressLine1"=>"",
    "addressLine2"=>"",
    "addressCity"=>"",
    "addressState"=>"",
    "addressZip"=>"",
    "addressCountry"=>"");

$legalEntityRepresentative = array (
    "firstName"=>"",
    "lastName"=>"",
    "dateOfBirth"=>"",
    "phone"=>"",
    "email"=>""
);

$integration = array(
    "type"=>""
);

$merchantInfo = array( "legalEntity"=> legalEntity, "legalEntityRepresentative"=> $legalEntityRepresentative);

$response = $businessClient_test -> onboardMerchant($reference, $merchantExternalId, $merchantInfo, $integration);

```
Click [here](https://mypaga.readme.io/docs/business-rest-api-operations#section-17-onboard-merchant) to get more info on **OnboardMerchant**.

![Packagist Downloads](https://img.shields.io/packagist/dm/paga/paga-business?style=plastic)
![Packagist License](https://img.shields.io/packagist/l/paga/paga-business?style=plastic)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/paga/paga-business?style=plastic)
![Packagist Version](https://img.shields.io/packagist/v/paga/paga-business?style=plastic)
