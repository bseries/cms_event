<article class="view-<?= $this->_config['controller'] . '-' . $this->_config['template'] ?>">
	<h1 class="alpha"><?= $t('Events') ?></h1>

	<nav class="actions">
		<?= $this->html->link($t('add event'), ['action' => 'add', 'library' => 'cms_event'], ['class' => 'button']) ?>
	</nav>

	<table>
		<thead>
			<tr>
				<td>
				<td><?= $t('Title') ?>
				<td><?= $t('Created') ?>
				<td><?= $t('Modified') ?>
				<td>
		</thead>
		<tbody>
			<?php foreach ($data as $item): ?>
			<tr>
				<td>
					<?php if ($version = $item->cover_medium->version('fix3')): ?>
						<?= $this->media->image($version->url('http'), ['class' => 'media']) ?>
					<?php endif ?>
				<td><?= $item->title ?>
				<td><?= $item->created ?>
				<td><?= $item->modified ?>
				<td>
					<nav class="actions">
						<?= $this->html->link($t('delete'), ['id' => $item->id, 'action' => 'delete', 'library' => 'cms_event'], ['class' => 'button']) ?>
						<?= $this->html->link($t('edit'), ['id' => $item->id, 'action' => 'edit', 'library' => 'cms_event'], ['class' => 'button']) ?>
					</nav>
			<?php endforeach ?>
		</tbody>
	</table>
</article>