<?php
foreach ($folderList as &$folder) {
    unset($folder->lft);
    unset($folder->rght);
    unset($folder->parent_id);
    unset($folder->bucket);
    unset($folder->created);
    unset($folder->modified);
}

echo json_encode($folderList);


/*
foreach ($folderList as &$folder) {
unset($folder->lft);
unset($folder->rght);
unset($folder->parent_id);
unset($folder->bucket);
unset($folder->created);
unset($folder->modified);
}
*/