<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $test->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $test->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Tests'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Files'), ['controller' => 'Files', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New File'), ['controller' => 'Files', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="tests form large-9 medium-8 columns content">
    <?= $this->Form->create($test) ?>
    <fieldset>
        <legend><?= __('Edit Test') ?></legend>
        <?php
            echo $this->Form->input('name');
            echo $this->Form->input('file_id', ['options' => $files, 'empty' => true]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
