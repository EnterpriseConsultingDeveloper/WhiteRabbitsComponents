<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Files'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Folders'), ['controller' => 'Folders', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Folder'), ['controller' => 'Folders', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="files form large-9 medium-8 columns content">
    <?= $this->Form->create($file, ['type' => 'file']) ?>
    <fieldset>
        <legend><?= __('Add File') ?></legend>
        <?php
            echo $this->Form->input('name');
            echo $this->Form->input('file', ['type' => 'file']);
            echo $this->Form->input('original_filename');
            echo $this->Form->input('mime_type');
            echo $this->Form->input('size');
            echo $this->Form->input('folder_id', ['options' => $folders]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
