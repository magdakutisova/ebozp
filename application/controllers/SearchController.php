<?php

class SearchController extends Zend_Controller_Action
{

    public function init()
    {
		$this->view->title = 'Vyhledávání';
		$this->view->headTitle ( $this->view->title );
    }

    public function indexAction()
    {
    	$this->view->subtitle = 'Indexace vyhledávače';
    	
		$clients = new Application_Model_DbTable_Client ();
		$clientData = $clients->getClients ();
		$index = Zend_Search_Lucene::create ( APPLICATION_PATH . '/searchIndex' );
		
		
		$message = '';

		if (sizeOf ( $clientData ) > 0) {			
			foreach ( $clientData as $client ) {
				$document = new Zend_Search_Lucene_Document ();
				$document->addField(Zend_Search_Lucene_Field::keyword('companyNumber', $client['company_number'], 'utf-8'));
				$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'clientId', $client ['id_client'], 'utf-8' ) );
				$document->addField ( Zend_Search_Lucene_Field::text ( 'companyName', $client ['company_name'], 'utf-8' ) );
				$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersStreet', $client ['headquarters_street'], 'utf-8' ) );
				$document->addField ( Zend_Search_Lucene_Field::text ( 'headquartersTown', $client ['headquarters_town'], 'utf-8' ) );
				$document->addField (Zend_Search_Lucene_Field::text('invoiceStreet', $client['invoice_street'], 'utf-8'));
				$document->addField(Zend_Search_Lucene_Field::text('invoiceTown', $client['invoice_town'], 'utf-8'));
				$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'client', 'utf-8' ) );
				$message .= "Indexován klient: " . $client ['company_name'] . "<br />";
				$index->addDocument ( $document );
			}
			
		}
		
		$subsidiaries = new Application_Model_DbTable_Subsidiary ();
		$subsidiaryData = $subsidiaries->getSubsidiariesSearch ();
		
		if (sizeOf ( $subsidiaryData ) > 0) {
			foreach ( $subsidiaryData as $subsidiary ) {
				$document = new Zend_Search_Lucene_Document ();
				$document->addField ( Zend_Search_Lucene_Field::keyword ( 'subsidiaryId', $subsidiary ['id_subsidiary'], 'utf-8' ) );
				$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryName', $subsidiary ['subsidiary_name'], 'utf-8' ) );
				$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryStreet', $subsidiary ['subsidiary_street'], 'utf-8' ) );
				$document->addField ( Zend_Search_Lucene_Field::text ( 'subsidiaryTown', $subsidiary ['subsidiary_town'], 'utf-8' ) );
				$document->addField ( Zend_Search_Lucene_Field::unIndexed('clientId', $subsidiary['client_id'], 'utf-8'));
				$document->addField ( Zend_Search_Lucene_Field::unIndexed ( 'type', 'subsidiary', 'utf-8' ) );
				$message .= "Indexována pobočka: " . $subsidiary ['subsidiary_name'] . "<br />";
				$index->addDocument ( $document );
			}
			
		}
		
		$index->commit();
		$index->optimize();
		$message .= 'Indexováno dokumentů: ' . $index->count();
		$this->view->message = $message;
		$this->_helper->redirector->gotoRoute ( array (), 'search' );
    }

    public function searchAction()
    {
    	$this->view->subtitle = 'Vyhledávání';
    	
        $form = new Application_Form_Search();
    	$this->view->form = $form;
    	
    	$post = false;
    	
    	if($this->getRequest()->isPost()){
    		$formData = $this->getRequest ()->getPost ();
    		$post = true;
			if ($form->isValid ( $formData )) {
				$query = $form->getValue('query');
				$message = "";
				try{
					$index = Zend_Search_Lucene::open(APPLICATION_PATH . '/searchIndex');
				}
				catch (Zend_Search_Lucene_Exception $e){
					$this->$this->_helper->redirector->gotoRoute ( array (), 'searchIndex' );
				}
				
				$results = $index->find($query);
				$message = "K vyhledávání indexováno " . $index->count() . " položek.";

				if($results){
					$countC = 0;
					$countS = 0;
					$clients = array();
					$subsidiaries = array();
					
					foreach($results as $result){
						if($result->type == 'client'){
							$clients[$countC]['companyNumber'] = $result->companyNumber;
							$clients[$countC]['clientId'] = $result->clientId;
							$clients[$countC]['companyName'] = $result->companyName;
							$clients[$countC]['headquartersStreet'] = $result->headquartersStreet;
							$clients[$countC]['headquartersTown'] = $result->headquartersTown;
							$clients[$countC]['invoiceStreet'] = $result->invoiceStreet;
							$clients[$countC]['invoiceTown'] = $result->invoiceTown;
							$countC++;
						}
						if($result->type == 'subsidiary'){
							$subsidiaries[$countS]['subsidiaryId'] = $result->subsidiaryId;
							$subsidiaries[$countS]['subsidiaryName'] = $result->subsidiaryName;
							$subsidiaries[$countS]['subsidiaryStreet'] = $result->subsidiaryStreet;
							$subsidiaries[$countS]['subsidiaryTown'] = $result->subsidiaryTown;
							$subsidiaries[$countS]['clientId'] = $result->clientId;
							$countS++;
						}
					}
													
					$this->view->clients = $clients;
					$this->view->subsidiaries = $subsidiaries;
					$this->view->message = $message;
				}
			}
    	}
    	$this->view->post = $post;
    	
    }

    

}



