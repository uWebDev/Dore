<ul class="pager">
    <?php if (!is_null($data['previous'])) : ?> 
        <li class="previous"><a href="<?= $this->route($route, ['id' => $data['previous']]) ?>">← Пред.</a></li>
    <?php else: ?>
        <li class="previous disabled"><span>← Пред.</span></li>
    <?php endif ?>
    <?php if (!is_null($data['next'])) : ?> 
        <li class="next"><a href="<?= $this->route($route, ['id' => $data['next']]) ?>">След. →</a></li>
    <?php else: ?>
        <li class="next disabled"><span>След. →</span></li>
        <?php endif ?>
</ul>
