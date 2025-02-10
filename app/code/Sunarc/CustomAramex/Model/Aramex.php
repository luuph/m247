<?php
/**
Description:  Aramex Shipping Magento2 plugin
Version:      1.0.0
Author:       aramex.com
Author URI:   https://www.aramex.com/solutions-services/developers-solutions-center
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */
namespace Sunarc\CustomAramex\Model;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\Xml\Security;
use \Magento\Customer\Model\Session;



/**
 * Class Aramex shipping
 */
class Aramex extends \Aramex\Shipping\Model\Aramex
{
    public $domesticmethods;

    /**
     * object \Aramex\Shipping\Model\Carrier\Aramex\Source\Internationalmethods
     * @var \Aramex\Shipping\Model\Carrier\Aramex\Source\Internationalmethods
     */
    public $internationalmethods;
    public $helper;

    public $jordonCityArray = array("عجلون" => "Ajloon","الهاشمية" => "Al Hashmyeh","الجفر" => "Al Jafer","حدود العمري" => "Al Omari Borders","القصر" => "Al Qaser","القسطل" => "Al Qastal","الرصيفة" => "Al Rosaifa","السخنة" => "Al Sukhneh","عمان" => "Amman","العقبة" => "Aqaba","الازرق" => "Azraq","بيرين" => "Bereian","دير علا" => "Der Allah","المنطقة الحرة" => "Free Zone"," الفحيص" => "Fuhais","الغور " => "Ghour","غور الصافي" => "Ghour Al Safi","الغويرية" => "Ghweria","إربد" => "Irbid","جرش" => "Jerash","الكرك" => "Karak","الخالدية" => "Khaldieh","معان" => "Ma'An","مأدبا" => "Madaba","معين" => "Maean","المفرق" => "Mafraq","ماحص" => "Mahes","مؤتةا" => "Moatah","مخيم حطين" => "Moghayam Hetein","الموقر" => "Mwaqar","ناعور" => "Naour","البترا" => "Petra","القويرية" => "Qwaireh","الرمثا" => "Ramtha","الرشادية" => "Rashadyeh","الرويشد" => "Rwaished","السلط" => "Salt","الشوبك" => "Shoubak","الشونة" => "Shouneh","الطفيلة" => "Tafileh","ذيبان" => "Theban"," وادي موسى" => "Wadi Mousa","يجوز" => "Yajoz","الزرقاء" => "Zarqa","الزرقاء الجديدة" => "Zarqa' Al Jadedeh");

    public $CityArrayWW = array(
        'AF'=>'Kabul',
        'AX'=>'Mariehamn',
        'AL'=>'Tirana',
        'DZ'=>'Algiers',
        'AS'=>'Pago Pago',
        'AD'=>'Andorra la Vella',
        'AO'=>'Luanda',
        'AI'=>'The Valley',
        'AQ'=>'Maxwell Bay',
        'AG'=>'Saint John s',
        'AR'=>'Buenos Aires',
        'AM'=>'Yerevan',
        'AW'=>'Oranjestad',
        'AU'=>'Canberra',
        'AT'=>' Vienna',
        'AZ'=>'Baku',
        'BS'=>'Nassau',
        'BH'=>'Manama',
        'BD'=>'Dhaka',
        'BB'=>'Bridgetown',
        'BY'=>'Minsk',
        'BE'=>'Brussels',
        'BZ'=>'Belmopan',
        'BJ'=>'Cotonou',
        'BM'=>'Hamilton',
        'BT'=>'Thimphu',
        'BO'=>'La Paz; Sucre',
        'BA'=>'Sarajevo',
        'BW'=>'Gaborone',
        'BV'=>'Bouvet Island',
        'BR'=>'Brasilia',
        'IO'=>'Camp Justice',
        'VG'=>'Road Town',
        'BN'=>'Bandar Seri Begawan',
        'BG'=>'Sofia',
        'BF'=>'Ouagadougou',
        'BI'=>'Gitega ',
        'KH'=>'Phnom Penh',
        'CM'=>'Yaounde',
        'CA'=>'Ottawa',
        'CV'=>'Praia',
        'KY'=>'George Town',
        'CF'=>'Bangui',
        'TD'=>'N Djamena',
        'CL'=>'Santiago',
        'CN'=>'Beijing',
        'CX'=>'Flying Fish Cove',
        'CC'=>'West Island',
        'CO'=>'Bogota',
        'KM'=>'Moroni',
        'CG'=>'Brazzaville',
        'CD'=>'Kinshasa',
        'CK'=>'Avarua',
        'CR'=>'San Jose',
        'CI'=>'Abidjan',
        'HR'=>'Zagreb',
        'CU'=>'Havana',
        'CY'=>'Nicosia',
        'CZ'=>'Prague',
        'DK'=>'Copenhagen',
        'DJ'=>'Djibouti',
        'DM'=>'Roseau',
        'DO'=>'Santo Domingo',
        'EC'=>'Quito',
        'EG'=>'Cairo',
        'SV'=>'San Salvador',
        'GQ'=>'Malabo',
        'ER'=>'Asmara',
        'EE'=>'Tallinn',
        'ET'=>'Addis Ababa',
        'FK'=>'Stanley',
        'FO'=>'Tórshavn',
        'FJ'=>'Suva',
        'FI'=>'Helsinki',
        'FR'=>'Parris',
        'GF'=>'Cayenne',
        'PF'=>'Papeete',
        'TF'=>'Saint-Pierre',
        'GA'=>'Libreville',
        'GM'=>'Banjul',
        'GE'=>'Tbilisi',
        'DE'=>'Berlin',
        'GH'=>'Accra',
        'GI'=>'Gibraltar',
        'GR'=>'Athens',
        'GL'=>'Nuuk',
        'GD'=>'Saint George s',
        'GP'=>'Basse-Terre',
        'GU'=>'Hagåtña',
        'GT'=>'Guatemala City',
        'GG'=>'Saint Peter Port',
        'GN'=>'Conakry',
        'GW'=>'Bissau',
        'GY'=>'GGGeorgetown',
        'HT'=>'Port-au-Prince',
        'HM'=>'Heard Island',
        'HN'=>'Tegucigalpa',
        'HK'=>'Central',
        'HU'=>'Budapest',
        'IS'=>'Reykjavik',
        'IN'=>'New Delhi',
        'ID'=>'Jakarta',
        'IR'=>'Tehran',
        'IQ'=>'Baghdad',
        'IE'=>'Dublin',
        'IM'=>'Douglas',
        'IL'=>'Tel Aviv; Jerusalem',
        'IT'=>'Rome',
        'JM'=>'Kingston',
        'JP'=>'Tokyo',
        'JE'=>'Saint Helier',
        'JO'=>'Amman',
        'KZ'=>'Astana',
        'KE'=>'Nairobi',
        'KI'=>'Tarawa Atoll',
        'KW'=>'Kuwait City',
        'KG'=>'Bishkek',
        'LA'=>'Vientiane',
        'LV'=>'Riga',
        'LB'=>'Beirut',
        'LS'=>'Maseru',
        'LR'=>'Monrovia',
        'LY'=>'Tripoli',
        'LI'=>'Vaduz',
        'LT'=>'Vilnius',
        'LU'=>'Luxembourg',
        'MO'=>'Beijing',
        'MK'=>'Skopje',
        'MG'=>'Antananarivo',
        'MW'=>'Lilongwe',
        'MY'=>'Kuala Lumpur',
        'MV'=>'Male',
        'ML'=>'Bamako',
        'MT'=>'Valletta',
        'MH'=>'Majuro',
        'MQ'=>'Fort-de-France',
        'MR'=>'Nouakchott',
        'MU'=>'Port Louis',
        'YT'=>'Mamoudzou',
        'MX'=>'Mexico City',
        'FM'=>'Palikir',
        'MD'=>'Chisinau',
        'MC'=>'Monaco',
        'MN'=>'Ulaanbaatar',
        'ME'=>'Podgorica',
        'MS'=>'Plymouth',
        'MA'=>'Rabat',
        'MZ'=>'Maputo',
        'MM'=>'Naypyidaw',
        'NA'=>'Windhoek',
        'NR'=>'Nauru',
        'NP'=>'Kathmandu',
        'NL'=>'Amsterdam',
        'NC'=>'Nouméa',
        'NZ'=>'Wellington',
        'NI'=>'Managua',
        'NE'=>'Niamey',
        'NG'=>'Abuja',
        'NU'=>'Alofi',
        'NF'=>'Kingston',
        'MP'=>'Saipan',
        'KPP'=>'Pyongyang',
        'NO'=>'Oslo',
        'OM'=>'Muscat',
        'PK'=>'Islamabad',
        'PW'=>'Melekeok',
        'PS'=>'Ramallah; East Jerusalem',
        'PA'=>'Panama City',
        'PG'=>'Port Moresby',
        'PY'=>'Asuncion',
        'PE'=>'Lima',
        'PH'=>'Manila',
        'PN'=>'Adamstown',
        'PL'=>'Warsaw',
        'PT'=>'Lisbon',
        'QA'=>'Doha',
        'RE'=>'Saint-Denis',
        'RO'=>'Bucharest',
        'RU'=>'Moscow',
        'RW'=>'Kigali',
        'WS'=>'Apia',
        'SM'=>'San Marino',
        'ST'=>'Sao Tome',
        'SA'=>'Riyadh',
        'SN'=>'Dakar',
        'RS'=>'Belgrade',
        'SC'=>'Victoria',
        'SL'=>'Freetown',
        'SG'=>'Singapore',
        'SK'=>' Bratislava',
        'SI'=>'Ljubljana',
        'SB'=>'Honiara',
        'SO'=>'Mogadishu',
        'ZA'=>'Pretoria',
        'GS'=>'King Edward Point',
        'KR'=>'Seoul',
        'ES'=>'Madrid',
        'LK'=>'Colombo',
        'BL'=>'Gustavia',
        'SH'=>'Jamestown',
        'KN'=>'Basseterre',
        'LC'=>'Castries',
        'MF'=>'Saint Martin',
        'PM'=>'Saint-Pierre',
        'VC'=>'Kingstown',
        'SD'=>'Khartoum',
        'SR'=>'Paramaribo',
        'SJ'=>'Longyearbyen',
        'SZ'=>'Mbabane',
        'SE'=>'Stockholm',
        'CH'=>'Bern',
        'SY'=>'Damascus',
        'TW'=>'Taipei',
        'TJ'=>'Dushanbe',
        'TZ'=>'Dar es Salaam; Dodoma (legislative)',
        'TH'=>'Bangkok',
        'TL'=>'Dili',
        'TG'=>'Lome',
        'TK'=>'Atafu',
        'TO'=>'Nuku alofa',
        'TT'=>'Port-of-Spain',
        'TN'=>'Tunis',
        'TR'=>'Ankara',
        'TM'=>'Ashgabat',
        'TC'=>'Cockburn Town',
        'TV'=>'Vaiaku village',
        'UG'=>'Kampala',
        'UA'=>'Kyiv',
        'AE'=>'Abu Dhabi',
        'GB'=>'London',
        'US'=>'"Washington D.C',
        'UY'=>'Montevideo',
        'UM'=>'Wake Island',
        'VI'=>'Charlotte Amalie',
        'UZ'=>'Tashkent',
        'VU'=>'Port-Vila',
        'VA'=>'Vatican City',
        'VE'=>'Caracas',
        'VN'=>'Hanoi',
        'WF'=>'Mata Utu',
        'EH'=>'Laayoune',
        'YE'=>'Sanaa',
        'ZM'=>'Lusaka',
        'ZW'=>'Harare'
    );


    public function _getAramexQuotes()
    {

        $r = $this->_rawRequest;
        $pkgWeight = $r->getWeightPounds();
        $pkgQty = $r->getPackageQty();
        $product_group = 'EXP';
        $allowed_methods_key = 'allowed_international_methods';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $internationalmethods = $objectManager->create('\Aramex\Shipping\Model\Carrier\Aramex\Source\Internationalmethods');
        $domesticmethods = $objectManager->create('\Aramex\Shipping\Model\Carrier\Aramex\Source\Domesticmethods');
        $sessionCustomer = $objectManager->create('\Magento\Customer\Model\Session');
        $helper = $objectManager->create('\Aramex\Shipping\Helper\Data');
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');
        $customer = $objectManager->create('\Magento\Customer\Model\Customer');

        $allowed_methods = $internationalmethods->toKeyArray();
        if ($this->_scopeConfig->getValue('aramex/shipperdetail/country', self::SCOPE_STORE) == $r->
            getDestCountryId()) {
            $product_group = 'DOM';
            $allowed_methods = $domesticmethods->toKeyArray();
            $allowed_methods_key = 'allowed_domestic_methods';
        }
        $admin_allowed_methods = explode(',', $this->getConfigData($allowed_methods_key));
        $admin_allowed_methods = array_flip($admin_allowed_methods);
        $allowed_methods = array_intersect_key($allowed_methods, $admin_allowed_methods);

        $OriginAddress = [
            'StateOrProvinceCode' => $this->_scopeConfig->getValue('aramex/shipperdetail/state', self::SCOPE_STORE),
            'City' => $this->_scopeConfig->getValue('aramex/shipperdetail/city', self::SCOPE_STORE),
            'PostCode' => $this->_scopeConfig->getValue('aramex/shipperdetail/postalcode', self::SCOPE_STORE),
            'CountryCode' => $this->_scopeConfig->getValue('aramex/shipperdetail/country', self::SCOPE_STORE),
        ];
        //$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');

        $DestinationAddress = [
            'StateOrProvinceCode' => $r->getDestState(),
            'City' => (isset($this->jordonCityArray[$r->getDestCity()])) ? $this->jordonCityArray[$r->getDestCity()] : $this->CityArrayWW[$r->getDestCountryId()],
            'PostCode' => self::USA_COUNTRY_ID == $r->getDestCountryId() ? substr($r->getDestPostal(), 0, 5) : $r->
            getDestPostal(),
            'CountryCode' => $r->getDestCountryId(),
        ];
        $ShipmentDetails = [
            'PaymentType' => 'P',
            'ProductGroup' => $product_group,
            'ProductType' => '',
            'ActualWeight' => ['Value' => $pkgWeight, 'Unit' => 'KG'],
            'ChargeableWeight' => ['Value' => $pkgWeight, 'Unit' => 'KG'],
            'NumberOfPieces' => $pkgQty
        ];
        //city = NULL fixing
        $city_from_base = "";
        $customerSession = $sessionCustomer;
        if ($customerSession->isLoggedIn()) {
            $customerObj = $customer->load($customerSession->getCustomer()->getId());
            $customerAddress = [];
            foreach ($customerObj->getAddresses() as $address) {
                $customerAddress[] = $address->toArray();
            }
            foreach ($customerAddress as $customerAddres) {
                if ($customerAddres['postcode'] == $r->getDestPostal()) {
                    $city_from_base =  $customerAddres['city'];
                }
            }
            // if (!empty($customerAddress)) {
            //     $DestinationAddress['City'] = $city_from_base;
            // }
        }
        $clientInfo = $helper->getClientInfo();
        $baseCurrencyCode = $storeManager->getStore()->getBaseCurrency()->getCode();
        $params = [
            'ClientInfo' => $clientInfo,
            'OriginAddress' => $OriginAddress,
            'DestinationAddress' => $DestinationAddress,
            'ShipmentDetails' => $ShipmentDetails,
            'PreferredCurrencyCode' => $baseCurrencyCode
        ];
        $priceArr = [];
        $cod = $this->_scopeConfig->getValue('payment/cashondelivery/active', self::SCOPE_STORE);
        foreach ($allowed_methods as $m_value => $m_title) {
            $params['ShipmentDetails']['ProductType'] = $m_value;
            if ($m_value == "CDA") {
                $params['ShipmentDetails']['Services'] = "CODS";
            } else {
                $params['ShipmentDetails']['Services'] = "";
            }
            if(!empty($cod)){
                $params['ShipmentDetails']['Services'] = "CODS";
            }
            $requestFromAramex = $this->makeRequestToAramex($params, $m_value, $m_title, $cod);
            if (isset($requestFromAramex['response']['error'])) {
                continue;
            }
            $priceArr[] = $requestFromAramex['priceArr'];
        }

        $checkoutSession->setAramexShippingData($priceArr);

        $result = $this->sendResult($priceArr, $requestFromAramex);
        return $result;
    }
    private function makeRequestToAramex($params, $m_value, $m_title, $cod)
    {
        $priceArr = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('\Aramex\Shipping\Helper\Data');
        $soapClientFactory = $objectManager->create('\Magento\Framework\Webapi\Soap\ClientFactory');
        $baseUrl = $helper->getWsdlPath();
        $soapClient = $soapClientFactory->create(
            "https://ws.aramex.net/ShippingAPI.V2/RateCalculator/Service_1_0.svc?wsdl",
            ['version' => SOAP_1_1,'trace' => 1, 'keep_alive' => false]
        );
        try {
            $results = $soapClient->CalculateRate($params);
            if ($results->HasErrors) {
                if (is_array($results->Notifications->Notification)) {
                    $error = "";
                    foreach ($results->Notifications->Notification as $notify_error) {
                        $error .= 'Aramex: ' . $notify_error->Code . ' - ' . $notify_error->Message . "  *******  ";
                    }
                    $response['error'] = $error;
                } else {
                    $response['error'] = 'Aramex: ' . $results->Notifications->Notification->Code . ' - ' .
                        $results->Notifications->Notification->Message;
                }
                $response['type'] = 'error';
            } else {
                $response['type'] = 'success';
                if (!empty($cod)) {
                    $priceArr[$m_value] = [
                        'label' => $m_title,
                        'amount' => $results->TotalAmount->Value - $results->RateDetails->OtherAmount3,
                        'currency' => $results->TotalAmount->CurrencyCode,
                        'cod' => $results->RateDetails->OtherAmount3
                    ];
                } else {
                    $priceArr[$m_value] = [
                        'label' => $m_title,
                        'amount' => $results->TotalAmount->Value,
                        'currency' => $results->TotalAmount->CurrencyCode
                    ];
                }
            }
        } catch (\Exception $e) {
            $response['type'] = 'error';
            $response['error'] = $e->getMessage();
        }
        return[ 'priceArr' => $priceArr, 'response' => $response];
    }
    private function sendResult($priceArr, $requestFromAramex)
    {
        $result = $this->_rateResultFactory->create();
        if (empty($priceArr[0])) {
            if (isset($requestFromAramex['response'])) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($requestFromAramex['response']['error']);
                $result->append($error);
                return $error;
            }
        } else {
            foreach ($priceArr as $priceArr1) {
                foreach ($priceArr1 as $method => $values) {
                    $rate = $this->_rateMethodFactory->create();
                    $rate->setCarrier($this->_code);
                    $rate->setCarrierTitle($this->getConfigData('title'));
                    $rate->setMethod($method);
                    $rate->setMethodTitle($values['label']);
                    $rate->setPrice($values['amount']);
                    $rate->setCost($values['amount']);
                    $result->append($rate);
                }
            }
        }
        return $result;
    }
}