<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Test'), ['action' => 'edit', $test->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Test'), ['action' => 'delete', $test->id], ['confirm' => __('Are you sure you want to delete # {0}?', $test->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Tests'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Test'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Files'), ['controller' => 'Files', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New File'), ['controller' => 'Files', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="tests view large-9 medium-8 columns content">
    <h3><?= h($test->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Name') ?></th>
            <td><?= h($test->name) ?></td>
        </tr>
        <tr>
            <th><?= __('File') ?></th>
            <td><?= $test->has('file') ? $this->Html->link($test->file->name, ['controller' => 'Files', 'action' => 'view', $test->file->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($test->id) ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($test->created) ?></td>
        </tr>
        <tr>
            <th><?= __('Modified') ?></th>
            <td><?= h($test->modified) ?></td>
        </tr>
    </table>
</div>
