<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Folder'), ['action' => 'edit', $folder->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Folder'), ['action' => 'delete', $folder->id], ['confirm' => __('Are you sure you want to delete # {0}?', $folder->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Folders'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Folder'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Parent Folders'), ['controller' => 'Folders', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Parent Folder'), ['controller' => 'Folders', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Files'), ['controller' => 'Files', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New File'), ['controller' => 'Files', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="folders view large-9 medium-8 columns content">
    <h3><?= h($folder->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th><?= __('Name') ?></th>
            <td><?= h($folder->name) ?></td>
        </tr>
        <tr>
            <th><?= __('Name') ?></th>
            <td><?= h($folder->bucket) ?></td>
        </tr>
        <tr>
            <th><?= __('Parent Folder') ?></th>
            <td><?= $folder->has('parent_folder') ? $this->Html->link($folder->parent_folder->name, ['controller' => 'Folders', 'action' => 'view', $folder->parent_folder->id]) : '' ?></td>
        </tr>
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= $this->Number->format($folder->id) ?></td>
        </tr>
        <tr>
            <th><?= __('Lft') ?></th>
            <td><?= $this->Number->format($folder->lft) ?></td>
        </tr>
        <tr>
            <th><?= __('Rght') ?></th>
            <td><?= $this->Number->format($folder->rght) ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($folder->created) ?></td>
        </tr>
        <tr>
            <th><?= __('Modified') ?></th>
            <td><?= h($folder->modified) ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Files') ?></h4>
        <?php if (!empty($folder->files)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Name') ?></th>
                <th><?= __('Original Filename') ?></th>
                <th><?= __('Mime Type') ?></th>
                <th><?= __('Size') ?></th>
                <th><?= __('Created') ?></th>
                <th><?= __('Modified') ?></th>
                <th><?= __('Folder Id') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($folder->files as $files): ?>
            <tr>
                <td><?= h($files->id) ?></td>
                <td><?= h($files->name) ?></td>
                <td><?= h($files->original_filename) ?></td>
                <td><?= h($files->mime_type) ?></td>
                <td><?= h($files->size) ?></td>
                <td><?= h($files->created) ?></td>
                <td><?= h($files->modified) ?></td>
                <td><?= h($files->folder_id) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Files', 'action' => 'view', $files->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Files', 'action' => 'edit', $files->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Files', 'action' => 'delete', $files->id], ['confirm' => __('Are you sure you want to delete # {0}?', $files->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Folders') ?></h4>
        <?php if (!empty($folder->child_folders)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th><?= __('Id') ?></th>
                <th><?= __('Name') ?></th>
                <th><?= __('Lft') ?></th>
                <th><?= __('Rght') ?></th>
                <th><?= __('Parent Id') ?></th>
                <th><?= __('Created') ?></th>
                <th><?= __('Modified') ?></th>
                <th class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($folder->child_folders as $childFolders): ?>
            <tr>
                <td><?= h($childFolders->id) ?></td>
                <td><?= h($childFolders->name) ?></td>
                <td><?= h($childFolders->lft) ?></td>
                <td><?= h($childFolders->rght) ?></td>
                <td><?= h($childFolders->parent_id) ?></td>
                <td><?= h($childFolders->created) ?></td>
                <td><?= h($childFolders->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Folders', 'action' => 'view', $childFolders->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Folders', 'action' => 'edit', $childFolders->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Folders', 'action' => 'delete', $childFolders->id], ['confirm' => __('Are you sure you want to delete # {0}?', $childFolders->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
