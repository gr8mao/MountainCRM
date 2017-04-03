<?php
/**
 * Created by PhpStorm.
 * User: maksimbelov
 * Date: 01.04.17
 * Time: 21:29
 */

/*
 * Класс Pagination для генерации постраничной навигации
 */

class Pagination
{

    /**
     *
     * @var int Ссылок навигации на страницу
     *
     */
    private $max = 5;

    /**
     *
     * @var string Ключ для GET, в который пишется номер страницы
     *
     */
    private $index = 'page';

    /**
     *
     * @var int Текущая страница
     *
     */
    private $current_page;

    /**
     *
     * @var int Общее количество записей
     *
     */
    private $total;

    /**
     *
     * @var int Записей на страницу
     *
     */
    private $limit;

    /**
     * Запуск необходимых данных для навигации
     * @param type $total <p>Общее количество записей</p>
     * @param type $currentPage <p>Номер текущей страницы</p>
     * @param type $limit <p>Количество записей на страницу</p>
     * @param type $index <p>Ключ для url</p>
     */
    public function __construct($total, $currentPage, $limit, $index)
    {
        # Устанавливаем общее количество записей
        $this->total = $total;

        # Устанавливаем количество записей на страницу
        $this->limit = $limit;

        # Устанавливаем ключ в url
        $this->index = $index;

        # Устанавливаем количество страниц
        $this->amount = $this->amount();

        # Устанавливаем номер текущей страницы
        $this->setCurrentPage($currentPage);
    }

    /**
     *  Для вывода ссылок
     * @return string HTML-код со ссылками навигации
     */
    public function get()
    {
        # Для записи ссылок
        $links = null;

        # Получаем ограничения для цикла
        $limits = $this->limits();

        $html = '<div class="ui pagination menu right floated left aligned">';
        # Генерируем ссылки
        for ($page = $limits[0]; $page <= $limits[1]; $page++) {
            # Если текущая это текущая страница, ссылки нет и добавляется класс active
            if ($page == $this->current_page) {
                $links .= '<a class="active item" href="#">' . $page . '</a>';
            } else {
                # Иначе генерируем ссылку
                $links .= $this->generateHtml($page);
            }
        }

        # Если ссылки создались
        if (!is_null($links)) {
            # Если текущая страница не первая
            if ($this->current_page > 1)
                # Создаём ссылку "На первую"
                $links = $this->generateHtml(1, '<i class="icon chevron left"></i>') . $links;

            # Если текущая страница не первая
            if ($this->current_page < $this->amount)
                # Создаём ссылку "На последнюю"
                $links .= $this->generateHtml($this->amount, '<i class="icon chevron right"></i>');
        }

        $html .= $links . '</ul>';

        # Возвращаем html
        return $html;
    }

    /**
     * Для генерации HTML-кода ссылки
     * @param integer $page - номер страницы
     *
     * @return string
     */
    private function generateHtml($page, $text = null)
    {
        # Если текст ссылки не указан
        if (!$text)
            # Указываем, что текст - цифра страницы
            $text = $page;

        $currentURI = rtrim($_SERVER['REQUEST_URI'], '/') . '/';
        $currentURI = preg_replace('~/'.$this->index.'[0-9]+~', '', $currentURI);
        # Формируем HTML код ссылки и возвращаем
        return
            '<a class="item" href="' . $currentURI . $this->index . $page . '">' . $text . '</a>';
    }

    /**
     *  Для получения, откуда стартовать
     *
     * @return array массив с началом и концом отсчёта
     */
    private function limits()
    {
        # Вычисляем ссылки слева (чтобы активная ссылка была посередине)
        $left = $this->current_page - round($this->max / 2);

        # Вычисляем начало отсчёта
        $start = $left > 0 ? $left : 1;

        # Если впереди есть как минимум $this->max страниц
        if ($start + $this->max <= $this->amount) {
            # Назначаем конец цикла вперёд на $this->max страниц или просто на минимум
            $end = $start > 1 ? $start + $this->max : $this->max;
        } else {
            # Конец - общее количество страниц
            $end = $this->amount;

            # Начало - минус $this->max от конца
            $start = $this->amount - $this->max > 0 ? $this->amount - $this->max : 1;
        }

        # Возвращаем
        return
            array($start, $end);
    }

    /**
     * Для установки текущей страницы
     *
     * @return void
     */
    private function setCurrentPage($currentPage)
    {
        # Получаем номер страницы
        $this->current_page = $currentPage;

        # Если текущая страница больше нуля
        if ($this->current_page > 0) {
            # Если текущая страница меньше общего количества страниц
            if ($this->current_page > $this->amount)
                # Устанавливаем страницу на последнюю
                $this->current_page = $this->amount;
        } else
            # Устанавливаем страницу на первую
            $this->current_page = 1;
    }

    /**
     * Для получения общего числа страниц
     *
     * @return int число страниц
     */
    private function amount()
    {
        # Делим и возвращаем
        return ceil($this->total / $this->limit);
    }

}