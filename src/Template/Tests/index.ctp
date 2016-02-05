<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Test'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Files'), ['controller' => 'Files', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New File'), ['controller' => 'Files', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="tests index large-9 medium-8 columns content">
    <h3><?= __('Tests') ?></h3>
    <p>Test Image from S3</p>
    <p><?= $this->S3File->image('preparedcontentimages/img/8/8f330f364a59941f9f4c261bb52ccd3467a3f0ed.JPG', []); ?></p>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th><?= $this->Paginator->sort('id') ?></th>
                <th><?= $this->Paginator->sort('name') ?></th>
                <th><?= $this->Paginator->sort('file_id') ?></th>
                <th><?= $this->Paginator->sort('created') ?></th>
                <th><?= $this->Paginator->sort('modified') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tests as $test): ?>
            <tr>
                <td><?= $this->Number->format($test->id) ?></td>
                <td><?= h($test->name) ?></td>
                <td><?= $test->has('file') ? $this->Html->link($test->file->name, ['controller' => 'Files', 'action' => 'view', $test->file->id]) : '' ?></td>
                <td><?= h($test->created) ?></td>
                <td><?= h($test->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $test->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $test->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $test->id], ['confirm' => __('Are you sure you want to delete # {0}?', $test->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
        </ul>
        <p><?= $this->Paginator->counter() ?></p>
    </div>
</div>
