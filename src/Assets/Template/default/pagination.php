<ul class="pager">
    <?php if (!is_null($data['previous'])) : ?> 
        <li class="previous"><a href="<?= $this->route($route, ['id' => $id, 'page' => $data['previous']]) ?>">← Пред.</a></li>
    <?php else: ?>
        <li class="previous disabled"><span>← Пред.</span></li>
    <?php endif ?>
    <?php if (isset($data['pages'])) : ?> 
        <?php foreach ($data['pages'] as $page) : ?> 
            <?php if ($page != $data['current']) : ?> 
                <li class="hidden-xs">
                    <a href="<?= $this->route($route, ['id' => $id, 'page' => $page]) ?>">
                        <?= $page ?>
                    </a>
                </li>
            <?php else: ?>
                <li class="disabled hidden-xs">
                    <span><?= $page ?></span>
                </li>
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>
    <?php if (!is_null($data['next'])) : ?> 
        <li class="next"><a href="<?= $this->route($route, ['id' => $id, 'page' => $data['next']]) ?>">След. →</a></li>
    <?php else: ?>
        <li class="next disabled"><span>След. →</span></li>
        <?php endif ?>
</ul>
