<?php ob_start() ?>
<script>
require(['editor'], function(Editor) {
	Editor.make('form .body textarea', true);
});
require(['media-attachment'], function(MediaAttachment) {
	MediaAttachment.one('form .media-attachment');
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
		<?= $this->form->field('body', ['type' => 'textarea', 'label' => $t('Content'), 'wrap' => ['class' => 'body']]) ?>
		<?= $this->form->field('tags', ['value' => $item->tags(), 'label' => $t('Tags')]) ?>
		<?= $this->form->button($t('save'), ['type' => 'submit']) ?>
	<?=$this->form->end() ?>
</article>
