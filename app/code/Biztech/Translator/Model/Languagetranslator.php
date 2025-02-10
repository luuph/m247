<?php
/** Copyright © 2016 store.biztechconsultancy.com. All Rights Reserved. **/

namespace Biztech\Translator\Model;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Json\DecoderInterface;
use Magento\Store\Model\StoreManagerInterface;

class Languagetranslator {
	const ENDPOINT = 'https://www.googleapis.com/language/translate/v2';
	protected $curl;
	protected $googleApiKey;
	protected $jsonDecoder;
	protected $storeManagerInterface;
	protected $_scopeConfig;

	/**
	 *
	 * @param Curl $curl
	 * @param StoreManagerInterface $storeManagerInterface
	 * @param DecoderInterface $jsonInterface
	 */
	public function __construct(
		Curl $curl,
		StoreManagerInterface $storeManagerInterface,
		DecoderInterface $jsonInterface
	) {

		$this->curl = $curl;
		$this->storeManagerInterface = $storeManagerInterface;
		$this->jsonDecoder = $jsonInterface;
	}
	public function setApiKey($apiKey) {
		$this->googleApiKey = $apiKey;
		return $this;
	}
	/**
	 * @param $data
	 * @param $target
	 * @param string $source
	 * @return mixed
	 */
	public function translate($data, $target, $source = '') {
		$values = [
			'key' => $this->googleApiKey,
			'target' => $target,
			'q' => $data,
		];
		if (strlen($source) > 0) {
			$values['source'] = $source;
		}
		$referer = $this->storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
		$handle = curl_init();
		curl_setopt($handle, CURLOPT_URL, self::ENDPOINT);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $values);
		curl_setopt($handle, CURLOPT_REFERER, $referer);
		curl_setopt($handle, CURLOPT_HTTPHEADER, ['X-HTTP-Method-Override: GET']);
		$json = curl_exec($handle);
		$response = $this->jsonDecoder->decode($json);

		return $response;
	}
}
