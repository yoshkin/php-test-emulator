<?php

namespace AYashenkov\Services;

use AYashenkov\Database\DB;

class Emulator
{

    /**
     * Сохраняем новый тест в таблице тестов
     * @param $data
     * @return array
     */
    public function saveSettings($data)
    {
        try {
            $range = $this->validateRange($data);
            if (!$range) {
                throw new \LogicException('Сложность вопроса не корректна (допустимо от 0 до 100)');
            }

            /**
             * Проверяем, есть ли результаты тестирования для последнего теста,
             * если нет, то новый не создаем, а обновляем существующий
             */
            $test = DB::q("SELECT id FROM tests t 
                  LEFT JOIN results r ON r.test_id = t.id 
                  WHERE r.test_id IS NULL ORDER BY id DESC LIMIT 1")
                ->fetch();

            $range_query = (string)($range['min'] . '-' . $range['max']);
            if (empty($test['id'])) {
                DB::x('INSERT INTO tests SET difficulty = ?s', $range_query);
            } else {
                DB::x('UPDATE tests SET difficulty = ?s WHERE id = ?i', $range_query, $test['id']);
            }

            return array(
                'success' => true,
                'data' => array(
                    'range' => $range,
                    'message' => 'Настройки успешно сохранены, теперь вы можете запустить эмулятор теста.'
                )
            );
        } catch (\LogicException $e) {
            return array(
                'success' => false,
                'data' => $e->getMessage()
            );
        }
    }

    /**
     * Получение истории результатов
     * @return array
     */
    public function getResultsHistory()
    {
        $history = $this->generateHistoryArray($data = array());
        return array(
            'success' => true,
            'data' => $history);
    }


    /**
     * Запуск эмулятора тестов
     * @param $data
     * @return array
     */
    public function runEmulator($data)
    {
        try {
            /**
             * Валидация входящих параметров
             */
            $intellect = $this->validateRange($data);
            if (!$intellect) {
                throw new \LogicException('Уровень интеллекта участника введен не корректно (допустимо от 0 до 100)');
            }

            /**
             * Проверяем, сохранены ли настройки теста
             */
            $test = DB::q("SELECT * FROM tests ORDER BY id DESC LIMIT 1")->fetch();
            if (!$test) {
                throw new \LogicException('Перед запуском эмулятора, нужно сохранить настройки теста');
            }

            /**
             * Новый участник
             */
            $range_sql = (string)($intellect['min'] . '-' . $intellect['max']);
            DB::x('INSERT INTO persons SET intellect = ?s', $range_sql);
            $person_id = DB::lastInsertId();

            /**
             * Сложность теста
             */
            $difficulty = $this->getTestDifficulty($test);

            /**
             * Максимальное количество использования вопроса
             */
            $max_used = DB::q("SELECT max(question_used_counter) as max_used FROM questions")->fetch();
            $max_used = $max_used['max_used']+1;

            /**
             * Все вопросы
             */
            $qu_max = 0;
            $questions_array = array();
            $questions = DB::q("SELECT * FROM questions")->fetchAll();
            foreach ($questions as $question) {
                // Формируем диапазон по частоте использования и добавляем в массив $questions_array
                $qu_min = $qu_max+1;
                $qu_max = $qu_min+floor($max_used/($question['question_used_counter']+1)); // Диапазон будет выше у реже используемых
                $questions_array[$question['id']] = array(
                    'qu_min' => $qu_min,
                    'qu_max' => $qu_max,
                    'question_used_counter' => $question['question_used_counter']
                );
            }

            $random_qu = array();
            // Эмулируем 40 вопросов
            for ($i=1; $i<=40; $i++) {
                // Получаем случайный вопрос с результатом ответа и массив оставшихся вопросов
                $random = $this->getRandomData($questions_array, $qu_max, $difficulty, $intellect);
                // Результаты ответа добаляем в массив $random_qu
                $random_qu[] = array(
                    'number' => $i,
                    'question_id' => $random['qu_rand'],
                    'result' => $random['result'],
                    'difficulty' => $random['difficulty'],
                    'question_used_counter' => $random['question_used_counter']
                );

                DB::x('UPDATE questions SET question_used_counter = question_used_counter+1 WHERE id = ?i', (int)$random['qu_rand']);

                DB::x('INSERT INTO results SET test_id = ?i, question_id = ?i, person_id = ?i, result = ?i',
                    (int)$test['id'], (int)$random['qu_rand'], (int)$person_id, (int)$random['result']);

                if (empty($random['questions'])) {
                    break;
                }
                $questions_array = $random['questions'];
                $qu_max = $random['qu_max'];
            }
            return array(
                'success' => true,
                'data' => array(
                    'range' => $intellect,
                    'questions' => $random_qu
                )
            );
        } catch (\LogicException $e) {
            return array(
                'success' => false,
                'data' => $e->getMessage()
            );
        }
    }


    /**
     * Получаем случайный вопрос, ответ участника теста и сложность вопроса
     * @param $questions array Массив вопросов
     * @param $max integer Максимальное число диапазона частоты использования
     * @param $difficulty array Диапазон сложности
     * @param $intellect array Диапазон интеллекта
     * @return array
     */
    protected function getRandomData($questions, $max, $difficulty, $intellect)
    {
        $rand = rand(1, $max);

        list($qu_max, $qu_rand, $question_used_counter) = 0;

        foreach ($questions as $k => $v) {
            if ($rand >= $v['qu_min'] && $rand <= $v['qu_max']) {
                $qu_rand = $k;
                $question_used_counter = $v['question_used_counter'];
                break;
            }
        }

        unset($questions[$qu_rand]);

        foreach ($questions as $k => $v) {
            $qu_min = $qu_max+1;
            $qu_max = $qu_min+($v['qu_max']-$v['qu_min']);
            $questions[$k] = array(
                'qu_min' => $qu_min,
                'qu_max' => $qu_max,
                'question_used_counter' => $v['question_used_counter']
            );
        }

        $difficulty = rand($difficulty['min'], $difficulty['max']);
        $intellect = rand($intellect['min'], $intellect['max']);

        $result = rand(0, $difficulty+$intellect) <= $intellect ? 1 : 0;
        if ($intellect == $difficulty) {
            $result = rand(0, 1);
        };
        if ($intellect == 0 && $difficulty > 0) {
            $result = 0;
        }
        if ($intellect == 100 && $difficulty < 100) {
            $result = 1;
        }

        return array(
            'qu_max' => $qu_max,
            'qu_rand' => $qu_rand,
            'difficulty' => $difficulty,
            'result' => $result,
            'question_used_counter' => $question_used_counter,
            'questions' => $questions
        );
    }


    /**
     * Валидация диапазона (min - max)
     * @param $range
     * @return array|bool
     */
    protected function validateRange($range)
    {
        $min = isset($range['min']) ? (int)$range['min'] : 0;
        $max = isset($range['max']) ? (int)$range['max'] : 0;

        if ($min <= $max && $min >= 0 && $max <= 100) {
            return array('min' => $min, 'max' => $max);
        }
        return false;
    }

    /**
     * Формируем массив с историей тестов
     * @param $history
     * @return array
     */
    protected function generateHistoryArray($history)
    {
        $persons = DB::q('SELECT * FROM persons');
        foreach ($persons->fetchAll() as $person) {
            $stat = DB::q('SELECT count(r.result) AS counter, sum(r.result) AS summ, t.difficulty FROM results r
              LEFT JOIN tests t ON t.id = r.test_id
              WHERE r.person_id = ?i', $person['id'])->fetch();

            $history[] = array(
                'number' => $person['id'],
                'intellect' => $person['intellect'],
                'difficulty' => $stat['difficulty'],
                'result' => array(
                    'summ' => $stat['summ'],
                    'counter' => $stat['counter'])
            );
        }
        return $history;
    }

    /**
     * Сложность теста
     * @param $test
     * @return array
     */
    protected function getTestDifficulty($test)
    {
        $difficulty = explode('-', $test['difficulty']);
        $difficulty = array(
            'min' => $difficulty[0],
            'max' => $difficulty[1]
        );
        return $difficulty;
    }
}