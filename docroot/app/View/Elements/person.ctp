<?php $person = $data['Family']; ?>

<a href="#" class="pull-left">
	<img class="photo" src="/img/user.png">
</a>
<div class="profile <?php echo ( $person['gender'] === 'M' ? 'male' : 'female' ); ?>">
	<h3 class="name">
		<a href="#"><?php echo $person['name']; ?></a>
	</h3>
	<?php if ( $person['birth_date'] ): ?>
	<span class="birth-date">
		<?php echo $person['birth_date']; ?>
	</span>
	<?php endif; ?>
</div>

<a href="#" class="arrow arrow-left" data-placement="left"></a>
<a href="#" class="arrow arrow-right" data-placement="right"></a>
<a href="#" class="arrow arrow-up" data-placement="top"></a>
<a href="#" class="arrow arrow-down" data-placement="bottom"></a>

<div class="fields-to-expose">
<?php foreach ( $fields as $field ): ?>
	<?php if ( array_key_exists($field, $person) ): ?>
	<input type="hidden" name="<?php echo $field; ?>" value="<?php echo $person[$field]; ?>">
	<?php endif; ?>
<?php endforeach; ?>
</div>
