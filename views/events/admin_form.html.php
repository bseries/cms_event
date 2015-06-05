<?php

use base_core\extensions\cms\Settings;
use lithium\security\Auth;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'cms_event', 'default' => $message]);
};

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->title,
		'empty' => $t('untitled'),
		'object' => $t('event')
	],
	'meta' => [
		'is_published' => $item->is_published ? $t('published') : $t('unpublished'),
		'is_sold_out' => $item->is_sold_out ? $t('sold out') : $t('tickets available')
	]
]);

?>
<article>
	<?=$this->form->create($item) ?>
		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('title', ['type' => 'text', 'label' => $t('Title'), 'class' => 'use-for-title']) ?>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('start', [
					'type' => 'date',
					'label' => $t('Start'),
					'value' => $item->start ?: date('Y-m-d')
				]) ?>
				<?= $this->form->field('end', [
					'type' => 'date',
					'label' => $t('End'),
					'value' => $item->end
				]) ?>

				<?= $this->form->field('location', [
					'type' => 'text',
					'label' => $t('Location')
				]) ?>
			</div>
		</div>
		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->media->field('cover_media_id', [
					'label' => $t('Cover'),
					'attachment' => 'direct',
					'value' => $item->cover()
				]) ?>
			</div>
			<div class="grid-column-right"></div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left"></div>
			<div class="grid-column-right">
				<?= $this->form->field('ticket_url', [
					'type' => 'text',
					'label' => $t('Ticket Link'),
					'placeholder' => $t('https://foo.com/bar or /bar')]
				) ?>
				<?= $this->form->field('is_sold_out', [
					'type' => 'checkbox',
					'label' => $t('sold out'),
					'checked' => (boolean) $item->is_sold_out,
					'value' => 1
				]) ?>
			</div>
		</div>

		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->editor->field('teaser', [
					'label' => $t('Teaser'),
					'size' => 'gamma',
					'features' => 'minimal'
				]) ?>
			</div>
			<div class="grid-column-right"></div>
		</div>

		<div class="grid-row">
			<?= $this->editor->field('body', [
				'label' => $t('Content'),
				'size' => 'beta',
				'features' => 'full'
			]) ?>
		</div>

		<div class="bottom-actions">
			<?php if ($item->exists()): ?>
				<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), ['id' => $item->id, 'action' => $item->is_published ? 'unpublish': 'publish', 'library' => 'cms_event'], ['class' => 'button large']) ?>
			<?php endif ?>
			<?= $this->form->button($t('save'), ['type' => 'submit', 'class' => 'button large save']) ?>
		</div>
	<?=$this->form->end() ?>
</article>