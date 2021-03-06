<?php

use base_core\extensions\cms\Settings;
use lithium\security\Auth;
use lithium\g11n\Message;

$t = function($message, array $options = []) {
	return Message::translate($message, $options + ['scope' => 'cms_event', 'default' => $message]);
};

$meta = [
	'is_published' => $item->is_published ? $t('published') : $t('unpublished'),
	'is_promoted' => $item->is_promoted ? $t('promoted') : $t('unpromoted'),
];

if (Settings::read('event.useTicketing')) {
	$meta['is_sold_out'] = $item->is_sold_out ? $t('sold out') : $t('tickets available');
}

$this->set([
	'page' => [
		'type' => 'single',
		'title' => $item->title,
		'empty' => $t('untitled'),
		'object' => $t('event')
	],
	'meta' => $meta
]);

?>
<article>
	<div class="top-actions">
		<?= $this->_render('element', 'backlink', ['type' => 'multiple'] + compact('item'), [
			'library' => 'base_core'
		]) ?>
	</div>

	<?=$this->form->create($item) ?>
		<?php if ($useOwner): ?>
			<div class="grid-row">
				<h1><?= $t('Access') ?></h1>

				<div class="grid-column-left"></div>
				<div class="grid-column-right">
					<?= $this->form->field('owner_id', [
						'type' => 'select',
						'label' => $t('Owner'),
						'list' => $users
					]) ?>
				</div>
			</div>
		<?php endif ?>
		<div class="grid-row">
			<div class="grid-column-left">
				<?php if ($isTranslated): ?>
					<?php foreach ($item->translate('title') as $locale => $value): ?>
						<?= $this->form->field("i18n.title.{$locale}", [
							'type' => 'text',
							'label' => $t('Title') . ' (' . $this->g11n->name($locale) . ')',
							'class' => $locale === PROJECT_LOCALE ? 'use-for-title' : null,
							'value' => $value
						]) ?>
					<?php endforeach ?>
				<?php else: ?>
					<?= $this->form->field('title', [
						'type' => 'text',
						'label' => $t('Title'),
						'class' => 'use-for-title'
					]) ?>
				<?php endif ?>
			</div>
			<div class="grid-column-right">

				<?= $this->form->field('location', [
					'type' => 'text',
					'label' => $t('Location')
				]) ?>

				<?= $this->form->field('url', [
					'type' => 'text',
					'label' => $t('Link'),
					'placeholder' => $t('https://foo.com/bar or /bar')]
				) ?>
				<div class="help"><?= $t('Provide an URL to i.e. the event details page.') ?></div>

				<?= $this->form->field('tags', [
					'value' => $item->tags(),
					'label' => $t('Tags'),
					'placeholder' => 'foo, bar',
					'class' => 'input--tags'
				]) ?>
			</div>
		</div>
		<div class="grid-row">
			<div class="grid-column-left">
				<?= $this->form->field('start_date', [
					'type' => 'date',
					'label' => $t('Start date'),
					'value' => $item->start_date ?: date('Y-m-d')
				]) ?>
				<?= $this->form->field('start_time', [
					'type' => 'time',
					'label' => $t('Start time'),
					'value' => $item->start_time
				]) ?>
				<div class="help"><?= $t('The time is optional, you can leave it out if you like to.') ?></div>
			</div>
			<div class="grid-column-right">
				<?= $this->form->field('end_date', [
					'type' => 'date',
					'label' => $t('End date'),
					'value' => $item->end_date
				]) ?>
				<?= $this->form->field('end_time', [
					'type' => 'time',
					'label' => $t('End time'),
					'value' => $item->end_time
				]) ?>
				<div class="help"><?= $t('The time is optional, you can leave it out if you like to.') ?></div>
				<?= $this->form->field('timezone', [
					'type' => 'select',
					'label' => $t('Timezone'),
					'list' => $timezones,
					'value' => $authedUser->timezone
				]) ?>
				<div class="help"><?= $t('Timezone in which the event is taking place.') ?></div>
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
			<div class="grid-column-right">
				<?= $this->media->field('media', [
					'label' => $t('Media'),
					'attachment' => 'joined',
					'value' => $item->media()
				]) ?>
			</div>
		</div>

		<?php if (Settings::read('event.useTicketing')): ?>
			<div class="grid-row">
				<h1><?= $t('Ticketing') ?></h1>

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
		<?php endif ?>

		<div class="grid-row">
			<div class="grid-column-left">
				<?php if ($isTranslated): ?>
					<?php foreach ($item->translate('teaser') as $locale => $value): ?>
						<?= $this->editor->field("i18n.teaser.{$locale}", [
							'label' => $t('Teaser') . ' (' . $this->g11n->name($locale) . ')',
							'size' => 'gamma',
							'features' => 'minimal',
							'value' => $value
						]) ?>
					<?php endforeach ?>
				<?php else: ?>
					<?= $this->editor->field('teaser', [
						'label' => $t('Teaser'),
						'size' => 'gamma',
						'features' => 'minimal'
					]) ?>
				<?php endif ?>
			</div>
			<div class="grid-column-right"></div>
		</div>

		<div class="grid-row">
			<?php if ($isTranslated): ?>
				<?php foreach ($item->translate('body') as $locale => $value): ?>
					<?= $this->editor->field("i18n.body.{$locale}", [
						'label' => $t('Content') . ' (' . $this->g11n->name($locale) . ')',
						'size' => 'beta',
						'features' => 'full',
						'value' => $value
					]) ?>
				<?php endforeach ?>
			<?php else: ?>
				<?= $this->editor->field('body', [
					'label' => $t('Content'),
					'size' => 'beta',
					'features' => 'full'
				]) ?>
			<?php endif ?>
		</div>

		<div class="bottom-actions">
			<div class="bottom-actions__left">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($t('delete'), [
						'action' => 'delete', 'id' => $item->id
					], ['class' => 'button large delete']) ?>
				<?php endif ?>
			</div>
			<div class="bottom-actions__right">
				<?php if ($item->exists()): ?>
					<?= $this->html->link($item->is_published ? $t('unpublish') : $t('publish'), [
						'id' => $item->id,
						'action' => $item->is_published ? 'unpublish': 'publish'
					], ['class' => 'button large']) ?>

					<?= $this->html->link($t('iCAL'), [
						'id' => $item->id,
						'action' => 'export_ical'
					], ['class' => 'button large', 'download' => "event_{$item->id}.ics"]) ?>
				<?php endif ?>
				<?= $this->form->button($t('save'), [
					'type' => 'submit',
					'class' => 'button large save'
				]) ?>
			</div>
		</div>

	<?=$this->form->end() ?>
</article>