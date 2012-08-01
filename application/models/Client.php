<?php
class Application_Model_Client{
	
	protected $idClient;
	protected $companyName;
	protected $companyNumber;
	protected $taxNumber;
	protected $headquartersStreet;
	protected $headquartersCode;
	protected $headquartersTown;
	protected $invoiceStreet;
	protected $invoiceCode;
	protected $invoiceTown;
	protected $business;
	protected $insuranceCompany;
	protected $private;
	protected $deleted;
	protected $open;
	protected $subsidiaries;
	
	public function __construct ($options = array()){
		if (!empty($options)){
			$this->populate($options);
		}
	}
	/**
	 * @return the $idClient
	 */
	public function getIdClient() {
		return $this->idClient;
	}

	/**
	 * @param $idClient the $idClient to set
	 */
	public function setIdClient($idClient) {
		$this->id = $idClient;
	}

	/**
	 * @return the $companyName
	 */
	public function getCompanyName() {
		return $this->companyName;
	}

	/**
	 * @param $companyName the $companyName to set
	 */
	public function setCompanyName($companyName) {
		$this->companyName = $companyName;
	}

	/**
	 * @return the $companyNumber
	 */
	public function getCompanyNumber() {
		return $this->companyNumber;
	}

	/**
	 * @param $companyNumber the $companyNumber to set
	 */
	public function setCompanyNumber($companyNumber) {
		$this->companyNumber = $companyNumber;
	}

	/**
	 * @return the $taxNumber
	 */
	public function getTaxNumber() {
		return $this->taxNumber;
	}

	/**
	 * @param $taxNumber the $taxNumber to set
	 */
	public function setTaxNumber($taxNumber) {
		$this->taxNumber = $taxNumber;
	}

	/**
	 * @return the $headquartersStreet
	 */
	public function getHeadquartersStreet() {
		return $this->headquartersStreet;
	}

	/**
	 * @param $headquartersStreet the $headquartersStreet to set
	 */
	public function setHeadquartersStreet($headquartersStreet) {
		$this->headquartersStreet = $headquartersStreet;
	}

	/**
	 * @return the $headquartersCode
	 */
	public function getHeadquartersCode() {
		return $this->headquartersCode;
	}

	/**
	 * @param $headquartersCode the $headquartersCode to set
	 */
	public function setHeadquartersCode($headquartersCode) {
		$this->headquartersCode = $headquartersCode;
	}

	/**
	 * @return the $headquartersTown
	 */
	public function getHeadquartersTown() {
		return $this->headquartersTown;
	}

	/**
	 * @param $headquartersTown the $headquartersTown to set
	 */
	public function setHeadquartersTown($headquartersTown) {
		$this->headquartersTown = $headquartersTown;
	}

	/**
	 * @return the $invoiceStreet
	 */
	public function getInvoiceStreet() {
		return $this->invoiceStreet;
	}

	/**
	 * @param $invoiceStreet the $invoiceStreet to set
	 */
	public function setInvoiceStreet($invoiceStreet) {
		$this->invoiceStreet = $invoiceStreet;
	}

	/**
	 * @return the $invoiceCode
	 */
	public function getInvoiceCode() {
		return $this->invoiceCode;
	}

	/**
	 * @param $invoiceCode the $invoiceCode to set
	 */
	public function setInvoiceCode($invoiceCode) {
		$this->invoiceCode = $invoiceCode;
	}

	/**
	 * @return the $invoiceTown
	 */
	public function getInvoiceTown() {
		return $this->invoiceTown;
	}

	/**
	 * @param $invoiceTown the $invoiceTown to set
	 */
	public function setInvoiceTown($invoiceTown) {
		$this->invoiceTown = $invoiceTown;
	}

	/**
	 * @return the $business
	 */
	public function getBusiness() {
		return $this->business;
	}

	/**
	 * @param $business the $business to set
	 */
	public function setBusiness($business) {
		$this->business = $business;
	}

	/**
	 * @return the $insuranceCompany
	 */
	public function getInsuranceCompany() {
		return $this->insuranceCompany;
	}

	/**
	 * @param $insuranceCompany the $insuranceCompany to set
	 */
	public function setInsuranceCompany($insuranceCompany) {
		$this->insuranceCompany = $insuranceCompany;
	}

	/**
	 * @return the $private
	 */
	public function getPrivate() {
		return $this->private;
	}

	/**
	 * @param $private the $private to set
	 */
	public function setPrivate($private) {
		$this->private = $private;
	}

	/**
	 * @return the $deleted
	 */
	public function getDeleted() {
		return $this->deleted;
	}

	/**
	 * @param $deleted the $deleted to set
	 */
	public function setDeleted($deleted) {
		$this->deleted = $deleted;
	}

	/**
	 * @return the $open
	 */
	public function getOpen() {
		return $this->open;
	}

	/**
	 * @param $open the $open to set
	 */
	public function setOpen($open) {
		$this->open = $open;
	}

	/**
	 * @return the $subsidiaries
	 */
	public function getSubsidiaries() {
		return $this->subsidiaries;
	}

	/**
	 * @param $subsidiaries the $subsidiaries to set
	 */
	public function setSubsidiaries($subsidiaries) {
		$this->subsidiaries = $subsidiaries;
	}

	public function populate(array $data){
		$this->idClient = isset($data['id_client']) ? $data['id_client'] : null;
		$this->companyName = isset($data['company_name']) ? $data['company_name'] : null;
		$this->companyNumber = isset($data['company_number']) ? $data['company_number'] : null;
		$this->taxNumber = isset($data['tax_number']) ? $data['tax_number'] : null;
		$this->headquartersStreet = isset($data['headquarters_street']) ? $data['headquarters_street'] : null;
		$this->headquartersCode = isset($data['headquarters_code']) ? $data['headquarters_code'] : null;
		$this->headquartersTown = isset($data['headquarters_town']) ? $data['headquarters_town'] : null;
		$this->invoiceStreet = isset($data['invoice_street']) ? $data['invoice_street'] :  null;
		$this->invoiceCode = isset($data['invoice_code']) ? $data['invoice_code'] : null;
		$this->invoiceTown = isset($data['invoice_town']) ? $data['invoice_town'] : null;
		$this->business = isset($data['business']) ? $data['business'] : null;
		$this->insuranceCompany = isset($data['insurance_company']) ? $data['insurance_company'] : null;
		$this->private = isset($data['private']) ? $data['private'] : null;
		//$this->deleted = isset($data['deleted']) ? $data['deleted'] : null;
		//$this->open = isset($data['open']) ? $data['open'] : null;
		
		return $this;
	}
	
	public function toArray($toUpdate = false){
		$data = array();
		if(!$toUpdate){
			$data['id_client'] = $this->idClient;
		}
		$data['company_name'] = $this->companyName;
		$data['company_number'] = $this->companyNumber;
		$data['tax_number'] = $this->taxNumber;
		$data['headquarters_street'] = $this->headquartersStreet;
		$data['headquarters_code'] = $this->headquartersCode;
		$data['headquarters_town'] = $this->headquartersTown;
		$data['invoice_street'] = $this->invoiceStreet;
		$data['invoice_code'] = $this->invoiceCode;
		$data['invoice_town'] = $this->invoiceTown;
		$data['business'] = $this->business;
		$data['insurance_company'] = $this->insuranceCompany;
		$data['private'] = $this->private;
		//$data['deleted'] = $this->deleted;
		//$data['open'] = $this->open;

		return $data;
	}
	
}