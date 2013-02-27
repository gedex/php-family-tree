<?php
App::uses('AppModel', 'Model');
/**
 * Family Model
 *
 */
class Family extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array('Tree');

}
