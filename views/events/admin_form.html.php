<?php

use lithium\core\Environment;

$features = Environment::get('features');

?>
<?php ob_start() ?>
<script>
require(['editor'], function(Editor) {
	Editor.make('form .teaser textarea', true);
	Editor.make('form .body textarea', true);
});
require(['media-attachment'], function(MediaAttachment) {
	<?php $url = ['controller' => 'files', 'library' => 'cms_media', 'admin' => true] ?>

	MediaAttachment.init({
		endpoints: {
			namespace: 'admin/api',
			view: '<?= $this->url($url + ['action' => 'api_view', 'id' => '__ID__']) ?>'
		}
	});
	MediaAttachment.one('form .media-attachment');
});
require(['jquery', 'domready!'], function($) {
	var handleSelect = function(el) {
		if ($(el).val() == 'url') {
			$('form .body').hide();
			$('form [name="url"]').parent().show();
		} else {
			$('form .body').show();
			$('form [name="url"]').vale('');
			$('form [name="url"]').parent().hide();
		}
	}
	$('[name="content_type"').change(function(ev) {
		handleSelect(this);
	});
	handleSelect('[name="content_type"]');
});

</script>
<?php $this->scripts(ob_get_clean()) ?>

<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Event')) ?></h1>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('title', ['type' => 'text', 'label' => $t('Title')]) ?>
		<div class="media-attachment">
			<?= $this->form->label('EventsCoverMediaId', $t('Cover')) ?>
			<?= $this->form->hidden('cover_media_id') ?>
			<div class="selected"></div>
			<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
		</div>
		<div class="date-range">
			<label><?= $t('Start/end') ?></label>
			<?= $this->form->field('start', [
				'type' => 'date',
				'label' => $t('Start'),
				'value' => $item->start != '0000-00-00' ? $item->start : ''
			]) ?>
			<div class="separator">&mdash;</div>
			<?= $this->form->field('end', [
				'type' => 'date',
				'label' => $t('End'),
				'value' => $item->end != '0000-00-00' ? $item->end : ''
			]) ?>
			<?= $this->form->field('is_open_end', ['type' => 'checkbox', 'label' => $t('Open ended?'), 'checked' => $item->is_open_end]) ?>
		</div>
		<?= $this->form->field('location', ['type' => 'text', 'label' => $t('Location')]) ?>

		<?= $this->form->field('teaser', [
			'type' => 'textarea',
			'label' => $t('Teaser'),
			'wrap' => ['class' => 'teaser'],
		]) ?>

		<?= $this->form->field('content_type', [
			'wrap' => ['class' => 'type-selector'],
			'type' => 'select',
			'label' => $t('Content Type'),
			'value' => $item->url ? 'url' : 'body',
			'list' => [
				'body' => $t('Text'),
				'url' => $t('Link')
			]
		]) ?>

		<?= $this->form->field('body', ['type' => 'textarea', 'label' => $t('Content Text'), 'wrap' => ['class' => 'body']]) ?>
		<?= $this->form->field('url', ['type' => 'url', 'label' => $t('Content Link')]) ?>

		<?= $this->form->field('tags', ['value' => $item->tags(), 'label' => $t('Tags')]) ?>

		<?php if ($features['connectFilmsWithEvents']): ?>
			<?= $this->form->field('films', [
				'type' => 'select',
				'multiple' => true,
				'list' => $films,
				'label' => $t('Associated Films')
			]) ?>
		<?php endif ?>
		<?= $this->form->button($t('save'), ['type' => 'submit']) ?>
	<?=$this->form->end() ?>
</article>