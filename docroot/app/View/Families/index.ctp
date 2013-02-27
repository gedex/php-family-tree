<?php

echo $this->Tree->generate($people, array(
	'id' => 'family-tree-list',
	'model' => 'Family',
	'element' => 'person',
));
?>

<div id="family-tree-chart" class="orgChart"></div>
