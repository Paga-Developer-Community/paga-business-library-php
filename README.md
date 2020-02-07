# Business Rest API Library
Paga has made it very easy for businesses to accept payments.
This Business Service Library is a PHP module that helps you make API calls when processing Paga Business Transactions.

# Examples
include("PagaBusinessClient.php");
$businessClient = PagaBusinessClient::builder()\
                ->setApiKey("<apiKey>")\
                ->setPrincipal("<publicId>")\
                ->setCredential("<password>")\
                ->setTest(true)\
                ->build();

$response = $businessClient-> getBanks($reference_number);
