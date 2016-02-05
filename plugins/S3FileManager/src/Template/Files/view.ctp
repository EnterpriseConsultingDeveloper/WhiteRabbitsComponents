<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit File'), ['action' => 'edit', $file->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete File'), ['action' => 'delete', $file->id], ['confirm' => __('Are you sure you want to delete # {0}?', $file->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Files'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New File'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Folders'), ['controller' => 'Folders', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Folder'), ['controller' => 'Folders', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="files view large-9 medium-8 columns content">
    <h3><?= h($file->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Name') ?></th>
            <td><?= h($file->name) ?></td>
        </tr>
        <tr>
            <th><?= __('Original Filename') ?></th>
            <td><?= h($file->original_filename) ?></td>
        </tr>
        <tr>
            <th><?= __('Mime Type') ?></th>
            <td><?= h($file->mime_type) ?></td>
        </tr>
        <tr>
            <th><?= __('Size') ?></th>
            <td><?= h($file->size) ?></td>
        </tr>
        <tr>
            <th><?= __('Folder') ?></th>
            <td><?= $file->has('folder') ? $this->Html->link($file->folder->name, ['controller' => 'Folders', 'action' => 'view', $file->folder->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($file->id) ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($file->created) ?></td>
        </tr>
        <tr>
            <th><?= __('Modified') ?></th>
            <td><?= h($file->modified) ?></td>
        </tr>
    </table>
</div>
