<?php
/*
 * список прохождения участков для приложения №1
 * Для каждого участка присвоено 2 статуса "Принятия"(start) и "Сдачи"(end)
 * start => ID статуса Принятия, с которого нужно взять дату (потом сюда запишется массив данных этого статуса)
 * end => ID статуса сдачи, с которого нужно взять дату (потом сюда запишется массив данных этого статуса)
 * этот массив читается в drawTableRow.php
 */
return [
    '3Д' => [
        'start' => 8,
        'end' => 47,
    ],
    '3Д_техн' => [
        'start' => 1,
        'end' => 2,
    ],
    '3Д_Печать' => [
        'start' => 3,
        'end' => 5,
    ],
    'Напайка_литника' => [
        'start' => 37,
        'end' => 50,
    ],
    'Литьё_в_бронзе' => [
        'start' => 12,
        'end' => 49,
    ],
    'Создание_мастер_модели' => [
        'start' => 20,
        'end' => 6,
    ],
    'Присвоение_артикула' => [
        'start' => 60,
        'end' => 40,
    ],
    'Изготовление_матрицы' => [
        'start' => 13,
        'end' => 14,
    ],
    'Сигнальная_партия' => [
        'start' => 62,
        'end' => 15,
    ],
    'Кладовая' => [
        'start' => 65,
        'end' => 66,
    ],
    'Изготовление_воска' => [
        'start' => 53,
        'end' => 16,
    ],
    'Заготовка' => [
        'start' => 73,
        'end' => 74,
    ],
    'Вставка_камней' => [
        'start' => 43,
        'end' => 39,
    ],
    'Литьё_в_драг' => [
        'start' => 36,
        'end' => 44,
    ],
    'Комплектация' => [
        'start' => 59,
        'end' => 19,
    ],
    'Монтировка_1' => [
        'start' => 9,
        'end' => 45,
    ],
    'Монтировка_2' => [
        'start' => 67,
        'end' => 68,
    ],
    'Мех_1' => [
        'start' => 69,
        'end' => 70,
    ],
    'Возрожд' => [
        'start' => 71,
        'end' => 72,
    ],
    'Оценка' => [
        'start' => 46,
        'end' => 61,
    ],
    'Палата' => [
        'start' => 54,
        'end' => 56,
    ],
    'Упаковка' => [
        'start' => 63,
        'end' => 64,
    ],
    'Фотографирование' => [
        'start' => 92,
        'end' => 95,
    ],
    'Сбыт' => [
        'start' => 41,
        'end' => 41,
    ],
];