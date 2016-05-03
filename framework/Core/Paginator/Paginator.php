<?php

namespace Dore\Core\Paginator;

/*
  Описание методов:
  1. setCurrentPage - для установления номера текущей страницы
  2. setRecordsCount - для установления общего количества записей
  3. setMaxPageCount - для установления максимального количества страниц пагинатора
  4. setPerPageLimit - для установления количества выводимых записей на одну страницу
  5. getPages - для получения массива данных о страницах

  Пример использования:
  $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

  $paginator = new Paginator();
  $pages = $paginator->setCurrentPage($page)
  ->setRecordsCount(200)
  ->setPerPageLimit(10)
  ->setMaxPageCount(5)
  ->getPages();

  Если к примеру Вы переключитесь на вторую страничку, то в массиве появится дополнительный ключ 'prev' и значение(число) предыдущей странички.
  Так же если нет страничек(высчитывается на основе переданных данных) "вперед", то ключа next в массиве так же не будет.

  Ну и наконец пример вывода.

  if(isset($pages['prev'])){
  echo '<a href="?page=1"><<</a>
  <a href="?page='.$pages['prev'].'"><</a>
  ';
  }

  foreach($pages['pages'] as $page){
  $currentPageClass = $page == $pages['current'] ? 'class="cur_page"' : '';
  echo '<a href="?page='.$page.'" '.$currentPageClass .' >'.$page.'</a> ';
  }

  if(isset($pages['next'])){
  echo '<a href="?page='.$pages['next'].'">></a>
  <a href="?page='.$pages['last'].'">>></a>
  ';
  }
 */

class Paginator
{

    private $currentPage;
    private $recordsCount;
    private $perPageLimit = 10;
    private $maxPagesCount = 1;
    private $pagesCount;

    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
        return $this;
    }

    public function setRecordsCount($recordsCount)
    {
        $this->recordsCount = $recordsCount;
        return $this;
    }

    public function setPerPageLimit($perPageLimit)
    {
        $this->perPageLimit = $perPageLimit;
        return $this;
    }

    public function setMaxPageCount($maxPagesCount)
    {
        $this->maxPagesCount = $maxPagesCount;
        return $this;
    }

    private function getPageRange()
    {
        $this->pagesCount = (int) ceil($this->recordsCount / $this->perPageLimit);
        $firstPageInRange0 = $this->currentPage - (int) ($this->maxPagesCount / 2);
        $firstPageInRange1 = $this->pagesCount - $firstPageInRange0 < $this->maxPagesCount ?
                $this->pagesCount - $this->maxPagesCount + 1 : $firstPageInRange0;
        $firstPageInRange = $firstPageInRange1 < 1 ? 1 : $firstPageInRange1;
        //
        $lastPageInRange0 = $firstPageInRange + ($this->maxPagesCount - 1);
        $lastPageInRange = $lastPageInRange0 > $this->pagesCount ? $this->pagesCount : $lastPageInRange0;

        return range($firstPageInRange, $lastPageInRange);
    }

    public function getPages()
    {
        $pageRange = $this->getPageRange();

        $pages = [
            'current' => $this->currentPage,
            'pages' => $pageRange,
        ];

        $prevPage = $this->currentPage != 1 ? $this->currentPage - 1 : null;
        $nextPage = $this->currentPage < $this->pagesCount ? $this->currentPage + 1 : null;
        $lastPage = $nextPage ? $this->pagesCount : null;

        $pages['previous'] = !$prevPage ? null : $prevPage;
        $pages['next'] = !$nextPage ? null : $nextPage;
        !$lastPage ? : $pages['last'] = $lastPage;

        return $pages;
    }

}
