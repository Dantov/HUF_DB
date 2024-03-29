<?php
/*
 * список прохождения участков для приложения №1
 * Для каждого участка присвоено 2 статуса "Принятия"(start) и "Сдачи"(end)
 * start => ID статуса Принятия, с которого нужно взять дату (потом сюда запишется массив данных этого статуса)
 * end => ID статуса сдачи, с которого нужно взять дату (потом сюда запишется массив данных этого статуса)
 * этот массив читается в drawTableRow.php
 */
return [
    [
        'id' => 1,
        'name' => '3D Дизайн',
        'title' => 'Создание 3Д моделей',
        'deadline' => 'Срок 1 день (3 Артикула/день)',
        'id_fio' => 4,
        'statuses' => [
            'start' => 8,
            'end' => 47,
        ],
    ],
    [
        'id' => 2,
        'name' => '3D Технолог.',
        'title' => 'Согласование 3Д моделей с Технологом',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 1,
        'statuses' => [
            'start' => 1,
            'end' => 2,
        ],
    ],
    [
        'id' => 3,
        'name' => '3Д Печать',
        'title' => 'Рост модели на 3д принтере',
        'deadline' => 'Срок 1 день (4 Артикула/день)',
        'id_fio' => 8,
        'statuses' => [
            'start' => 3,
            'end' => 5,
        ],
    ],
    [
        'id' => 31,
        'name' => 'Доработка',
        'title' => 'Напайка литника на восковую модель из 3Д принтера',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 11,
        'statuses' => [
            'start' => 37,
            'end' => 50,
        ],
    ],
    [
        'id' => 5,
        'name' => 'Литейка',
        'title' => 'Литьё в бронзе',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 14,
        'statuses' => [
            'start' => 12,
            'end' => 49,
        ],
    ],
    [
        'id' => 4,
        'name' => 'Доработка',
        'title' => 'Создание мастер модели',
        'deadline' => 'Срок 1 день (3 Артикула/день)',
        'id_fio' => 11,
        'statuses' => [
            'start' => 20,
            'end' => 6,
        ],
    ],
    [
        'id' => 36,
        'name' => '3L',
        'title' => 'Создание мастер модели',
        'deadline' => 'Срок 1 день (3 Артикула/день)',
        'id_fio' => 11,
        'statuses' => [
            'start' => 96,
            'end' => 97,
        ],
    ],
    [
        'id' => 8,
        'name' => 'ПДО Артикул',
        'title' => 'Согласование и присвоение артикула',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 28,
        'statuses' => [
            'start' => 60,
            'end' => 40,
        ],
    ],
    [
        'id' => 21,
        'name' => 'Изготовление матрицы',
        'title' => 'Изготовление матрицы',
        'deadline' => 'Срок 1 день (16 Артикула/день)',
        'id_fio' => 13,
        'statuses' => [
            'start' => 13,
            'end' => 14,
        ],
    ],
    [
        'id' => 32,
        'name' => 'ПДО Сигнал',
        'title' => 'Внесение артикула в базу. Сигнальная партия',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 28,
        'statuses' => [
            'start' => 62,
            'end' => 15,
        ],
    ],
    [
        'id' => 23,
        'name' => 'Кладовая',
        'title' => 'Кладовая',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 19,
        'statuses' => [
            'start' => 65,
            'end' => 66,
        ],
    ],
    [
        'id' => 9,
        'name' => 'Восковка',
        'title' => 'Изготовление восковой детали и напайка ёлки.',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 13,
        'statuses' => [
            'start' => 53,
            'end' => 16,
        ],
    ],
    [
        'id' => 27,
        'name' => 'Заготовка',
        'title' => 'Заготовка',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 25,
        'statuses' => [
            'start' => 73,
            'end' => 74,
        ],
    ],
    [
        'id' => 12,
        'name' => 'Закрепка в воск',
        'title' => 'Вставка камней в воск',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 18,
        'statuses' => [
            'start' => 43,
            'end' => 39,
        ],
    ],
    [
        'id' => 29,
        'name' => 'Литейка',
        'title' => 'Литьё модели в драгоценном метале',
        'deadline' => 'Срок 1 день (16 Артикула/день)',
        'id_fio' => 14,
        'statuses' => [
            'start' => 36,
            'end' => 44,
        ],
    ],
    [
        'id' => 6,
        'name' => 'Комплектация',
        'title' => 'Сортирует модели после литья.',
        'deadline' => 'Срок 1 день (20 Артикула/день)',
        'id_fio' => 15,
        'statuses' => [
            'start' => 59,
            'end' => 19,
        ],
    ],
    [
        'id' => 24,
        'name' => 'Монтировка 1',
        'title' => 'Пайка. Шлифовка. Закрепка.',
        'deadline' => 'Срок 1 день (3 Артикула/день)',
        'id_fio' => 18,
        'statuses' => [
            'start' => 9,
            'end' => 45,
        ],
    ],
    [
        'id' => 25,
        'name' => 'Монтировка 2',
        'title' => 'Пайка. Шлифовка. Закрепка.',
        'deadline' => 'Срок 1 день (3 Артикула/день)',
        'id_fio' => 21,
        'statuses' => [
            'start' => 67,
            'end' => 68,
        ],
    ],
    [
        'id' => 15,
        'name' => 'Механический 1',
        'title' => 'Финальная обработка изделий.',
        'deadline' => 'Срок 1 день (3 Артикула/день)',
        'id_fio' => 22,
        'statuses' => [
            'start' => 69,
            'end' => 70,
        ],
    ],
    [
        'id' => 26,
        'name' => 'Возрождение',
        'title' => 'Возрождение',
        'deadline' => 'Срок 1 день (1 Артикула/день)',
        'id_fio' => 23,
        'statuses' => [
            'start' => 71,
            'end' => 72,
        ],
    ],
    [
        'id' => 33,
        'name' => 'ПДО Себестоимость',
        'title' => 'Оценка себестоимости изделия',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 7,
        'statuses' => [
            'start' => 46,
            'end' => 61,
        ],
    ],
    [
        'id' => 17,
        'name' => 'Палата',
        'title' => 'Ставят пробу и именик.',
        'deadline' => 'Срок 1 день (20 Артикула/день)',
        'id_fio' => 19,
        'statuses' => [
            'start' => 54,
            'end' => 56,
        ],
    ],
    [
        'id' => 22,
        'name' => 'Упаковка',
        'title' => 'Укладываем в красивые коробочки',
        'deadline' => 'Срок 1 день (16 Артикула/день)',
        'id_fio' => 24,
        'statuses' => [
            'start' => 63,
            'end' => 64,
        ],
    ],
    [
        'id' => 34,
        'name' => 'Фото',
        'title' => 'Фотографирование изделия',
        'deadline' => 'Срок 1 день (10 Артикула/день)',
        'id_fio' => 33,
        'statuses' => [
            'start' => 92,
            'end' => 93,
        ],
    ],
    [
        'id' => 35,
        'name' => 'Ретушь',
        'title' => 'Ретуширование изделия',
        'deadline' => 'Срок 1 день (5 Артикула/день)',
        'id_fio' => 33,
        'statuses' => [
            'start' => 94,
            'end' => 95,
        ],
    ],
    [
        'id' => 19,
        'name' => 'Сбыт',
        'title' => 'Сбыт',
        'deadline' => 'Срок 1 день (16 Артикула/день)',
        'id_fio' => 20,
        'statuses' => [
            'start' => 41,
            'end' => 41,
        ],
    ],
];