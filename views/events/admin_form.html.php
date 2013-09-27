<?php ob_start() ?>
<script>
require(['editor'], function(Editor) {
	Editor.make('form .teaser textarea', false);
	Editor.make('form .body textarea', true);
});
require(['media-attachment'], function(MediaAttachment) {
	MediaAttachment.one('form .media-attachment');
});
</script>
<?php $this->scripts(ob_get_clean()) ?>

<event class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $this->title($t('Event')) ?></h1>

	<?=$this->form->create($item) ?>
		<?= $this->form->field('title', ['type' => 'text', 'label' => $t('Title')]) ?>
		<div class="media-attachment">
			<?= $this->form->label('EventsCoverMediaId', $t('Cover')) ?>
			<?= $this->form->hidden('cover_media_id') ?>
			<div class="selected"></div>
			<?= $this->html->link($t('select'), '#', ['class' => 'button select']) ?>
		</div>
		<?= $this->form->field('teaser', ['type' => 'textarea', 'label' => $t('Teaser'), 'wrap' => ['class' => 'teaser']]) ?>
		<?= $this->form->field('body', ['type' => 'textarea', 'label' => $t('Content'), 'wrap' => ['class' => 'body']]) ?>
		<?= $this->form->field('tags', ['value' => $item->tags(), 'label' => $t('Tags')]) ?>
		<?= $this->form->button($t('save'), ['type' => 'submit']) ?>
	<?=$this->form->end() ?>
</event>