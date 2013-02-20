<?php
namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Album\Model\Album;         
use Album\Form\AlbumForm;
use Doctrine\ORM\EntityManager;
use Album\Entity\Album; // Atualizado para o uso de doctrine

class AlbumController extends AbstractActionController
{
	protected $albumTable;
	
	/**
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $em;
	
	public function setEntityManager(EntityManager $em)
	{
		$this->em = $em;
	}
	
	public function getEntityManager()
	{
		if (null === $this->em) {
			$this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
		}
		return $this->em;
	}
	
    public function indexAction()
    {
    	/* return new ViewModel(array(
    			'albums' => $this->getAlbumTable()->fetchAll(),
    	)); */ // Versão antiga sem doctrine
    	
    	return new ViewModel(array(
    			'albums' => $this->getEntityManager()->getRepository('Album\Entity\Album')->findAll()
    	)); // doctrine
    }

    public function addAction()
    {
    	$form = new AlbumForm();
    	$form->get('submit')->setValue('Add');
    	
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$album = new Album();
    		$form->setInputFilter($album->getInputFilter());
    		$form->setData($request->getPost());
    	
    		if ($form->isValid()) {
    			/* $album->exchangeArray($form->getData());
    			$this->getAlbumTable()->saveAlbum($album); */ // Versão antiga
    			
    			//Versão com doctrine
    			$album->populate($form->getData());
    			$this->getEntityManager()->persist($album);
    			$this->getEntityManager()->flush();
    			
    	
    			// Redirect to list of albums
    			return $this->redirect()->toRoute('album');
    		}
    	}
    	return array('form' => $form);
    }

    public function editAction()
    {
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) {
    		return $this->redirect()->toRoute('album', array(
    				'action' => 'add'
    		));
    	}
    	// $album = $this->getAlbumTable()->getAlbum($id);   ANTIGO
    	$album = $this->getEntityManager()->find('Album\Entity\Album', $id); // Com doctrine
    	
    	$form  = new AlbumForm();
    	$form->bind($album);
    	$form->get('submit')->setAttribute('value', 'Edit');
    	
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$form->setInputFilter($album->getInputFilter());
    		$form->setData($request->getPost());
    	
    		if ($form->isValid()) {
    			//$this->getAlbumTable()->saveAlbum($form->getData()); antigo
    			
    			//com doctrine
    			
    			$form->bindValues();
    			$this->getEntityManager()->flush();
    			 
    	
    			// Redirect to list of albums
    			return $this->redirect()->toRoute('album');
    		}
    	}
    	
    	return array(
    			'id' => $id,
    			'form' => $form,
    	);
    }

    public function deleteAction()
    {
    	$id = (int) $this->params()->fromRoute('id', 0);
    	if (!$id) {
    		return $this->redirect()->toRoute('album');
    	}
    	
    	$request = $this->getRequest();
    	if ($request->isPost()) {
    		$del = $request->getPost('del', 'No');
    	
    		if ($del == 'Yes') {
    			$id = (int) $request->getPost('id');
    			// $this->getAlbumTable()->deleteAlbum($id); ANTIGO
    			
    			$album = $this->getEntityManager()->find('Album\Entity\Album', $id);
    			if ($album) {
    				$this->getEntityManager()->remove($album);
    				$this->getEntityManager()->flush();
    			}
    		}
    	
    		// Redirect to list of albums
    		return $this->redirect()->toRoute('album');
    	}
    	
    	return array(
    			'id'    => $id,
    			'album' => $this->getAlbumTable()->getAlbum($id)
    	);
    }
    
    public function getAlbumTable()
    {
    	if (!$this->albumTable) {
    		$sm = $this->getServiceLocator();
    		$this->albumTable = $sm->get('Album\Model\AlbumTable');
    	}
    	return $this->albumTable;
    }
}