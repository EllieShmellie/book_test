<?php

namespace app\events;

use app\models\Book;
use yii\base\Event;

class BookEvent extends Event
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        public Book $book,
        /** @var int[] IDs of newly added authors */
        public array $authorIds = [],
        array $config = [],
    ) {
        parent::__construct($config);
    }
}
