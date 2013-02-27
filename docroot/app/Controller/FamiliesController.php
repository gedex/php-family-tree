<?php
App::uses('AppController', 'Controller');
App::uses('TreeHelper', 'AppHelper');
/**
 * Families Controller
 *
 * @property Family $Family
 */
class FamiliesController extends AppController {

/**
 * Relations
 *
 * @var array
 */
	private $relations = array(
		'Brother' => 'Brother',
		'Sister' => 'Sister',
		'Son' => 'Son',
		'Daughter' => 'Daughter',
		'Husband' => 'Husband',
		'Wife' => 'Wife',
	);

	private $fieldsToExpose = array(
		'id', 'parent_id'
	);

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$people = $this->Family->find('all', array(
			'order' => 'lft asc',
		));
		$fields = $this->fieldsToExpose;

		$this->set(compact('people', 'fields'));
	}

	public function save() {
		if ( $this->request->is('post') ) {
			extract( $this->data );
			if ( !array_key_exists($relation, $this->relations) ) {
				$relation = 'Brother';
			}

			if ( $relation === 'Brother' ) {
				$gender = 'M';
			} elseif ( $relation === 'Sister' ) {
				$gender = 'F';
			} elseif ( $relation === 'Son' ) {
				$parent_id = $id;
				$gender = 'M';
			} elseif ( $relation === 'Daughter' ) {
				$parent_id = $id;
				$gender = 'F';
			} elseif ( $relation === 'Wife' ) {
				$parent_id = null;
				$gender = 'F';
			} elseif ( $relation === 'Husband' ) {
				$parent_id = null;
				$gender = 'M';
			}

			$data = array(
				'parent_id' => $parent_id,
				'name' => $name,
				'gender' => $gender,
				'birth_date' => $birth_date,
				'birth_place' => $birth_place,
			);

			$this->Family->save( $data );
		}

		$this->redirect(array('action' => 'index'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->Family->id = $id;
		if (!$this->Family->exists()) {
			throw new NotFoundException(__('Invalid family'));
		}
		$this->set('family', $this->Family->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Family->create();
			if ($this->Family->save($this->request->data)) {
				$this->Session->setFlash(__('The family has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The family could not be saved. Please, try again.'));
			}
		}
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->Family->id = $id;
		if (!$this->Family->exists()) {
			throw new NotFoundException(__('Invalid family'));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			if ($this->Family->save($this->request->data)) {
				$this->Session->setFlash(__('The family has been saved'));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The family could not be saved. Please, try again.'));
			}
		} else {
			$this->request->data = $this->Family->read(null, $id);
		}
	}

/**
 * delete method
 *
 * @throws MethodNotAllowedException
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->Family->id = $id;
		if (!$this->Family->exists()) {
			throw new NotFoundException(__('Invalid family'));
		}
		if ($this->Family->delete()) {
			$this->Session->setFlash(__('Family deleted'));
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('Family was not deleted'));
		$this->redirect(array('action' => 'index'));
	}
}
